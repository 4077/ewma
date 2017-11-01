<?php namespace ewma\Views;

use ewma\App\App;
use ewma\Service\Service;

class Views extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    public $compiledByFilePath = [];

    public function create($templateFilePath = null, $data = [])
    {
        $view = new View;

        $view->setTemplateFilePath($templateFilePath);
        $view->setData($data);

        $this->app->response->addViewsCommonInstructions = true;

        return $view;
    }
}
