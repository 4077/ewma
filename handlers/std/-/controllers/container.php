<?php namespace ewma\handlers\std\controllers;

class Container extends \Controller
{
    public function view()
    {
        return $this->data('content');
    }
}
