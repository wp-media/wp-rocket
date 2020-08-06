<?php

return [
	'test_data' => [
		'testShouldDoNothingWhenBypass' => [
			'config' => [
				'options' => [
					'embeds' => 1,
				],
				'bypass' => true,
			],
			'scripts' => (object) [
				'registered' => [
					'wp-edit-post' => (object) [
						'deps' => [
							'wp-embed',
						],
					],
				],
			],
			'expected' => true,
		],
		'testShouldDoNothingWhenOptionDisabled' => [
			'config' => [
				'options' => [
					'embeds' => 0,
				],
				'bypass' => false,
			],
			'scripts' => (object) [
				'registered' => [
					'wp-edit-post' => (object) [
						'deps' => [
							'wp-embed',
						],
					],
				],
			],
			'expected' => true,
		],
		'testShouldRemoveDependency' => [
			'config' => [
				'options' => [
					'embeds' => 1,
				],
				'bypass' => false,
			],
			'scripts' => (object) [
				'registered' => [
					'wp-edit-post' => (object) [
						'deps' => [
							'wp-embed',
						],
					],
				],
			],
			'expected' => false,
		],
	],
];
