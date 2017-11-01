<?php namespace ewma\controllers\html;

class Assets extends \Controller
{
    public $singleton = true;

    public function view()
    {
        $v = $this->v();

        foreach ($this->app->js->getUrls() as $url) {
            $v->assign('js', [
                'SRC' => $url
            ]);
        }

        $jsVersion = $this->app->js->settings['version'];
        foreach ($this->app->response->getJsFilesPaths() as $js) {
            $v->assign('js', [
                'SRC' => abs_url($js . '.js' . ($jsVersion ? '?' . $jsVersion : ''))
            ]);
        }

        foreach ($this->app->css->getUrls() as $url) {
            $v->assign('css', [
                'HREF' => $url
            ]);
        }

        $cssVersion = $this->app->css->settings['version'];
        foreach ($this->app->response->getCssFilesPaths() as $css) {
            $v->assign('css', [
                'HREF' => abs_url($css . '.css' . ($cssVersion ? '?' . $cssVersion : ''))
            ]);
        }

        return $v;
    }
}