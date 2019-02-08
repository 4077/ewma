<?php namespace ewma\Configs;

use ewma\App\App;
use ewma\Service\Service;

class Configs extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    //
    //
    //

    private $profileConfigsPaths = [];

    public function boot()
    {
        $rootModuleConfigPath = abs_path('config/profile.php');

        if (file_exists($rootModuleConfigPath)) {
            $this->profileConfigsPaths = l2a(require $rootModuleConfigPath);
        }
    }

    private function getConfigDir(\ewma\Modules\Module $module)
    {
        return abs_path('config', str_replace('\\', '/', $module->namespace), '-');
    }

    public function load(\ewma\Modules\Module $module)
    {
        $configDir = $this->getConfigDir($module);

        if (is_dir($configDir)) {
            if (file_exists($configDir . '/main.php')) {
                $config = require $configDir . '/main.php';

                if (file_exists($configDir . '/profile.php')) {
                    $profileConfigsPaths = l2a(require $configDir . '/profile.php');
                } else {
                    $profileConfigsPaths = $this->profileConfigsPaths;
                }

                foreach ($profileConfigsPaths as $profileConfigPath) {
                    ra($config, aread($configDir . '/profiles/' . $profileConfigPath . '.php'));
                }

                return $config;
            }
        }
    }

    public function createAbsentProfiles()
    {
        $created = [];

        if ($profilesList = l2a(aread(abs_path('config/profiles.php')))) {
            foreach ($this->app->modules->getAll() as $module) {
                $moduleDir = abs_path('config', str_replace('\\', '/', $module->namespace), '-');

                if (is_dir($moduleDir)) {
                    foreach ($profilesList as $profilesPath) {
                        $profileFilePath = abs_path('config', str_replace('\\', '/', $module->namespace), '-/profiles', $profilesPath . '.php');

                        if (!file_exists($profileFilePath)) {
                            awrite($profileFilePath, []);

                            $created[] = $profileFilePath;
                        }
                    }
                }
            }
        }

        return $created;
    }

    public function addFromModules()
    {
        return $this->app->configs->updateFromModules(AA);
    }

    public function rewriteFromModules()
    {
        return $this->app->configs->updateFromModules(RR);
    }

    public function updateFromModules($mergeMode = RA)
    {
        $output = [];
        foreach ($this->app->modules->getAll() as $module) {
            if ($config = $this->updateFromModule($module, $mergeMode)) {
                $output[str_replace('\\', '/', $module->namespace)] = $config;
            }
        }

        return $output;
    }

    private function updateFromModule(\ewma\Modules\Module $module, $mergeMode = AA)
    {
        $moduleDir = $module->dir;

        if (file_exists($moduleDir . '/settings.php')) {
            $settings = require $moduleDir . '/settings.php';
        } else {
            $settings = [];
        }

        if (!empty($settings['config'])) {
            $settingsConfig = $settings['config'];

            $configDir = $this->getConfigDir($module);

            $mainConfigFilePath = $configDir . '/main.php';

            if (!file_exists($mainConfigFilePath)) {
                $config = $settingsConfig;
            } else {
                $config = aread($mainConfigFilePath);

                if ($mergeMode == AA) {
                    aa($config, $settingsConfig);
                }

                if ($mergeMode == RA) {
                    ra($config, $settingsConfig);
                }

                if ($mergeMode == RR) {
                    $config = $settingsConfig;
                }
            }

            awrite($mainConfigFilePath, $config);

            return $config;
        }
    }

    public function removeExcessProfiles()
    {

    }
}
