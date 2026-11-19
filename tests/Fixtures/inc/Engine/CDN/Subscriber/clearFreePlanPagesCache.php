<?php

return [
	// TC-3.3: No pages in free list → no cache clearing actions fire.
	'testNoActionsWhenFreePlanListIsEmpty'          => [
		'config'   => [
			'pages' => [],
		],
		'expected' => [
			'clean_home_called'  => false,
			'clean_files_called' => false,
		],
	],

	// TC-3.3: Homepage in free list → rocket_clean_home() fires.
	'testClearsHomepageCacheWhenHomepageInFreeList' => [
		'config'   => [
			'pages' => [
				[ 'url' => 'http://example.org', 'title' => 'Home' ],
			],
		],
		'expected' => [
			'clean_home_called'  => true,
			'clean_files_called' => false,
		],
	],

	// TC-3.3: Non-homepage in free list → rocket_clean_files() fires.
	'testClearsOtherPageCacheWhenPageInFreeList'    => [
		'config'   => [
			'pages' => [
				[ 'url' => 'http://example.org/about/', 'title' => 'About' ],
			],
		],
		'expected' => [
			'clean_home_called'  => false,
			'clean_files_called' => true,
		],
	],

	// TC-3.3: Mixed pages → both cache clearing functions fire.
	'testClearsBothCacheTypesForMixedFreeList'      => [
		'config'   => [
			'pages' => [
				[ 'url' => 'http://example.org', 'title' => 'Home' ],
				[ 'url' => 'http://example.org/blog/', 'title' => 'Blog' ],
			],
		],
		'expected' => [
			'clean_home_called'  => true,
			'clean_files_called' => true,
		],
	],
];
