<?php

return [
	'testShouldDoNothingWhenOptinCannotTrack'              => [
		'config'   => [
			'can_track'   => false,
			'row_details' => [
				'url'    => 'http://example.org/',
				'status' => 'completed',
				'score'  => 85,
				'data'   => [
					'is_retest'  => false,
					'start_time' => 0,
					'source'     => 'dashboard',
				],
			],
			'job_details' => [],
			'plan'        => 'free',
		],
		'expected' => [
			'track_called' => false,
		],
	],
	'testShouldDoNothingWhenRowDataIsEmpty'                => [
		'config'   => [
			'can_track'   => true,
			'row_details' => [
				'url'    => 'http://example.org/',
				'status' => 'completed',
				'score'  => 85,
				'data'   => [],
			],
			'job_details' => [],
			'plan'        => 'free',
		],
		'expected' => [
			'track_called' => false,
		],
	],
	'testShouldTrackWithRiPageIdentifierForHomepage'       => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'row_details' => [
				'url'    => 'http://example.org/',
				'status' => 'completed',
				'score'  => 95,
				'data'   => [
					'is_retest'  => false,
					'start_time' => 1700000000,
					'source'     => 'dashboard',
				],
			],
			'job_details' => [],
			'plan'        => 'pro',
		],
		'expected' => [
			'track_called'       => true,
			'ri_page_identifier' => 'is_home_page',
		],
	],
	'testShouldTrackWithRiPageIdentifierForHomepageWithoutTrailingSlash' => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'row_details' => [
				'url'    => 'http://example.org',
				'status' => 'completed',
				'score'  => 80,
				'data'   => [
					'is_retest'  => true,
					'start_time' => 1700000000,
					'source'     => 'auto',
				],
			],
			'job_details' => [],
			'plan'        => 'free',
		],
		'expected' => [
			'track_called'       => true,
			'ri_page_identifier' => 'is_home_page',
		],
	],
	'testShouldTrackWithoutRiPageIdentifierForNonHomepage' => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'row_details' => [
				'url'    => 'http://example.org/about/',
				'status' => 'completed',
				'score'  => 70,
				'data'   => [
					'is_retest'  => false,
					'start_time' => 1700000000,
					'source'     => 'manual',
				],
			],
			'job_details' => [],
			'plan'        => 'plus',
		],
		'expected' => [
			'track_called'       => true,
			'ri_page_identifier' => null,
		],
	],
	'testShouldTrackWithoutRiPageIdentifierForBlogPage'    => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'row_details' => [
				'url'    => 'http://example.org/blog/',
				'status' => 'failed',
				'score'  => 0,
				'data'   => [
					'is_retest'  => false,
					'start_time' => 1700000000,
					'source'     => 'dashboard',
				],
			],
			'job_details' => [],
			'plan'        => 'pro',
		],
		'expected' => [
			'track_called'       => true,
			'ri_page_identifier' => null,
		],
	],
];
