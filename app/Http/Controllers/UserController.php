<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\Server;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Routing:
 *
 * @group User
 *
 * @authenticated
 *
 * @middleware can:not-suspended
 */
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @todo Filter the users by the ones that the user is attached to
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        return view('user.index', ['users' => User::all()]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('user.show', [
            'user' => User::find($user->id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $this->authorize('manage', $user);

        // Return the edit page with the user and servers
        return view('user.edit', [
            'user' => $user,
            'validScopes' => User::validScopes(),
            'servers' => Server::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\UserUpdateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $this->authorize('manage', $user);
        try {
            /**
             * Sync the servers with the user
             *
             * If the request has servers, filter the list of server ids
             * and sync the list of server ids with the user's servers
             *
             * If no servers are provided, detach all the user's servers
             */
            if ($request->has('servers')) {
                $server_ids = array_filter($request->input('servers'), function ($server_id) {
                    return in_array($server_id, Server::pluck('id')->toArray());
                });
                $user->servers()->sync($server_ids);
            } else {
                $user->servers()->detach();
            }

            /**
             * Check if the user is last user with user:manage:* scope
             * If the user is the one, don't allow the update
             */
            if ($user->isLastPowerfulUser() &&
                ! (is_array($request->input('scopes')) && in_array('user:manage:*', $request->input('scopes')))
            ) {
                $error_message = 'User update failed, tried removing the last user with user:manage:* privileges';
                Log::notice($error_message, ['user_id' => $user->id]);

                return back()->withInput()->withErrors(['lastuser:editscope' => $error_message]);
            }

            /**
             * Update the user
             * Fill the user with the validated data
             * and save the user
             */
            $user->fill($request->validated());
            $user->save();

            Log::info('User updated successfully', ['user_id' => $user->id]);

            return to_route('user.show', $user->id);
        } catch (Exception $e) {
            report($e);

            return back()->withInput()->withErrors(['general' => 'A problem occurred while updating the user. Please try again later.']);
        }
    }

    /**
     * Remove the specified resource from storage
     * Before deleting the user, detach all users from the user to prevent a foreign key error
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('manage', $user);
        // Cannot delete the user with user:manage:* scope
        if ($user->isLastPowerfulUser()) {
            Log::notice('User deleted failed, tried removing the last user with user:manage:* scope', ['user_id' => $user->id]);

            return back()->withErrors(['user:editdelete' => 'Cannot delete the last user with user:manage:* scope.']);
        }

        $user->servers()->detach();
        $user->delete();

        Log::info('User deleted successfully', ['user_id' => $user->id]);

        return to_route('user.index');
    }
}
