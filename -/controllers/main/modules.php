<?php namespace ewma\controllers\main;

class Modules extends \Controller
{
    public function create()
    {
        if ($newModulePath = $this->data('path')) {
            $module = $this->app->modules->getByPath($this->data('in'));

            $newModulePathArray = p2a($newModulePath);
            $modulePath = $module->path;
            $newModuleNamespace = $this->data('ns') ? $this->data['ns'] : $module->namespace;

            $type = 'master';

            if ($module->type == 'slave') {
                $type = 'slave';
            }

            if ($this->dataHas('master')) {
                $type = 'master';
            }

            if ($this->dataHas('slave')) {
                $type = 'slave';
            }

            $report = [];

            foreach ($newModulePathArray as $newModuleName) {
                if ($modulePath) {
                    $moduleDirAbsPath = abs_path('modules', $modulePath);

                    $newModuleDirAbsPath = abs_path($moduleDirAbsPath, $newModuleName);
                } else {
                    $newModuleDirAbsPath = abs_path('modules', $newModuleName);
                }

                if (file_exists($newModuleDirAbsPath)) {
                    return 'directory ' . $newModuleDirAbsPath . ' already exists';
                } else {
                    if (!$this->data('ns')) {
                        $newModuleNamespace .= '\\' . $newModuleName;
                    }

                    $content = '<?php return [';
                    $content .= PHP_EOL . "    'namespace' => '" . trim_l_backslash($newModuleNamespace) . "'";

                    if ($type == 'slave') {
                        $content .= "," . PHP_EOL . "    'type'      => 'slave'";
                    }

                    $content .= PHP_EOL . "];" . PHP_EOL;

                    write($newModuleDirAbsPath . '/settings.php', $content);

                    $modulePath .= '/' . $newModuleName;
                    $report[] = 'created ' . $type . ' path=' . $modulePath . ' ns=' . $newModuleNamespace;

                    $type = 'slave';
                }
            }

            if ($this->dataHas('reset')) {
                $report += $this->c('\ewma~cache:reset');
            }

            return $report;
        }
    }
}
