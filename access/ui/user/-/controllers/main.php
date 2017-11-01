<?php namespace ewma\access\ui\user\controllers;

class Main extends \Controller
{
    private $user;

    public function __create()
    {
        $this->user = $this->unpackModel('user');
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $user = $this->user;

        $fields = $this->getFields();

        foreach ($fields as $field => $fieldData) {
            if (!isset($fieldData['visible']) || $fieldData['visible']) {
                $label = isset($fieldData['label']) ? $fieldData['label'] : '';

                $v->assign('field', [
                    'LABEL'   => (!isset($fieldData['label_visible']) || $fieldData['label_visible']) ? $label : '',
                    'CLASS'   => isset($fieldData['class']) ? $fieldData['class'] : $field,
                    'CONTROL' => $this->getCellView($fields, $user, $field)
                ]);
            }
        }

        $this->css(':\css\std~');

        $this->app->html->setFavicon(abs_url('-/ewma/favicons/access_users.png'));

        return $v;
    }

    private function getFields()
    {
        return [
            'enabled'  => [
                'label'    => 'Включен',
                'sortable' => true,
                'width'    => 33,
                'control'  => [
                    '^ui/userControls/enabled~:view|table',
                    [
                        'user' => '%row',
                        'mode' => 'normal'
                    ]
                ]
            ],
            'login'    => [
                'label'    => 'Логин',
                'sortable' => true,
                'control'  => [
                    '^ui/userControls/login~:view',
                    [
                        'user' => '%row'
                    ]
                ],
            ],
            'email'    => [
                'label'    => 'e-mail',
                'sortable' => true,
                'control'  => [
                    '^ui/userControls/email~:view',
                    [
                        'user' => '%row'
                    ]
                ]
            ],
            'password' => [
                'label'    => 'Пароль',
                'width'    => 33,
                'sortable' => true,
                'control'  => [
                    '^ui/userControls/password~:view|table',
                    [
                        'user' => '%row',
                        'mode' => 'normal'
                    ]
                ]
            ],
            'groups'   => [
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
            'permissions'   => [
                'field'   => false,
                'label'   => 'Разрешения',
                'control' => [
                    '^ui/userControls/permissions~:view',
                    [
                        'user' => '%row'
                    ]
                ]
            ]
        ];
    }

    private function getCellView($fields, $model, $field)
    {
        $column = $fields[$field];

        if (!empty($column['control'])) {
            $control = $column['control'];

            $controlCall = $this->_call($control);
            $controlCall->data(false, $this->tokenizeData($model, $field, $controlCall->data()));

            $content = $controlCall->perform();
        } else {
            $content = $model->{$field};
        }

        return $content;
    }

    protected function tokenizeData($model, $field, $data)
    {
        $requestDataFlatten = a2f($data);

        foreach ($requestDataFlatten as $path => $value) {
            if ($value === '%row') {
                $requestDataFlatten[$path] = $model;
            } elseif ($value === '%row_id') {
                $requestDataFlatten[$path] = $model->id;
            } elseif ($value === '%instance') {
                $requestDataFlatten[$path] = $this->data['instance'];
            } elseif ($value === '%row_id') {
                $requestDataFlatten[$path] = $model->id;
            } elseif ($value === '%pack') {
                $requestDataFlatten[$path] = pack_model($model);
            } elseif ($value === '%xpack') {
                $requestDataFlatten[$path] = xpack_model($model);
            }

            if (null !== $field) {
                if ($value === '%column_id') {
                    $requestDataFlatten[$path] = $field;
                } elseif ($value === '%value') {
                    $requestDataFlatten[$path] = $model->{$field};
                }
            }
        }

        $output = f2a($requestDataFlatten);

        return $output;
    }
}
