<?php namespace ewma\remoteCall\controllers;

class Main extends \Controller
{
    public function send()
    {
        $server = \ewma\remoteCall\RemoteCall::getServer($this->data('server'));

        if ($server) {
            return $server->call($this->data('call/path'), $this->data('call/data'));
        }
    }

    public function perform()
    {
        $handlerUrl = $this->data('handler_url');
        $call = $this->data('call');

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', $handlerUrl, [
            'form_params' => [
                'call' => $call
            ]
        ]);

        $body = $response->getBody()->getContents();

        return _j($body);
    }

    public function handle()
    {
        $call = $this->app->request->request->get('call') or
        $call = $this->app->request->query->get('call');

        $errors = [];

        if (empty($call)) {
            $errors[] = 'empty call';
        } else {
            $serversData = \std\data\sets\Svc::get('ewma/remoteCall:servers');

            if ($thisServer = ap($serversData, $this->_appConfig('env/id'))) {
                $enabled = \std\data\sets\Svc::get('ewma/remoteCall::enabled');

                if ($enabled) {
                    if (isset($thisServer['key'])) {
                        $call = _j64($call, $thisServer['key']);

                        if (!$call) {
                            $errors[] = 'wrong key for env/id=' . $this->_appConfig('env/id');
                        }
                    }
                } else {
                    $errors[] = 'server with env/id=' . $this->_appConfig('env/id') . ' disabled';
                }
            } else {
                $errors[] = 'server for env/id=' . $this->_appConfig('env/id') . ' not configured';
            }
        }

        $response = [
            'errors' => $errors,
            'output' => null
        ];

        if (empty($errors)) {
            $response['output'] = $this->_call($call)->perform();
        }

        return j_($response);
    }
}
