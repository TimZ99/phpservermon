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
     * This function will show a list of all servers.
     * 
     * @return \Illuminate\Http\Response
     * 
     * @todo Filter the servers by the ones that the user is attached to
     */
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
        // Check if the user is an admin or attached to the server
        Gate::any(['admin-only', 'user-connected-to-server'], [$server]);

        // Return the server page with the server and users
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
        // Check if the user is an admin
        Gate::authorize('admin-only');

        // Return the server edit page with the server and list of users with id and name
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
        // Check if the user is an admin
        Gate::authorize('admin-only');
        
        try {
            // Find the server
            $server = Server::findOrFail($id);

            /**
             * Sync the users with the server
             * If the request has users, filter the list of user ids
             * and sync the list of user ids with the server's users
             * 
             * If no users are provided, detach all the server's users
             */
            if($request->has('users')) {
                $user_ids = array_filter($request->input('users'), function($user_id) {
                    return in_array((int) $user_id, User::pluck('id')->toArray());
                });
                // Filter out invalid user ids from the input
                $server->users()->sync($user_ids);
            }
            else {
                $server->users()->detach();
            }
            
            /** 
             * Update the server
             * Fill the server with the validated data
             */
            $server->fill($request->validated());
            $server->save();

            // Return the server page with the updated server
            return to_route('server.show', $server->id);
        } catch (Exception $e) {
            report($e);
            // If an error occurs, return back to the server edit page with the input and errors
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
        // Check if the user is an admin
        Gate::authorize('admin-only');
        // Detach all users from the server
        $server->users()->detach();
        // Delete the server
        $server->delete();
        // Return to the server index page
        return to_route('server.index');
    }
}
