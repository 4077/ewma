<?php namespace ewma\remoteCall;

class RemoteCall
{
    public static function getServer($name)
    {
        $serversData = \std\data\sets\Svc::get('ewma/remoteCall:servers');

        if ($serverData = ap($serversData, $name)) {
            $server = new Server($serverData);

            return $server;
        }
    }
}
