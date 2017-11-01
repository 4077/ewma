<?php namespace ewma\handlers\ui\controllers;

class Callbacks extends \Controller
{
    public function createCat()
    {
        $selectedCatId = &$this->s('~:selected_cat_id');
        $selectedCatId = $this->data('cat_id');

        $this->reload();
    }

    public function selectCat()
    {
        $selectedCatId = &$this->s('~:selected_cat_id');
        $selectedCatId = $this->data('cat_id');

        $this->reload();
    }

    public function catUpdate()
    {
        $this->reload();
    }

    public function createItem()
    {
        $selectedItemId = &$this->s('~:selected_item_id_by_cat_id/' . $this->data('cat_id'));
        $selectedItemId = $this->data('item_id');

        $this->reload();
    }

    public function selectItem()
    {
        $selectedItemId = &$this->s('~:selected_item_id_by_cat_id/' . $this->data('cat_id'));
        $selectedItemId = $this->data('item_id');

        $this->reload();
    }

    //
    // reload
    //

    private function reload()
    {
        $this->c('~:reload');
    }
}
