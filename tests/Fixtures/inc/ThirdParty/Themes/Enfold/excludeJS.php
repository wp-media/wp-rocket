<?php

return [
	'testShouldReturnExpected' => [
		'config'   => [
			'excluded'     => [],
		],
		'expected' => [
			'excluded' => [
				'/jquery-migrate(.min)?.js',
				'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
				'var avia_is_mobile',
				'/wp-content/uploads/dynamic_avia/avia-footer-scripts-(.*).js',
			],
		],
	],
];
