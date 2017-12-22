<?php namespace ewma\dev\ui\node\controllers\main\content;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function update()
    {
        $this->d('~:cache/' . $this->data('type') . '|', $this->data('value'), RR);

        $this->e('ewma/dev/nodeEditor/update/' . $this->data('type') . '/' . $this->_instance())->trigger();
    }
}
