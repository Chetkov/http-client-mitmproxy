<?php

declare(strict_types=1);

use Chetkov\HttpClientMitmproxy\DefaultRegistry;

return [
    'registry_factory' => static function () {
        $redis = new \Redis();
        $redis->connect('localhost');
        return new DefaultRegistry($redis);
    }
];