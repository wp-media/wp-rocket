<?php

return [
	// Transient exists before running this test.
	[
		'item_url'  => 'http://www.example.com/?p=1',
		'expected'  => true,
		'is_mobile' => false,
	],
	[
		'item_url'  => 'http://www.example.com/?p=2',
		'expected'  => true,
		'is_mobile' => false,
	],
	[
		'item_url'  => 'http://www.example.com/?p=3',
		'expected'  => true,
		'is_mobile' => false,
	],
	[
		'item_url'  => 'http://www.example.com/?p=1',
		'expected'  => true,
		'is_mobile' => true,
	],
	[
		'item_url'  => 'http://www.example.com/?p=2',
		'expected'  => true,
		'is_mobile' => true,
	],
	[
		'item_url'  => 'http://www.example.com/?p=3',
		'expected'  => true,
		'is_mobile' => true,
	],


	// The transient does not exist before this test.
	[
		'item_url'  => 'http://www.example.com/lorem-ipsum',
		'expected'  => false,
		'is_mobile' => false,
	],
	[
		'item_url'  => 'http://www.example.com/minim-veniam',
		'expected'  => false,
		'is_mobile' => false,
	],
	[
		'item_url'  => 'http://www.example.com/?p=67',
		'expected'  => false,
		'is_mobile' => true,
	],
	[
		'item_url'  => 'http://www.example.com/lorem-ipsum',
		'expected'  => false,
		'is_mobile' => true,
	],
	[
		'item_url'  => 'http://www.example.com/minim-veniam',
		'expected'  => false,
		'is_mobile' => true,
	],
	[
		'item_url'  => 'http://www.example.com/?p=67',
		'expected'  => false,
		'is_mobile' => true,
	],
];
