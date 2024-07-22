<?php

return [
	'shouldReturnArray' => [
		'config' => [
			'lists' => (object) [
				'exclude_js_template' => [
					'data-minify=',
					'data-no-minify=',
				],
			]
		],
		'expected' => [
			'lists' => (object) [
				'exclude_js_template' => [
					'data-minify=',
					'data-no-minify=',
				],
			],
		],
	],
];
