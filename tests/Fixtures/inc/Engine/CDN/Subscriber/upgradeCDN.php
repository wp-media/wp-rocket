<?php
return [
        'shouldSetRocketcdnWhenTokenExists' => [
            'config' => [
                'new_version'          => '3.22.0',
                'old_version'          => '3.21.1',
                'options'              => [
                    'cdn' => 1,
                ],
                'rocketcdn_user_token' => 'some-token',
                'rocketcdn_version'    => '',
            ],
            'expected' => [
                'should_update' => true,
                'options'       => [
                    'cdn'      => 1,
                    'cdn_type' => 'rocketcdn',
                ],
            ],
        ],
        'shouldSetRocketcdnWhenVersionConstantExists' => [
            'config' => [
                'new_version'          => '3.22.0',
                'old_version'          => '3.21.1',
                'options'              => [
                    'cdn' => 1,
                ],
                'rocketcdn_user_token' => '',
                'rocketcdn_version'    => '1.0.0',
            ],
            'expected' => [
                'should_update' => true,
                'options'       => [
                    'cdn'      => 1,
                    'cdn_type' => 'rocketcdn',
                ],
            ],
        ],
        'shouldSetByocdnWhenLegacyCdnEnabledAndNoRocketcdn' => [
            'config' => [
                'new_version'          => '3.22.0',
                'old_version'          => '3.21.1',
                'options'              => [
                    'cdn' => 1,
                ],
                'rocketcdn_user_token' => '',
                'rocketcdn_version'    => '',
            ],
            'expected' => [
                'should_update' => true,
                'options'       => [
                    'cdn'      => 1,
                    'cdn_type' => 'byocdn',
                ],
            ],
        ],
        'shouldSetRocketcdnWhenNoCdnConfigured' => [
            'config' => [
                'new_version'          => '3.22.0',
                'old_version'          => '3.21.1',
                'options'              => [],
                'rocketcdn_user_token' => '',
                'rocketcdn_version'    => '',
            ],
            'expected' => [
                'should_update' => true,
                'options'       => [
                    'cdn_type' => 'rocketcdn',
                ],
            ],
        ],
    ];