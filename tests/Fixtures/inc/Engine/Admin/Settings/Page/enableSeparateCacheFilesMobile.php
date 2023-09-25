<?php

return [
    'testShouldBailOutIfCacheMobileIsDisabled' => [
        'config' => [
            'cache_mobile' => 0,
            'do_caching_mobile_files' => 0,
        ],
    ],
    'testShouldBailOutIfSeparateCacheisAlreadyEnabled' => [
        'config' => [
            'cache_mobile' => 1,
            'do_caching_mobile_files' => 1,
        ],
    ],
    'testShouldEnableSeparateCacheFiles' => [
        'config' => [
            'cache_mobile' => 1,
            'do_caching_mobile_files' => 1,
        ],
    ],
];