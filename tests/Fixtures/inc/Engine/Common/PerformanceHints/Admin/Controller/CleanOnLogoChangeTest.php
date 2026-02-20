<?php

return [
	'testShouldNotTruncateWhenLogoNotChanged' => [
		'config'   => [
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
			'value'     => 456,
			'old_value' => 123,
		],
		'expected' => [
			'truncate' => true,
			'return'   => 456,
		],
	],
];
