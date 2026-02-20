<?php

return [
	'testShouldNotTruncatedWhenNotAllowed' => [
		'config'   => [
			'filter'    => false,
			'value'     => 123,
			'old_value' => 123,
		],
		'expected' => [
			'truncate' => false,
			'return'   => 123,
		],
	],
	'testShouldNotTruncateWhenLogoNotChanged' => [
		'config'   => [
			'filter'    => true,
			'value'     => 123,
			'old_value' => 123,
		],
		'expected' => [
			'truncate' => false,
			'return'   => 123,
		],
	],
	'testShouldTruncateWhenLogoChanged' => [
		'config'   => [
			'filter'    => true,
			'value'     => 456,
			'old_value' => 123,
		],
		'expected' => [
			'truncate' => true,
			'return'   => 456,
		],
	],
];
