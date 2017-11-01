<?php namespace ewma\access\ui\groups\controllers\main;

class Groups extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $selectedGroupId = $this->s('~:selected_group_id');

        $groups = \ewma\models\access\Group::orderBy('system', 'DESC')->orderBy('position')->get();

        foreach ($groups as $group) {
            $groupXPack = xpack_model($group);

            $v->assign('group', [
                'ID'             => $group->id,
                'NAME'           => $group->system
                    ? ($group->name ? $group->name : '...')
                    : $this->c('\std\ui txt:view', [
                        'path'                => '>xhr:rename',
                        'data'                => [
                            'group' => $groupXPack
                        ],
                        'class'               => 'txt',
                        'fitInputToClosest'   => '.group',
                        'placeholder'         => '...',
                        'editTriggerSelector' => $this->_selector(". .group[group_id='" . $group->id . "'] .rename.button"),
                        'content'             => $group->name
                    ]),
                'SYSTEM_CLASS'   => $group->system ? 'system' : '',
                'SORTABLE'       => (int)!$group->system,
                'SELECTED_CLASS' => $group->id == $selectedGroupId ? 'selected' : '',
            ]);

            if (!$group->system) {
                $v->append('group', [
                    'RENAME_BUTTON' => $this->c('\std\ui tag:view', [
                        'attrs'   => [
                            'class' => 'rename button',
                            'hover' => 'hover',
                            'title' => 'Переименовать'
                        ],
                        'content' => '<div class="icon"></div>'
                    ]),
                    'DELETE_BUTTON' => $this->c('\std\ui button:view', [
                        'path'    => '>xhr:delete',
                        'data'    => [
                            'group' => $groupXPack
                        ],
                        'class'   => 'delete button',
                        'content' => '<div class="icon"></div>'
                    ])
                ]);
            }

            $this->c('\std\ui button:bind', [
                'selector' => $this->_selector(". .group[group_id='" . $group->id . "']"),
                'path'     => '>xhr:select',
                'data'     => [
                    'group' => xpack_model($group)
                ]
            ]);
        }

        $v->assign([
                       'CREATE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:create',
                           'class'   => 'create_button',
                           'content' => 'Создать'
                       ])
                   ]);

        $this->c('\std\ui sortable:bind', [
            'selector'       => $this->_selector(". .groups"),
            'items_id_attr'  => 'group_id',
            'path'           => $this->_p('>xhr:rearrange'),
            'plugin_options' => [
                'items'    => "[group_id][sortable='1']",
                'distance' => 15,
                'axis'     => 'y'
            ]
        ]);

        $this->css(':\css\std~, \jquery\ui icons');

        $this->e('ewma/access/groups/create')->rebind(':reload');

        return $v;
    }
}
