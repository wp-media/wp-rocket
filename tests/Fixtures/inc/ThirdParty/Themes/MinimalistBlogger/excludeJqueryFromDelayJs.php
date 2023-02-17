<?php
return [
	'testChildThemeShouldReturnExpected' => [
		'config' => [
			'stylesheet'  => 'child-minimalist-blogger',
			'theme-name'  => 'child-minimalist-blogger',
			'excluded' => []
		],
		'expected' => [
			'/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js',
			'/jquery-migrate(.min)?.js',
		]
	],
	'testThemeShouldReturnExpected' => [
		'config' => [
			'stylesheet'  => 'minimalist-blogger',
			'theme-name'  => 'minimalist-blogger',
			'excluded' => []
		],
		'expected' => [
			'/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js',
			'/jquery-migrate(.min)?.js',
		]
	]
];
