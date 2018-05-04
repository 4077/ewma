<?php namespace ewma\controllers\main\html;

class Meta extends \Controller
{
    public $singleton = true;

    public function set()
    {
        $this->app->html->meta->set($this->data('name'), $this->data('content'), $this->data('http_equiv') ?? false);

        return $this;
    }

    public function setList()
    {
        $list = map($this->data('list'), 'keywords, description');

        foreach ($list as $name => $content) {
            $this->app->html->meta->set($name, $content, $this->data('http_equiv') ?? false);
        }

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
