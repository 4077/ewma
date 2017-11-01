<?php namespace ewma\access\ui\groups\controllers\main\groups;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function create()
    {
        $group = \ewma\access\Groups::create();

        $this->e('ewma/access/groups/create')->trigger(['group' => $group]);
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/access/groups');
        } else {
            if ($group = $this->unpackModel('group')) {
                if ($this->data('confirmed')) {
                    \ewma\access\Groups::delete($group);

                    $s = &$this->s('~');

                    if ($s['selected_group_id'] == $group->id) {
                        if ($otherGroup = \ewma\models\access\Group::where('position', '<', $group->position)->orderBy('position', 'DESC')->first()) {
                            $s['selected_group_id'] = $otherGroup->id;
                        } elseif ($otherGroup = \ewma\models\access\Group::orderBy('position', 'ASC')->first()) {
                            $s['selected_group_id'] = $otherGroup->id;
                        }
                    }

                    $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/access/groups');

                    $this->e('ewma/access/groups/delete')->trigger(['group' => $group]);
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteConfirm|ewma/access/groups', [
                        'path'            => '\std dialogs/confirm~:view',
                        'data'            => [
                            'confirm_call' => $this->_abs([':delete|', ['group' => $this->data['group']]]),
                            'discard_call' => $this->_abs([':delete|', ['group' => $this->data['group']]]),
                            'message'      => 'Удалить группу <b>' . ($group->name ? $group->name : '...') . '</b>?'
                        ],
                        'forgot_on_close' => true,
                        'pluginOptions'   => [
                            'resizable' => false
                        ]
                    ]);
                }
            }
        }
    }

    public function rearrange()
    {
        if ($this->dataHas('sequence')) {
            foreach ((array)$this->data['sequence'] as $n => $groupId) {
                if (is_numeric($n) && $group = \ewma\models\access\Group::find($groupId)) {
                    $group->position = $n * 10;
                    $group->save();
                }
            }
        }
    }

    public function select()
    {
        if ($group = $this->unxpackModel('group')) {
            $selectedGroupId = &$this->s('~:selected_group_id');

            $selectedGroupId = $group->id;

            $this->c('~:reload'); // по идее нужно перезагружать только группы и разрешения, модули не нужно.

        }
    }

    public function rename()
    {
        if ($group = $this->unxpackModel('group')) {
            $txt = \std\ui\Txt::value($this);

            $group->name = $txt->value;
            $group->save();

            $txt->response();

            $this->e('ewma/access/groups/update/name', ['group_id' => $group->id])->trigger(['group' => $group]);
        }
    }
}
