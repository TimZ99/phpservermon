<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServerUpdateRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\Server;
use App\Jobs\RunCurl;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

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
     */
    public function monitorPage()
    {
        $user = Auth::user();
        foreach($user->servers as $server) {
            $server->statusCss = 'danger';
            $server->statusCssColor = '#dc3545';
        }

        return view('server.monitor', ['servers' => $user->servers]);
    }

    /**
     * Display a listing of the resource.
     * 
     * This function will show a list of all servers.
     * 
     * @return \Illuminate\Http\Response
     *
     */
    public function index()
    {
        // Check if the user is an admin
        Gate::authorize('admin-only');

        $servers = Server::all();
        foreach($servers as $server) {
            $server->statusCss = 'danger';
            $server->statusCssColor = '#dc3545';
        }
        return view('server.index', ['servers' => $servers]);
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
     * Show the form for creating a new resource.
     * Admin-only function
     * 
     * @return \Illuminate\Http\Response
     * 
     */
    public function create()
    {
        // Check if the user is an admin
        Gate::authorize('admin-only');
        // Return the server create page with a list of users with id and name
        return view('server.create');
    }

    /**
     * Store a newly created resource in storage.
     * Admin-only function
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * 
     */
    public function store(ServerUpdateRequest $request)
    {
        // Check if the user is an admin
        Gate::authorize('admin-only');
        
        try {
            // Create the server
            
            $server = Server::create($request->validated());
            // Sync the users with the server
            $server->users()->sync($request->input('users'));
            // Return the server page with the created server
            return to_route('server.show', $server->id);
        } catch (Exception $e) {
            report($e);
            // If an error occurs, return back to the server create page with the input and errors
            return back()->withInput()->withErrors(['general' => 'A problem occurred while creating the server. Please try again later.']);
        }
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
        $server->fill($request->validated())->save();

        $json = json_encode([
            'SSL' => [
                'enabled' => true,
                'SSL_expiration' => [
                    'enabled' => true,
                    'type' => 'warning',
                    'input' => ['days' => 5]
                ],
                'SSL_certificate_valid' => [
                    'enabled' => true,
                    'type' => 'error',
                    'input' => []
                ],
            ],
            'status_code' => [
                'enabled' => true,
                'type' => 'error',
                'input' => []
            ]
        ]);

        $server->fill(['check_settings' => $json])->save();

        // Return the server page with the updated server
        return to_route('server.show', $server->id);
    }

    /**
     * Run the job for the specified server
     * Admin-only function
     * 
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     * 
     */
    public function runJob(Server $server)
    {
        // Check if the user is an admin
        Gate::authorize('admin-only');
        // Dispatch the RunCurl job for the server
        RunCurl::dispatch($server);

        return 'Job dispatched and queue is processed.';
        // Return to the server page
        return to_route('server.show', $server->id);
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
