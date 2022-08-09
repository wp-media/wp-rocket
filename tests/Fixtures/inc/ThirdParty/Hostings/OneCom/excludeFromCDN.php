<?php

return [
	'testShouldBailOutWithDisabledOneDotComCDN' => [
		'config'      => [
            'oc_cdn_enabled' => false,
            'excluded' => [
                '/wp-content/plugins/some-plugins/(.*).css',
            ],
        ],
		'excluded' => '/wp-includes/(.*)',
	],
	'testShouldExcludeFromCDN' => [
		'config'      => [
            'oc_cdn_enabled' => true,
            'excluded' => [
                '/wp-content/plugins/some-plugins/(.*).css',
            ],
        ],
		'excluded' => '/wp-includes/(.*)',
	],
];
