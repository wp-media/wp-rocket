<?php

return [
	'testShouldBailOutWithDisabledOneDotComCDN' => [
		'config'      => [
            'oc_cdn_enabled' => false,
            'excluded' => [],
        ],
		'expected' => [],
	],
	'testShouldExcludeFromCDN' => [
		'config'      => [
            'oc_cdn_enabled' => true,
            'excluded' => [],
        ],
		'expected' => [
            '/wp-includes/(.*)'
        ],
	],
];
