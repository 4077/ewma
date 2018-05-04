<?php namespace ewma\controllers\main;

class Html extends \Controller
{
    public $singleton = true;

    public function __create()
    {
        $this->css('reset');
        $this->css('presets');
        $this->css('global');

        $this->c('\jquery~');
        $this->c('\jquery\ui~', ['theme' => 'custom']);

        $this->js('fn');
        $this->js('main');
    }

    public function view()
    {
        $v = $this->v();

        $this->app->response->provideCommonJs();

        $this->widget(":body");

        $v->assign([
                       'TITLE'   => $this->app->html->getTitle(),
                       'META'    => $this->c('>meta:view'),
                       'ASSETS'  => $this->c('>assets:view'),
                       'CONTENT' => $this->app->html->getContent()
                   ]);

        if ($favicon = $this->app->html->getFavicon()) {
            $v->assign('favicon', ['HREF' => $favicon]);
        }

        foreach ($this->app->html->getContainers() as $container) {
            $v->append('CONTENT', $container);
        }

        $v->assign('APP_DATA', j_($this->app->response->getAppData()));

        return $v;
    }

    public function setTitle($value)
    {
        $this->app->html->setTitle($value);

        return $this;
    }

    public function setContent($content)
    {
        $this->app->html->setContent($content);

        return $this;
    }

    public function setFavicon()
    {
        $this->app->html->setFavicon($this->data('url'));

        return $this;
    }

    public function addContainer($name = '')
    {
        $this->app->html->addContainer($name, $this->data('content'));
    }

    public function replaceContainer($name = '')
    {
        $this->app->html->replaceContainer($name, $this->data('content'));
    }

    public function removeContainer($name = '')
    {
        $this->app->html->removeContainer($name);
    }

    public function addCall($name)
    {
        $this->app->html->addCall($name, $this->data('call'));

        return $this;
    }
}
