<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $actions = [];
        foreach ($this->actions() as $key => $action) {
            $actions[$key] = [
                'title' => $action['title'],
                'description' => $action['description'],
            ];
        }

        return view('setting.index', [
            'actions' => $actions,
        ]);
    }

    public function run(Request $request)
    {
        @set_time_limit(0);
        $key = (string) $request->input('action');
        $actions = $this->actions();
        if (!isset($actions[$key])) {
            return response()->json([
                'ok' => false,
                'message' => 'That action is not available.',
            ], 422);
        }

        $action = $actions[$key];
        try {
            Artisan::call($action['command'], $action['parameters']);
            $output = trim(Artisan::output());

            return response()->json([
                'ok' => true,
                'message' => $action['success'],
                'output' => $output !== '' ? $output : 'Finished.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Setting action failed: ' . $action['command'] . ' ' . $e->getMessage());

            return response()->json([
                'ok' => false,
                'message' => 'The action could not be completed.',
                'output' => $e->getMessage(),
            ], 422);
        }
    }

    private function actions()
    {
        return [
            'cache' => [
                'title' => 'Clear application cache',
                'description' => 'Removes cached application data.',
                'command' => 'cache:clear',
                'parameters' => [],
                'success' => 'Application cache cleared.',
            ],
            'config' => [
                'title' => 'Clear config cache',
                'description' => 'Removes the cached configuration so .env changes are read again.',
                'command' => 'config:clear',
                'parameters' => [],
                'success' => 'Config cache cleared.',
            ],
            'route' => [
                'title' => 'Clear route cache',
                'description' => 'Removes the cached route list.',
                'command' => 'route:clear',
                'parameters' => [],
                'success' => 'Route cache cleared.',
            ],
            'view' => [
                'title' => 'Clear view cache',
                'description' => 'Removes compiled Blade views.',
                'command' => 'view:clear',
                'parameters' => [],
                'success' => 'View cache cleared.',
            ],
            'optimize' => [
                'title' => 'Clear all caches',
                'description' => 'Clears config, routes, views, and the application cache together.',
                'command' => 'optimize:clear',
                'parameters' => [],
                'success' => 'All caches cleared.',
            ],
            'migrate' => [
                'title' => 'Run migrations',
                'description' => 'Runs pending database migrations. Existing diamond records stay in place.',
                'command' => 'migrate',
                'parameters' => ['--force' => true],
                'success' => 'Migrations finished.',
            ],
        ];
    }
}
