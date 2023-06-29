<?php

return [
    'testShouldBailOutIfCacheMobileIsDisabled' => [
        'config' => [
            'cache_mobile' => 0,
        ],
    ],
    'testShouldEnableSeparateCacheFiles' => [
        'config' => [
            'cache_mobile' => 1,
        ],
    ],
];