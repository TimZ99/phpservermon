<?php

namespace App\Http\Controllers;

use App\Http\Requests\Server\StoreServerRequest;
use App\Http\Requests\Server\UpdateServerRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Server;

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
        return view('server.show', [
            'server' => Server::find($server->server_id)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server)
    {
        Gate::authorize('admin-only');
        return view('server.edit', [
            'server' => Server::find($server->server_id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServerRequest $request, Server $server)
    {
        Gate::authorize('admin-only');

        $server->fill($request->validated());
        $server->save();
        return to_route('server.edit', $server->server_id)->with('status', 'server-updated');
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
