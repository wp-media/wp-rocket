<?php
return [
	'test_data' => [
		'testShouldReturnExpected' => [
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
	]
];
