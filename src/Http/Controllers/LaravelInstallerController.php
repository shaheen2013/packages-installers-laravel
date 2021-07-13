<?php

namespace Mediusware\LaravelInstaller\Http\Controllers;

use Artisan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Log;
use Mediusware\LaravelInstaller\Http\Requests\SetupStoreRequest;

class LaravelInstallerController extends Controller
{
    private $env           = [];
    private $envNew        = '';
    private $envExample    = [];
    private $broadcastList = [
        'pusher' => 'pusher_app',
        'redis'  => 'redis',
    ];

    /**
     * LaravelInstallerController constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('LaravelInstaller::index');
    }

    /**
     * @return JsonResponse
     */
    public function getServerRequirements(): JsonResponse
    {
        $requirements = collect(config('laravel_installer.requirements'))->map(function ($requirement) {
            return [
                'title'  => $requirement,
                'status' => extension_loaded($requirement)
            ];
        });

        $requirements = $requirements->toArray();
        array_unshift($requirements, [
            'title'  => 'php',
            'status' => version_compare(phpversion(), config('laravel_installer.php.version')) != -1,
        ]);

        return response()->json(collect($requirements));
    }

    /**
     * @return JsonResponse
     */
    public function getDirectoryPermission(): JsonResponse
    {
        $directories = collect(config('laravel_installer.directories'))->map(function ($dir) {
            $permission = decoct(fileperms(base_path($dir)) ?? '00000');
            return [
                "title"      => $dir,
                "permission" => substr($permission, -3),
                "status"     => substr($permission, -3) == '777',
            ];
        });
        return response()->json($directories);
    }

    /**
     * @param SetupStoreRequest $request
     */
    public function saveSetup(SetupStoreRequest $request)
    {
        $this->getEnv()
            ->formatApp($request->app ?? '')
            ->formatDatabase($request->database ?? '')
            ->formatMail($request->mail ?? '')
            ->formatBroadcasting($request->broadcasting ?? '')
            ->formatStorage($request->storage ?? '')
            ->setEnv()
            ->saveEnv(true);

        dd($this->env, $this->envNew, $this->envExample, $request->all());
    }

    /**
     * @param $key
     * @return JsonResponse
     */
    public function getSetup($key): JsonResponse
    {
        $env = $this->{'get' . ucfirst($key)}($key);
        return response()->json($env);
    }

    /**
     * @param $key
     * @return Collection
     */
    private function getApp($key): Collection
    {
        return collect(config($key))->except([
            'asset_url',
            'timezone',
            'locale',
            'fallback_locale',
            'faker_locale',
            'key',
            'cipher',
            'providers',
            'aliases',
        ]);
    }

    /**
     * @param $key
     * @return Collection
     */
    private function getDatabase($key): Collection
    {
        return collect(config($key))->only([
            'connections'
        ]);
    }

    /**
     * @param $key
     * @return Collection
     */
    private function getMail($key): Collection
    {
        return collect(config($key))->except([
            'connections'
        ]);
    }

    /**
     * @param $key
     * @return Collection
     */
    private function getBroadcasting($key): Collection
    {
        return collect(config($key))->only(['connections'])->except(['connections.null']);
    }

    /**
     * @param $key
     * @return Collection
     */
    private function getFilesystems($key): Collection
    {
        return collect(config($key));
    }

    /**
     * @param $app
     * @return LaravelInstallerController
     */
    private function formatApp($app): LaravelInstallerController
    {
        collect($app)->map(function ($item, $index) {
            $this->env[strtoupper("app_$index")] = $item;
        });

        return $this;
    }

    /**
     * @param $db
     * @return LaravelInstallerController
     */
    private function formatDatabase($db): LaravelInstallerController
    {
        collect($db)->map(function ($item, $index) {
            $this->env[strtoupper("db_$index")] = $item;
        });

        return $this;
    }

    /**
     * @param $mail
     * @return LaravelInstallerController
     */
    private function formatMail($mail): LaravelInstallerController
    {
        collect($mail)->map(function ($item, $index) {
            $this->env[strtoupper("mail_$index")] = $item;
        });

        return $this;
    }

    /**
     * @param $broadcasting
     * @return LaravelInstallerController
     */
    private function formatBroadcasting($broadcasting): LaravelInstallerController
    {
        collect($broadcasting)->map(function ($item, $index) use ($broadcasting) {
            $this->env[strtoupper($this->broadcastList[$broadcasting['broadcast']] . "_$index")] = $item;
        });

        return $this;
    }

    /**
     * @param $storage
     * @return LaravelInstallerController
     */
    private function formatStorage($storage): LaravelInstallerController
    {
        collect($storage)->map(function ($item, $index) use ($storage) {
            $this->env[strtoupper($index)] = $item;
        });

        return $this;
    }

    /**
     * @return LaravelInstallerController
     */
    private function getEnv(): LaravelInstallerController
    {
        $env              = file_get_contents(base_path('.env.example'), true);
        $this->envExample = explode("\n", $env);

        return $this;
    }

    /**
     * @return LaravelInstallerController
     */
    private function setEnv(): LaravelInstallerController
    {
        $envNew = collect($this->envExample)->map(function ($val) {
            $env = explode('=', $val);

            if (!str_contains($env[1] ?? '', '$'))
                $env[1] = $this->addQuotation($this->env[$env[0]] ?? $env[1] ?? '');

            Log::info($env[0]
                ? implode('=', $env)
                : '');

            return $env[0]
                ? implode('=', $env)
                : '';
        })->toArray();

        $this->envNew = implode("\n", $envNew);

        return $this;
    }

    /**
     * @param bool $runOptimize
     */
    private function saveEnv(bool $runOptimize = false): void
    {
        file_put_contents(base_path('.env'), $this->envNew);
        if ($runOptimize) {
            Artisan::call('optimize:clear');
        }

        Artisan::call('key:generate');
    }

    /**
     * @param string $str
     * @return string
     */
    private function addQuotation(string $str): string
    {
        if (strpos($str, ' ') !== false) {
            $str = '"' . $str . '"';
        }

        return $str;
    }

}
