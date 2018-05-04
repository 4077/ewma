<?php namespace ewma\Modules;

use ewma\App\App;
use ewma\Service\Service;

class Dev extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    //
    //
    //

    public function create($path, $ns = false, $type = false)
    {
        if ($path = trim($path)) {
            $pathArray = p2a($path);
            $newPathArray = $pathArray;

            $baseModule = $this->app->modules->getRootModule();

            $baseModulePathArray = [];
            foreach ($pathArray as $name) {
                $baseModulePathArray[] = $name;

                if ($module = $this->app->modules->getByPath(a2p($baseModulePathArray))) {
                    $baseModule = $module;

                    array_shift($newPathArray);
                }
            }

            $newModuleNamespace = $ns ?: $baseModule->namespace;

            if (!$type) {
                $type = $baseModule->type;
            }

            $report = [];

            $fullPathArray = p2a($baseModule->path);

            foreach ($newPathArray as $newModuleName) {
                if (is_numeric(substr($newModuleName, 0, 1))) {
                    $newModuleName = '_' . $newModuleName;
                }

                $fullPathArray[] = $newModuleName;
                $fullPath = a2p($fullPathArray);

                if (!$ns) {
                    $newModuleNamespace .= '\\' . $newModuleName;
                }

                $content = '<?php return [';
                $content .= PHP_EOL . "    'namespace' => '" . trim_l_backslash(str_replace('/', '\\', $newModuleNamespace)) . "'";

                if ($type == 'slave') {
                    $content .= "," . PHP_EOL . "    'type'      => 'slave'";
                }

                $content .= PHP_EOL . "];" . PHP_EOL;

                write(abs_path('modules', $fullPath) . '/settings.php', $content);

                $report[] = [
                    'type' => $type,
                    'path' => $fullPath,
                    'ns'   => $newModuleNamespace
                ];
            }

            return $report;
        }
    }

    public function renderNamespace($path)
    {
        if ($path = trim($path)) {
            $pathArray = p2a($path);
            $newPathArray = $pathArray;

            $baseModule = $this->app->modules->getRootModule();

            $baseModulePathArray = [];
            foreach ($pathArray as $name) {
                $baseModulePathArray[] = $name;

                if ($module = $this->app->modules->getByPath(a2p($baseModulePathArray))) {
                    $baseModule = $module;

                    array_shift($newPathArray);
                }
            }

            $newModuleNamespace = $baseModule->namespace;

            foreach ($newPathArray as $newModuleName) {
                if (is_numeric(substr($newModuleName, 0, 1))) {
                    $newModuleName = '_' . $newModuleName;
                }

                $newModuleNamespace .= '\\' . $newModuleName;
            }

            return $newModuleNamespace;
        }
    }

    public function delete($path)
    {
        if ($module = $this->app->modules->getByPath($path)) {
            delete_dir($module->dir);
        }
    }
}
