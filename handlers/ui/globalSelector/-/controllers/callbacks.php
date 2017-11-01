<?php namespace ewma\handlers\ui\assignments\globalSelector\controllers;

class Callbacks extends \Controller
{
    public function selectCat()
    {
        $selectedCatId = &$this->s('~:selected_cat_id');
        $selectedCatId = $this->data('cat_id');

        $this->c('~:reload', false, 'context');
    }
}
