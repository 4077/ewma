<?php namespace ewma\dev\ui\node\controllers\main\cp;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function setType()
    {
        $this->s('~:type|', $this->data('type'), RR);

        $this->app->session->save($this->_module()->namespace);

        $this->e('ewma/dev/nodeEditor/typeSet/' . $this->_instance())->trigger();
    }

    public function save()
    {
        $cache = $this->d('~:cache|');

        foreach ($cache as $type => $code) {
            if (null !== $code) {
                $s = $this->s('~|');

                $filePath = $this->getFilePath($s['module_path'], $s['node_path'], $type);

                write($filePath, $code);

                $this->d('~:cache/' . $type . '|', null, RR);

                if ($type == 'js') {
                    $this->c('\ewma\cache~:reset', ['jsCompiler' => true]);
                    $this->c('\ewma\js~:increaseVersion');
                }

                if ($type == 'less') {
                    $this->c('\ewma\cache~:reset', ['cssCompiler' => true]);
                    $this->c('\ewma\css~:increaseVersion');
                }
            }
        }

        $this->c('~:performCallback:update|');

        $this->e('ewma/dev/nodeEditor/save/' . $this->_instance())->trigger();
    }

    private function getFilePath($modulePath, $nodePath, $type)
    {
        $nodesDir = $modulePath ? 'modules/' . $modulePath : '';

        $filePath = abs_path($nodesDir, '-', $this->getTypeDir($type), $nodePath . '.' . $this->getExtension($type));

        return $filePath;
    }

    private $dataByType = [
        // [type => dir, ext, editor_mode]
        'controller' => ['controllers', 'php', 'php'],
        'js'         => ['js', 'js', 'javascript'],
        'css'        => ['css', 'css', 'css'],
        'less'       => ['less', 'less', 'less'],
        'template'   => ['templates', 'tpl', 'smarty']
    ];

    private function getTypeDir($type)
    {
        return $this->dataByType[$type][0];
    }

    private function getExtension($type)
    {
        return $this->dataByType[$type][1];
    }
}
