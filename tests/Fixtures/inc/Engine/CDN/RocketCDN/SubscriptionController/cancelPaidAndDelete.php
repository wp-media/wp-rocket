<?php
$html_page = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';

return [
	'withOnePrePaidFreePage'       => [
		'config'   => [
			'free_pages' => [
				[
					'url'   => 'http://example.org/',
					'title' => 'Home',
				],
			],
			'html'       => $html_page,
		],
		'expected' => [
			'free_pages_count_in_db' => 1,
		],
	],

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
			'html'       => $html_page,
		],
		'expected' => [
			'free_pages_count_in_db' => 2,
		],
	],

	'withNoPrePaidFreePages'       => [
		'config'   => [
			'free_pages' => [],
			'html'       => $html_page,
		],
		'expected' => [
			'free_pages_count_in_db' => 0,
		],
	],

];
