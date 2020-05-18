<?php

return [
	// Should return the job ID when the transient exists.
	[
		'item_url' => 'http://www.example.com/?p=1',
		'expected' => 1,
	],
	[
		'item_url' => 'http://www.example.com/?p=2',
		'expected' => 5,

	],
	[
		'item_url' => 'http://www.example.com/?p=3',
		'expected' => 5,
	],

	// Should return false when the job ID does not exist.
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
