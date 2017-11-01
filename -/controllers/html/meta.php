<?php namespace ewma\controllers\html;

class Meta extends \Controller
{
    public $singleton = true;

    public function set($name, $content, $httpEquiv = false)
    {
        $this->app->html->meta->set($name, $content, $httpEquiv);

        return $this;
    }

    public function view()
    {
        $tags = [];

        foreach ($this->app->html->meta->get() as $name => $data) {
            $attrs = [];

            $attrs[$data['equiv'] ? 'http-equiv' : 'name'] = $name;
            $attrs['content'] = $data['content'];

            $tags[] = $this->c('\std\ui tag:view:meta', [
                'attrs' => $attrs
            ]);
        }

        return implode(PHP_EOL, $tags);
    }
}
