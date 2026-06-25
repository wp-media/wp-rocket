<?php
return [
	'withMultiplePrePaidFreePages' => [
		'config'   => [
			'free_pages' => [
				[
					'url'   => 'http://example.org/',
					'title' => 'Home',
				],
				[
					'url'   => 'http://example.org/about/',
					'title' => 'About',
				],
			],
		],
		'expected' => [
			'free_pages_count_in_db' => 2,
		],
	],

	'withOnePrePaidFreePage'       => [
		'config'   => [
			'free_pages' => [
				[
					'url'   => 'http://example.org/',
					'title' => 'Home',
				],
			],
		],
		'expected' => [
			'free_pages_count_in_db' => 1,
		],
	],

	'withNoPrePaidFreePages'       => [
		'config'   => [
			'free_pages' => [],
		],
		'expected' => [
			'free_pages_count_in_db' => 0,
		],
	],

];
