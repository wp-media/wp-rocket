<?php

return [
	'testShouldReturnEmptryStringWhenNotObject' => [
		'data'     => [],
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => '',
	],
	'testShouldReturnValueWhenPropertySet' => [
		'data'     => json_decode( json_encode( [
			'ID'                   => 1,
			'upgrade_infinite_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
		] ) ),
		'expected' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
	],
];
