<?php namespace ewma\remoteCall;

class Server
{
    private $handlerUrl;

    private $key;

    public function __construct($data)
    {
        $scheme = ap($data, 'protocol') or
        $scheme = 'http';

        $host = $data['host'];
        $handlerRoute = $data['route'];

        $this->handlerUrl = $scheme . '://' . path($host, $handlerRoute) . '/';
        $this->key = ap($data, 'key');
    }

    public function call($path, $data = [])
    {
        $call = [$path, $data];

        if (null !== $this->key) {
            $call = j64_($call, $this->key);
        }

        return appc('\ewma\remoteCall~:perform', [
            'handler_url' => $this->handlerUrl,
            'call'        => $call
        ]);
    }
}
