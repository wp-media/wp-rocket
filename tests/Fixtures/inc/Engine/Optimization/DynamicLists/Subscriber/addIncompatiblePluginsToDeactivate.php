<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list'     => [
			'wp-optimize' => 'wp-optimize/wp-optimize.php',
		],
		'expected' => [
			'wp-optimize' => 'wp-optimize/wp-optimize.php',
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'original' => [
			'wp-asset-clean-up' => 'wp-asset-clean-up/wpacu.php',
		],
		'list'     => [
			'wp-optimize' => 'wp-optimize/wp-optimize.php',
		],
		'expected' => [
			'wp-asset-clean-up' => 'wp-asset-clean-up/wpacu.php',
			'wp-optimize'       => 'wp-optimize/wp-optimize.php',
		],
	],
];
