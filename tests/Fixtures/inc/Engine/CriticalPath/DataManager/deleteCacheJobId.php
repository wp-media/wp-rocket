<?php

return [
	// Transient exists before running this test.
	[
		'item_url' => 'http://www.example.com/?p=1',
		'expected' => true,
	],
	[
		'item_url' => 'http://www.example.com/?p=2',
		'expected' => true,
	],
	[
		'item_url' => 'http://www.example.com/?p=3',
		'expected' => true,
	],

	// The transient does not exist before this test.
	[
		'item_url' => 'http://www.example.com/lorem-ipsum',
		'expected' => false,
	],
	[
		'item_url' => 'http://www.example.com/minim-veniam',
		'expected' => false,
	],
	[
		'item_url' => 'http://www.example.com/?p=67',
		'expected' => false,
	],
];
