<?php

return [
	'productTypeShouldDisable' => [
		'config' => [
			'enabled' => true,
			'post' => (object) [
				'post_type' => 'product'
			]
		],
		'expected' => false
	],
	'postTypeShouldReturnSame' => [
		'config' => [
			'enabled' => true,
			'post' => (object) [
				'post_type' => 'post'
			]
		],
		'expected' => true
	],
];
