<?php

return [
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
				'style-font-face.min.css' => '@font-face { font-family: Helvetica; }',
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
];
