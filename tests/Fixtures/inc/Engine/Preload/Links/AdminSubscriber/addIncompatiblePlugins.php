<?php

return [
	'test_data' => [
		'testShouldReturnDefaultWhenOptionDisabled' => [
			'preload_links' => 0,
			'plugins'       => [
				'w3-total-cache' => 'w3-total-cache/w3-total-cache.php',
			],
			'expected' => [
				'w3-total-cache' => 'w3-total-cache/w3-total-cache.php',
			],
		],
		'testShouldReturnUpdatedArrayWhenOptionEnabled' => [
			'preload_links' => 1,
			'plugins'       => [
				'w3-total-cache' => 'w3-total-cache/w3-total-cache.php',
			],
			'expected' => [
				'w3-total-cache' => 'w3-total-cache/w3-total-cache.php',
				'flying-pages' => 'flying-pages/flying-pages.php',
				'instant-page' => 'instant-page/instantpage.php',
				'quicklink' => 'quicklink/quicklink.php',
			],
		],
	],
];
