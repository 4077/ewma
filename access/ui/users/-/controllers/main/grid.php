<?php namespace ewma\access\ui\users\controllers\main;

use ewma\models\access\User as UserModel;

class Grid extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $filter = $this->getFilter();

        $v->assign([
                       'CONTENT' => $this->c('\std\ui\grid~:view|' . $this->_nodeId(), [
                           'set'      => [
                               'filter' => $filter
                           ],
                           'defaults' => [
                               'model'   => UserModel::class,
                               'filter'  => $filter,
                               'pager'   => ['page' => 1, 'per_page' => 10],
                               'sorter'  => ['name' => 'ASC'],
                               'columns' => $this->getColumns()
                           ]
                       ])
                   ]);

        $this->e('ewma/access/users/delete')->rebind(':reload');
        $this->e('ewma/access/users/create')->rebind(':reload');

        $this->css(':\jquery\ui icons');

        return $v;
    }

    private function getFilter()
    {
        return [];
    }

    private function getColumns()
    {
        return [
            'id'          => [
                'label'    => '#',
                'sortable' => true
            ],
            'enabled'     => [
                'label'    => 'Включен',
                'sortable' => true,
                'width'    => 33,
                'control'  => [
                    '^ui/userControls/enabled~:view|grid',
                    [
                        'user' => '%row',
                        'mode' => 'compact'
                    ]
                ]
            ],
            'login'       => [
                'label'    => 'Логин',
                'sortable' => true,
                'control'  => [
                    '^ui/userControls/login~:view',
                    [
                        'user' => '%row'
                    ]
                ],
            ],
            'email'       => [
                'label'    => 'e-mail',
                'sortable' => true,
                'control'  => [
                    '^ui/userControls/email~:view',
                    [
                        'user' => '%row'
                    ]
                ]
            ],
            'phone'       => [
                'label'    => 'Телефон',
                'width'    => '160, 160 -',
                'sortable' => true,
                'control'  => [
                    '^ui/userControls/phone~:view',
                    [
                        'user' => '%row'
                    ]
                ]
            ],
            'password'    => [
                'label'    => 'Пароль',
                'width'    => 33,
                'sortable' => true,
                'control'  => [
                    '^ui/userControls/password~:view|grid',
                    [
                        'user' => '%row',
                        'mode' => 'compact'
                    ]
                ]
            ],
            'groups'      => [
                'field'   => false,
                'label'   => 'Группы',
                'width'   => 250,
                'control' => [
                    '^ui/userControls/groups~:view',
                    [
                        'user' => '%row'
                    ]
                ]
            ],
            'permissions' => [
                'field'   => false,
                'label'   => 'Разрешения',
                'width'   => '52, 100 200',
                'control' => [
                    '^ui/userControls/permissions~:view',
                    [
                        'user' => '%row'
                    ]
                ]
            ],
            'actions'     => [
                'label'         => 'Действия',
                'label_visible' => false,
                'width'         => 66,
                'field'         => false,
                'control'       => [
                    '>controls/actions:view',
                    [
                        'user' => '%xpack'
                    ]
                ]
            ]
        ];
    }
}
