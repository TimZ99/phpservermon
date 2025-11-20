<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServerUpdateRequest;
use App\Models\Server;
use App\Models\User;
use App\Services\ServerChecks\RunServerCheckService;
use App\Services\ServerChecks\ServerCheckRegistry;
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

        $server = Server::with([
            'users:id,name',
            'check_histories' => fn ($query) => $query->latest('created_at')->take(50),
        ])->findOrFail($server->id);
        $activeRunId = session("server_run.{$server->id}");
        $runCompleted = false;
        if ($activeRunId && $server->last_check_run_id === $activeRunId) {
            $runCompleted = true;
            session()->forget("server_run.{$server->id}");
            $activeRunId = null;
        }

        $registry = app(ServerCheckRegistry::class);

        return view('server.show', [
            'server' => $server,
            'checkSettings' => $server->check_settings ?? [],
            'checkDefinitions' => $registry->all(),
            'activeRunId' => $activeRunId,
            'runCompleted' => $runCompleted,
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

        $registry = app(ServerCheckRegistry::class);
        $server = new Server;
        $server->setRelation('users', collect());

        return view('server.create', [
            'server' => $server,
            'users' => User::where('suspended', false)->select('id', 'name')->get(),
            'defaultCheckSettings' => $registry->defaults(),
            'checkDefinitions' => $registry->all(),
        ]);
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
            $data = $request->safe()->only(['name', 'ip', 'port']);
            $server = Server::make($data);
            $server->check_settings = $this->buildCheckSettingsFromRequest($request, $server);
            $server->save();
            $this->syncServerUsers($server, $request->input('users', []));

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
        $server = Server::with('users')->find($server->id);
        $registry = app(ServerCheckRegistry::class);

        return view('server.edit', [
            'server' => $server,
            // Get id and name for all users that are not suspended
            'users' => User::where('suspended', false)->select('id', 'name')->get(),
            'defaultCheckSettings' => $registry->defaults(),
            'checkDefinitions' => $registry->all(),
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
        $this->syncServerUsers($server, $request->input('users', []));
        /**
         * Update the server
         * Fill the server with the validated data
         */
        $server->fill($request->validated());
        $server->check_settings = $this->buildCheckSettingsFromRequest($request, $server);
        $server->save();

        // Return the server page with the updated server
        return to_route('server.show', $server->id);
    }

    /**
     * Run the job for the specified server
     *
     * @return \Illuminate\Http\Response
     */
    public function runJob(Server $server, RunServerCheckService $runServerCheck)
    {
        $this->authorize('check', $server);

        $runServerCheck->handle([$server]);

        return to_route('server.show', $server->id)->with('check_dispatched', true);
    }

    public function runSingleCheck(Server $server, string $check, RunServerCheckService $runServerCheck, ServerCheckRegistry $registry)
    {
        $this->authorize('check', $server);

        $checkDefinitions = $registry->all();
        if (! array_key_exists($check, $checkDefinitions)) {
            abort(404);
        }

        $enabled = data_get($server->check_settings, "{$check}.enabled", false);
        if (! $enabled) {
            return to_route('server.show', $server->id)->with('check_error', __('This check is disabled.'));
        }

        $runServerCheck->handle([$server], true, [$check]);

        return to_route('server.show', $server->id)->with([
            'check_dispatched' => true,
            'check_name' => $check,
        ]);
    }

    /**
     * Run a batch process on the given servers.
     *
     * @param  array  $servers  An array of servers to run the batch process on.
     *                          Each element should be an instance of \App\Models\Server.
     * @return void
     */
    public function runBatch(RunServerCheckService $runServerCheck, $servers = [])
    {
        $this->authorize('checkAny', Server::class);
        if (empty($servers)) {
            $servers = Auth::user()->servers;
        }

        $runServerCheck->handle($servers);

        return to_route('server.monitor')->with('check_dispatched', true);
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

    protected function buildCheckSettingsFromRequest(ServerUpdateRequest $request, ?Server $server = null): array
    {
        $defaults = app(ServerCheckRegistry::class)->defaults();
        $existing = $server?->check_settings ?? [];
        $base = array_replace_recursive($defaults, $existing);
        $input = $request->input('check_settings', []);

        if (! is_array($input) || empty($input)) {
            return $base;
        }

        $settings = [];

        foreach ($defaults as $name => $config) {
            $current = $base[$name] ?? $config;
            $enabled = data_get($input, "{$name}.enabled");
            if ($enabled === null) {
                $enabled = data_get($current, 'enabled', false);
            }

            $current['enabled'] = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);

            switch ($name) {
                case 'SSL_expiration':
                    $days = data_get($input, "{$name}.days");
                    if ($days === null) {
                        $days = data_get($current, 'input.days', 5);
                    }
                    $current['input']['days'] = max(1, (int) $days);
                    break;
                case 'ContentRegex':
                    $pattern = data_get($input, "{$name}.pattern");
                    if ($pattern === null) {
                        $pattern = data_get($current, 'input.pattern', '');
                    }
                    $current['input']['pattern'] = trim((string) $pattern);
                    break;
                case 'Latency':
                    $warning = data_get($input, "{$name}.warning_ms");
                    if ($warning === null) {
                        $warning = data_get($current, 'input.warning_ms', 600);
                    }
                    $fail = data_get($input, "{$name}.fail_ms");
                    if ($fail === null) {
                        $fail = data_get($current, 'input.fail_ms', 1500);
                    }

                    $warning = max(1, (int) $warning);
                    $fail = max($warning, (int) $fail);

                    $current['input']['warning_ms'] = $warning;
                    $current['input']['fail_ms'] = $fail;
                    break;
                case 'Headers':
                    $raw = data_get($input, "{$name}.required", null);
                    if ($raw === null) {
                        $requirements = data_get($current, 'input.required', []);
                    } else {
                        $requirements = $this->parseHeaderRequirements($raw);
                    }
                    $current['input']['required'] = $requirements;
                    break;
            }

            $settings[$name] = $current;
        }

        return $settings;
    }

    /**
     * @return array<string, string|null>
     */
    protected function parseHeaderRequirements(?string $raw): array
    {
        if ($raw === null) {
            return [];
        }

        $lines = preg_split("/\r?\n/", trim($raw)) ?: [];
        $requirements = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $requirements[trim($name)] = trim($value) === '' ? null : trim($value);
            } else {
                $requirements[$line] = null;
            }
        }

        return $requirements;
    }

    protected function syncServerUsers(Server $server, array $userIds): void
    {
        $ids = empty($userIds)
            ? []
            : User::whereIn('id', $userIds)
                ->where('suspended', false)
                ->pluck('id')
                ->all();

        $server->users()->sync($ids);
    }
}
