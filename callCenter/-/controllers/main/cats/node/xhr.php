<?php namespace ewma\callCenter\controllers\main\cats\node;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function __create()
    {
        $this->a() or $this->lock();
    }

    public function select()
    {
        if ($cat = $this->unxpackModel('cat')) {
            $s = &$this->s('~|');

            $s['selected_cat_id'] = $cat->id;

            $this->c('<<:reload');
            $this->c('<<<calls:reload');
            $this->c('<<<output:reload');
        }
    }

    public function create()
    {
        if ($cat = $this->unpackModel('cat')) {
            $newCat = $cat->nested()->create([]);

            $s = &$this->s('~|');
            $s['selected_cat_id'] = $newCat->id;

            $this->e('ewma/callCenter/cats/create', ['cat_id' => $cat->id])->trigger(['cat' => $cat]);
            $this->c('~:reload');
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteCatConfirm|ewma/callCenter');
        } else {
            if ($cat = $this->unpackModel('cat')) {
                $catsIds = \ewma\Data\Tree::getIds($cat);

                $nestedCatsCount = count($catsIds) - 1;

                $calls = \ewma\callCenter\models\Call::whereIn('cat_id', $catsIds)->get();
                $callsCount = count($calls);

                if ($this->dataHas('confirmed') || (!$nestedCatsCount && !$callsCount)) {
                    \ewma\callCenter\models\Cat::whereIn('id', $catsIds)->delete();
                    \ewma\callCenter\models\Call::whereIn('cat_id', $catsIds)->delete();

                    $selectedCatId = &$this->s('~:selected_cat_id|');

                    if (in_array($selectedCatId, $catsIds)) {
                        $selectedCatId = false;
                    }

                    $this->c('~:reload');
                    $this->c('\std\ui\dialogs~:close:deleteCatConfirm|ewma/callCenter');
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteCatConfirm|ewma/callCenter', [
                        'path'          => '~cats/deleteConfirm:view',
                        'data'          => [
                            'confirm_call'      => $this->_abs(':delete|', ['cat' => $this->data['cat']]),
                            'discard_call'      => $this->_abs(':delete|', ['cat' => $this->data['cat']]),
                            'cat_name'          => $cat->name,
                            'calls_count'       => $callsCount,
                            'nested_cats_count' => $nestedCatsCount
                        ],
                        'pluginOptions' => [
                            'resizable' => false
                        ]
                    ]);
                }
            }
        }
    }

    public function rename()
    {
        if ($cat = $this->unpackModel('cat')) {
            $txt = \std\ui\Txt::value($this);

            $cat->name = $txt->value;
            $cat->save();

            $txt->response();

            $this->e('ewma/callCenter/cats/update/name', ['cat_id' => $cat->id])->trigger(['cat' => $cat]);
        }
    }

    public function exchangeDialog()
    {
        if ($cat = $this->unpackModel('cat')) {
            $catNameBranch = trim_l_slash(a2p(\ewma\Data\Table\Transformer::getCells(\ewma\Data\Tree::getBranch($cat), 'name')));

            $this->c('\std\ui\dialogs~:open:exchange|ewma/callCenter', [
                'default'             => [
                    'pluginOptions/width' => 500
                ],
                'path'                => '\std\data\exchange~:view|ewma/callCenter',
                'data'                => [
                    'target_name' => '#' . $cat->id . ' ' . $catNameBranch,
                    'import_call' => $this->_abs('app/exchange:import', ['cat' => pack_model($cat)]),
                    'export_call' => $this->_abs('app/exchange:export', ['cat' => pack_model($cat)])
                ],
                'pluginOptions/title' => 'call-center'
            ]);
        }
    }
}
