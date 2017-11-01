<?php namespace ewma\routers\ui\routers\controllers;

use ewma\routers\Routers;

class Input extends \Controller
{
    public $allow = self::XHR;

    private function performCallback($event, $routerId)
    {
        if ($context = $this->data('context')) {
            if ($callback = $this->d('contexts:callbacks/' . $event . '|' . $context)) {
                $this->_call($callback)->data('router_id', $routerId)->perform();
            }
        }
    }

    public function compile()
    {
        $this->c('^:compile');
    }

    public function create()
    {
        if ($router = Routers::create()) {
            $this->performCallback('create', $router->id);
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/routers');
        } else {
            if ($router = \ewma\routers\models\Router::find($this->data('router_id'))) {
                if ($this->data('confirmed')) {
                    Routers::delete($this->data['router_id']);

                    $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/routers');

                    $this->performCallback('delete', $this->data('router_id'));
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteConfirm|ewma/routers', [
                        'path'          => '\std dialogs/confirm~:view',
                        'data'          => [
                            'confirm_call' => $this->_abs([':delete', $this->data]),
                            'discard_call' => $this->_abs([':delete', $this->data]),
                            'message'      => 'Удалить роутер <b>' . ($router->name ? $router->name : '...') . '</b>?'
                        ],
                        'pluginOptions' => [
                            'resizable' => false
                        ]
                    ]);
                }
            }
        }
    }

    public function toggleEnabled()
    {
        Routers::toggleEnabled($this->data('router_id'));

        $this->performCallback('toggle_enabled', $this->data('router_id'));
    }

    public function reorder()
    {
        Routers::reorder($this->data('sequence'));
    }

    public function select()
    {
        $this->performCallback('select', $this->data('router_id'));
    }
}
