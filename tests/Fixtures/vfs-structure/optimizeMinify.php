<?php

return [
	'wordpress' => [
		'wp-includes' => [
			'js'  => [
				'jquery' => [
					'jquery.js' => 'jquery',
				],
			],
			'css' => [
				'dashicons.min.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
			],
		],
		'wp-content'  => [
			'cache'   => [
				'min' => [
					'1' => [],
				],
			],
			'themes'  => [
				'twentytwenty' => [
					'style.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
					'assets'    => [
						'script.js' => 'test',
					],
				],
			],
			'plugins' => [
				'hello-dolly' => [
					'style.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
					'script.js' => 'test',
				],
			],
		],
	],
];
