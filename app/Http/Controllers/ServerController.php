<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Server;
use App\Models\User;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('server.index', ['servers' => Server::all()]);
    }

    /**
     * Display the specified resource.
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
     */
    public function edit(Server $server)
    {
        Gate::authorize('admin-only');
        return view('server.edit', [
            'server' => Server::find($server->id),
            'users' => User::where('suspended', false)->select('id', 'name')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FormRequest $request, Server $server)
    {
        Gate::authorize('admin-only');

        /**
         * Filter out invalid user ids from the input
         */
        $user_ids = array_filter($request->input('users'), function($user_id) {
            return in_array((int) $user_id, User::pluck('id')->toArray());
        });

        $server->users()->sync($user_ids);

        $server->fill($request->validate(Server::rules()));
        $server->save();

        return to_route('server.show', $server->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server)
    {
        Gate::authorize('admin-only');

        $server->delete();
        return to_route('server.index');
    }
}
