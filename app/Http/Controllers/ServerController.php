<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServerUpdateRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Server;
use App\Models\User;
use Exception;

/**
 * Routing:
 * @group Server
 * @authenticated
 * @middleware can:not-suspended
 * 
 */
class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */

     // TODO: show only the servers that the user is attached to
    public function index()
    {
        return view('server.index', ['servers' => Server::all()]);
    }

    /**
     * Display the specified resource.
     * 
     * Allow users that are attached to the servers
     * Allow admins
     * 
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     * 
     */
    public function show(Server $server)
    {
        Gate::any(['admin-only', 'user-connected-to-server'], [$server]);

        return view('server.show', [
            'server' => Server::find($server->id)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Admin-only function
     * 
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     * 
     */
    public function edit(Server $server)
    {
        Gate::authorize('admin-only');

        return view('server.edit', [
            'server' => Server::find($server->id),
            // Get id and name for all users that are not suspended
            'users' => User::where('suspended', false)->select('id', 'name')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Admin-only function
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     * 
     */
    public function update(ServerUpdateRequest $request, $id)
    {
        Gate::authorize('admin-only');
        
        try {
            $server = Server::findOrFail($id);

            if($request->has('users')) {
                $user_ids = array_filter($request->input('users'), function($user_id) {
                    return in_array((int) $user_id, User::pluck('id')->toArray());
                });
                // Filter out invalid user ids from the input
                $server->users()->sync($user_ids);
            }
            
            // Update the server
            // Fill the server with the validated data
            $server->fill($request->validated());
            $server->save();

            return to_route('server.show', $server->id);
        } catch (Exception $e) {
            report($e);
            return back()->withInput()->withErrors(['general' => 'A problem occurred while updating the server. Please try again later.']);
        }
    }

    /**
     * Remove the specified resource from storage
     * Before deleting the server, detach all users from the server to prevent a foreign key error
     * Admin-only function
     * 
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     * 
     */
    public function destroy(Server $server)
    {
        Gate::authorize('admin-only');
        $server->users()->detach();
        $server->delete();
        return to_route('server.index');
    }
}
