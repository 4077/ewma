<?php namespace ewma\callCenter\controllers\main\calls;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function __create()
    {
        $this->a() or $this->lock();
    }

    public function create()
    {
        if ($cat = $this->unxpackModel('cat')) {
            $call = $cat->calls()->create([]);

            $this->e('ewma/callCenter/calls/create', ['cat_id' => $cat->id])->trigger(['call' => $call]);
        }
    }

    public function rearrange()
    {
        foreach ((array)$this->data('sequence') as $n => $id) {
            if ($group = \ewma\callCenter\models\Call::find($id)) {
                $group->position = (int)$n * 10;
                $group->save();
            }
        }
    }
}
