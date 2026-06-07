<?php
return [
	'testShouldReturnDefaultWhenCdnTypeIsEmpty' => [
		'config' => [
			'input' => [
				'cdn_type' => '',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
	],
	'testShouldReturnRocketCdnWhenCdnTypeIsRocketCdn' => [
		'config' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
	],
	'testShouldReturnByoCdnWhenCdnTypeIsByoCdn' => [
		'config' => [
			'input' => [
				'cdn_type' => 'byocdn',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'byocdn',
			]
		],
	],
	'testShouldReturnDefaultWhenCdnTypeIsInvalid' => [
		'config' => [
			'input' => [
				'cdn_type' => 'invalid',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
	],
	'testShouldSanitizeCdnType' => [
		'config' => [
			'input' => ['cdn_type' => '<b>byocdn</b>'],
		],
		'expected' => [
			'input' => ['cdn_type' => 'rocketcdn'],
		],
	],
];
