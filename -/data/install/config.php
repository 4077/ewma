<?php return [
    'eventDispatchers' => [
        '/eventDispatcher'
    ],
    'routers'          => [
        // [path/to/router/controller => use html wrapper]
        '\ewma\remoteCall router' => false,
        '\ewma router'            => true,
        '\ewma\routers router'    => false
    ]
];
