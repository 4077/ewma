<?php namespace ewma\access\ui\users\controllers\main\grid\controls;

class Actions extends \Controller
{
    private $buttons = [
        'open'   => [
            'label' => 'Открыть в окне',
            'class' => 'open',
            'path'  => '~xhr:userDialog'
        ],
        'delete' => [
            'label' => 'Удалить',
            'class' => 'delete',
            'path'  => '~xhr:delete'
        ]
    ];

    public function view()
    {
        $v = $this->v();

        foreach ($this->buttons as $button) {
            $v->assign('button', [
                'CONTENT' => $this->c('\std\ui button:view', [
                    'path'    => $button['path'],
                    'data'    => [
                        'user' => $this->data['user']
                    ],
                    'class'   => 'button ' . $button['class'],
                    'content' => '<div class="icon"></div>',
                    'attrs'   => [
                        'title' => $button['label']
                    ]
                ])
            ]);
        }

        $this->css(':\jquery\ui icons');

        return $v;
    }
}
