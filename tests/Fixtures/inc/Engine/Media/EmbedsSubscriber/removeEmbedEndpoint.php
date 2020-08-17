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
			'endpoints'  => [
                '/oembed/1.0/embed' => [],
                '/oembed/1.0/proxy' => [],
                '/wp/v2' => [],
                '/wp/v2/posts' => [],
            ],
			'expected' => [
                '/oembed/1.0/embed' => [],
                '/oembed/1.0/proxy' => [],
                '/wp/v2' => [],
                '/wp/v2/posts' => [],
            ],
		],
		'testShouldDoNothingWhenOptionDisabled' => [
			'config' => [
				'options' => [
					'embeds' => 0,
				],
				'bypass' => false,
			],
			'endpoints'  => [
                '/oembed/1.0/embed' => [],
                '/oembed/1.0/proxy' => [],
                '/wp/v2' => [],
                '/wp/v2/posts' => [],
            ],
			'expected' => [
                '/oembed/1.0/embed' => [],
                '/oembed/1.0/proxy' => [],
                '/wp/v2' => [],
                '/wp/v2/posts' => [],
            ],
		],
		'testShouldReturnEndpointsWithoutEmbeds' => [
			'config' => [
				'options' => [
					'embeds' => 1,
				],
				'bypass' => false,
			],
			'endpoints'  => [
                '/oembed/1.0/embed' => [],
                '/oembed/1.0/proxy' => [],
                '/wp/v2' => [],
                '/wp/v2/posts' => [],
            ],
			'expected' => [
                '/oembed/1.0/proxy' => [],
                '/wp/v2' => [],
                '/wp/v2/posts' => [],
            ],
		],
	],
];
