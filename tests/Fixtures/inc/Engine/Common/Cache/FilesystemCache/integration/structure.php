<?php

return [
	'wp-content' => [
		'outside.css' => 'outside',
		'other'       => [
			'keep.css' => 'keep',
		],
		'cache'       => [
			'background-css' => [
				'1' => [
					'example.org' => [
						'a.css'      => 'a',
						'a.css.json' => '[]',
					],
				],
				'2' => [
					'example.org' => [
						'a.css' => 'blog2',
					],
				],
			],
		],
	],
];
