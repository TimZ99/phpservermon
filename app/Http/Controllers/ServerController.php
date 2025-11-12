<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServerUpdateRequest;
use App\Models\Server;
use App\Models\User;
use App\Services\ServerChecks\ServerCheckOrchestrator;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Routing:
 *
 * @group Server
 *
 * @authenticated
 *
 * @middleware can:not-suspended
 */
class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * This function will show a list of all servers.
     *
     * @return \Illuminate\Http\Response
     */
    public function monitorPage()
    {
        $user = Auth::user();
        $servers = $user->servers;
        foreach ($servers as $server) {
            $show_status = [];
            $checkSettings = $server->check_settings ?? [];

            foreach ($checkSettings as $checkName => $check) {
                if (! isset($check['enabled']) || ! $check['enabled']) {
                    continue;
                }
                if (isset($check['nested']) && $check['nested']) {
                    foreach ($check as $nestedKey => $nestedCheck) {
                        if (! isset($nestedCheck['enabled']) || ! $nestedCheck['enabled']) {
                            continue;
                        }
                        $latestResult = $server->check_histories()
                            ->where('name', $nestedKey)
                            ->latest('created_at')
                            ->first();
                        if ($latestResult) {
                            $statusCss = match ($latestResult->status) {
                                'success' => ['css' => 'success', 'color' => '#28a745'],
                                'warning' => ['css' => 'warning', 'color' => '#ffc107'],
                                'danger' => ['css' => 'danger', 'color' => '#dc3545'],
                                default => ['css' => 'secondary', 'color' => '#ddd'],
                            };

                            $show_status[] = [
                                'name' => $nestedKey,
                                'css' => $statusCss['css'],
                                'color' => $statusCss['color'],
                            ];
                        }
                    }
                }

                $latestResult = $server->check_histories()
                    ->where('name', $checkName)
                    ->latest('created_at')
                    ->first();
                if ($latestResult) {
                    $statusCss = match ($latestResult->status) {
                        'success' => ['css' => 'success', 'color' => '#28a745'],
                        'warning' => ['css' => 'warning', 'color' => '#ffc107'],
                        default => ['css' => 'danger', 'color' => '#dc3545'],
                    };

                    $show_status[] = [
                        'name' => $checkName,
                        'css' => $statusCss['css'],
                        'color' => $statusCss['color'],
                    ];
                }
            }
            $server->show_status = $show_status;
        }

        return view('server.monitor', ['servers' => $servers]);
    }

    /**
     * Display a listing of the resource.
     *
     * This function will show a list of all servers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Server::class);
        $servers = Server::all();
        foreach ($servers as $server) {
            $server->statusCss = match ($server->overall_status) {
                'success' => 'success',
                'warning' => 'warning',
                default => 'danger',
            };
            $server->statusCssColor = match ($server->statusCss) {
                'success' => '#28a745',
                'warning' => '#ffc107',
                default => '#dc3545',
            };
        }

        return view('server.index', ['servers' => $servers]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Server $server)
    {
        $this->authorize('view', $server);

        // Return the server page with the server and users
        return view('server.show', [
            'server' => Server::find($server->id),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('manageAny', Server::class);

        // Return the server create page with a list of users with id and name
        return view('server.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ServerUpdateRequest $request)
    {
        $this->authorize('manageAny', Server::class);
        try {
            // Create the server
            $server = Server::create($request->validated());
            $server->update(['check_settings' => $this->defaultCheckSettings()]);
            // Sync the users with the server
            $server->users()->sync($request->input('users'));

            // Return the server page with the created server
            return to_route('server.show', $server->id);
        } catch (Exception $e) {
            \Sentry\captureException($e);
            report($e);

            // If an error occurs, return back to the server create page with the input and errors
            return back()->withInput()->withErrors(['general' => 'A problem occurred while creating the server. Please try again later.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Server $server)
    {
        $this->authorize('manage', $server);

        // Return the server edit page with the server and list of users with id and name
        return view('server.edit', [
            'server' => Server::find($server->id),
            // Get id and name for all users that are not suspended
            'users' => User::where('suspended', false)->select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     */
    public function update(ServerUpdateRequest $request, $id)
    {
        $this->authorize('manage', Server::findOrFail($id));
        // Find the server
        $server = Server::findOrFail($id);

        /**
         * Sync the users with the server
         * If the request has users, filter the list of user ids
         * and sync the list of user ids with the server's users
         *
         * If no users are provided, detach all the server's users
         */
        if ($request->has('users')) {
            $user_ids = array_filter($request->input('users'), function ($user_id) {
                return in_array((int) $user_id, User::pluck('id')->toArray());
            });
            // Filter out invalid user ids from the input
            $server->users()->sync($user_ids);
        } else {
            $server->users()->detach();
        }
        /**
         * Update the server
         * Fill the server with the validated data
         */
        $server->fill($request->validated())->save();
        $server->fill(['check_settings' => $this->defaultCheckSettings()])->save();

        // Return the server page with the updated server
        return to_route('server.show', $server->id);
    }

    /**
     * Run the job for the specified server
     *
     * @return \Illuminate\Http\Response
     */
    public function runJob(Server $server, ServerCheckOrchestrator $orchestrator)
    {
        $this->authorize('check', $server);

        $orchestrator->dispatch([$server]);

        return 'Server checks queued.';
    }

    /**
     * Run a batch process on the given servers.
     *
     * @param  array  $servers  An array of servers to run the batch process on.
     *                          Each element should be an instance of \App\Models\Server.
     * @return void
     */
    public function runBatch(ServerCheckOrchestrator $orchestrator, $servers = [])
    {
        $this->authorize('checkAny', Server::class);
        if (empty($servers)) {
            $servers = Auth::user()->servers;
        }

        $orchestrator->dispatch($servers);

        return 'Jobs dispatched and the queue is being processed.';
    }

    /**
     * Remove the specified resource from storage
     * Before deleting the server, detach all users from the server to prevent a foreign key error
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Server $server)
    {
        $this->authorize('manage', $server);
        // Detach all users from the server
        $server->users()->detach();
        // Delete the server
        $server->delete();

        // Return to the server index page
        return to_route('server.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultCheckSettings(): array
    {
        return [
            'StatusCode' => ['enabled' => true],
            'SSL_active' => ['enabled' => true],
            'SSL_certificate_valid' => ['enabled' => true],
            'SSL_expiration' => [
                'enabled' => true,
                'input' => ['days' => 5],
            ],
            'ContentRegex' => [
                'enabled' => false,
                'input' => ['pattern' => '/.+/'],
            ],
            'Latency' => [
                'enabled' => true,
                'input' => [
                    'warning_ms' => 600,
                    'fail_ms' => 1500,
                ],
            ],
            'Headers' => [
                'enabled' => false,
                'input' => ['required' => []],
            ],
        ];
    }
}
