<?php namespace ewma\handlers\std\controllers;

class Main extends \Controller
{
    public $singleton = true;

    public function callFunction()
    {
        if ($this->dataHas('function string')) {
            return call_user_func_array($this->data['function'], (array)$this->data('args'));
        }
    }

    public function callMethod()
    {
        if ($this->dataHas('method array')) {
            return call_user_func_array($this->data['method'], (array)$this->data('args'));
        }
    }

    public function callObjectMethod()
    {
        if ($this->dataHas('object object, method string')) {
            return call_user_func_array([$this->data['object'], $this->data['method']], (array)$this->data('args'));
        }
    }

    public function setObjectField()
    {
        if ($this->dataHas('object object, field string')) {
            $this->data['object']->{$this->data['field']} = $this->data('value');
        }
    }

    public function createObject()
    {
        if ($this->dataHas('class_name string')) {
            return new $this->data['class_name']((array)$this->data('args'));
        }
    }

    public function sessionNodeRead()
    {
        if ($this->dataHas('path string')) {
            return $this->app->rootController->s($this->data['path'], (array)$this->data('default_data'));
        }
    }

    public function sessionNodeWrite()
    {
        if ($this->dataHas('path string')) {
            $s = &$this->app->rootController->s($this->data['path']);
            $s = $this->data('data');

            return $s;
        }
    }

    public function &sessionNodeLink()
    {
        if ($this->dataHas('path string')) {
            $s = &$this->app->rootController->s($this->data['path'], (array)$this->data('default_data'));

            return $s;
        }
    }

    public function storageNode()
    {
        if ($this->dataHas('path string')) {
            return $this->app->rootController->d($this->data['path'], (array)$this->data('default_data'));
        }
    }

    public function getModel()
    {
        $modelType = $this->data('class');
        $modelId = $this->data('id');

        if (class_exists($modelType)) {
            $builder = new $modelType;

            return $builder->find($modelId);
        }
    }

    public function redirect()
    {
        $this->app->response->redirect($this->data('url'), $this->data('code'));

        return true;
    }

    public function redirectIfNotUser()
    {
        if (!$this->_user()) {
            $this->app->response->redirect($this->data('url'), $this->data('code'));

            return true;
        }
    }

    public function _j()
    {
        return _j($this->data['json']);
    }

    public function _if()
    {
        return $this->data('value') ? $this->data('true') : $this->data('false');
    }

    public function _return()
    {
        return $this->data('content');
    }

    public function responseCode404() // todo response....
    {
        header("HTTP/1.0 404 Not Found");
    }
}
