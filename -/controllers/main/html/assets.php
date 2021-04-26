<?php namespace ewma\controllers\main\html;

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

//        $jsVersion = $this->app->js->settings['version'];
//        foreach ($this->app->response->getJsFilesPaths() as $js) {
//            $v->assign('js', [
//                'SRC' => abs_url($js . '.js' . ($jsVersion ? '?' . $jsVersion : ''))
//            ]);
//        }

        $hrefs = $this->app->js->getHrefs();

        foreach ($hrefs as $href) {
            $v->assign('js', [
                'SRC' => $href
            ]);
        }

        foreach ($this->app->css->getUrls() as $url) {
            $v->assign('css', [
                'HREF' => $url
            ]);
        }

        $hrefs = $this->app->css->getHrefs();

        foreach ($hrefs as $href) {
            $v->assign('css', [
                'HREF' => $href
            ]);
        }

        return $v;
    }
}