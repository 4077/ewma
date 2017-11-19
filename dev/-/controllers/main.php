<?php namespace ewma\dev\controllers;

class Main extends \Controller
{
    public function createModule()
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
                $report[] = $this->c('\ewma~cache:reset');
            }

            return $report;
        }
    }

    public function resetGrids()
    {
        $builder = \ewma\models\Session::where('module_namespace', 'std\ui\grid');
        $builder = $this->sessionQueryConds($builder);
        $builder->delete();

        $builder = \ewma\models\Session::where('module_namespace', 'std\ui\grid2');
        $builder = $this->sessionQueryConds($builder);
        $builder->delete();

        return 'grids reset';
    }

    public function resetTrees()
    {
        $builder = \ewma\models\Session::where('module_namespace', 'std\ui\tree');
        $builder = $this->sessionQueryConds($builder);
        $builder->delete();

        return 'trees reset';
    }

    public function resetDialogs()
    {
        $builder = \ewma\models\Session::where('module_namespace', 'std\ui\dialogs');
        $builder = $this->sessionQueryConds($builder);
        $builder->delete();

        return 'dialogs reset';
    }

    public function resetDataEditors()
    {
        $builder = \ewma\models\Session::where('module_namespace', 'std\ui\data');
        $builder = $this->sessionQueryConds($builder);
        $builder->delete();

        return 'data editors reset';
    }

    private function sessionQueryConds($builder)
    {
        if ($this->dataHas('i') || $this->dataHas('instance')) {
            $instance = $this->data('i') or
            $instance = $this->data('instance') or
            $instance = '';

            $builder->where('node_instance', $instance);
        }

        if ($this->dataHas('s') || $this->dataHas('session')) {
            $instance = $this->data('s') or
            $instance = $this->data('session');

            $builder->where('key', $this->app->session->getKey());
        }

        return $builder;
    }

    public function resetSessionEvents()
    {
        \ewma\models\Session::where('module_namespace', 'ewma\sessionEvents')->delete();

        return 'session events reset';
    }

    public function emailmysqldump() ////
    {
        $user = app()->getConfig('databases/default/user');
        $pass = app()->getConfig('databases/default/pass');
        $name = app()->getConfig('databases/default/name');

        $dir = $this->_protected('dump');

        mdir($dir);

        $filePath = $dir . '/' . $name . '.sql';

        exec('mysqldump -u ' . $user . ' -p' . $pass . ' ' . $name . ' > ' . $filePath);

        foreach (l2a($this->data('recipients')) as $recipient) {
            /**
             * @var $mailer \std\mailer\Mailer
             */
            $mailer = $this->c('\std\mailer~:get');

            $mailer->AddAddress($recipient);

            $mailer->From = $this->data('sender/email');
            $mailer->FromName = $this->data('sender/name');
            $mailer->Subject = 'backup';
            $mailer->Body = 'backup';

            $mailer->addAttachment($filePath);

            $mailer->send();
        }

        return \Carbon\Carbon::now()->toDateTimeString();
    }

    public function exec()
    {
        if ($this->isSuperuser()) {
            $cwd = getcwd();

            chdir(app()->root);
            exec($this->data('command'), $output);
            chdir($cwd);

            return $output;
        }
    }
}
