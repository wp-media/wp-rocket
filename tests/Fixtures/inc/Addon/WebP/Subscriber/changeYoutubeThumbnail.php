<?php
return [
	'hasWebpShouldChangeExtension' => [
		'config' => [
			'has_webp' => true,
			'extension' => 'jpg'
		],
		'expected' => 'webp'
	],
	'hasNotWebShouldReturnDefault' => [
		'config' => [
			'has_webp' => false,
			'extension' => 'jpg'
		],
		'expected' => 'jpg'
	]
];
