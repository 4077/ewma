<?php namespace ewma\controllers\main;

class Html extends \Controller
{
    public $singleton = true;

    public function __create()
    {
        $this->css('reset');
        $this->css('presets');
        $this->css('global');

        $this->c('\js\jquery~');
        $this->c('\js\jquery\ui~', ['theme' => 'custom']);

        $this->js('fn');
        $this->js('main');

        if ($this->isSuperuser()) {
            $this->js('dev');
        }
    }

    public function view()
    {
        $v = $this->v();

        $this->app->response->provideCommonJs();

        $this->widget(":body");

        $v->assign([
                       'HEAD_PREPEND' => $this->app->html->renderHeadPrepend(),
                       'HEAD_APPEND'  => $this->app->html->renderHeadAppend(),
                       'TITLE'        => $this->app->html->getTitle(),
                       'META'         => $this->c('>meta:view'),
                       'ASSETS'       => $this->c('>assets:view'),
                       'CONTENT'      => $this->app->html->getContent()
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

    public function setTitle()
    {
        $this->app->html->setTitle($this->data('content'));

        return $this;
    }

    public function setContent()
    {
        $this->app->html->setContent($this->data('content'));

        return $this;
    }

    public function setFavicon()
    {
        $this->app->html->setFavicon($this->data('url'));

        return $this;
    }

    public function headPrepend()
    {
        $this->app->html->headPrepend($this->data('content'));

        return $this;
    }

    public function headAppend()
    {
        $this->app->html->headAppend($this->data('content'));

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
