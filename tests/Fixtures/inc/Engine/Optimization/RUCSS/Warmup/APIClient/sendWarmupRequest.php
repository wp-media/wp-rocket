<?php

return [
	'test_data' => [
		'shouldReturnTrueOnSuccess' => [
			'atts' => [
				'url'     => 'https://www.example.com/path/to/styles.css',
				'type'    => 'css',
				'content' => '.error {color: red;}',
			],
			'returnCode' => '200',
			'expected' => true,
		],
		'shouldRetirnFalseOnFailure' => [
			'atts' => [
				'url'     => 'https://www.example.com/path/to/slider.js',
				'type'    => 'js',
				'content' => 'alert("This is a slide");',
			],
			'returnCode' => '404',
			'expected' => false,
		]
	],
];
