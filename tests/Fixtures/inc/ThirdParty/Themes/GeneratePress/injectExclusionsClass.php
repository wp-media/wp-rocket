<?php

return [
    'shouldAddExclusionClass' => [
        'config' => [
            'default_class' => [
                'site-footer',
            ],
        ],
        'expected' => [
            'site-footer',
            'no-wpr-lazyrender',
        ],
    ],
];