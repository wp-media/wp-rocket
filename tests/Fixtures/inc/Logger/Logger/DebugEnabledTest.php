<?php

return [
	'test_data' => [
		'shouldReturnTrueWhenDebugIsTrue' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => true,
				'REQUEST_URI'     => '/page/',
			],
			'expected' => true,
		],
		'shouldReturnFalseWhenDebugIsFalse' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => false,
				'REQUEST_URI'     => '/page/',
			],
			'expected' => false,
		],
		'shouldReturnFalseWhenDebugNotDefined' => [
			'config'   => [
				'REQUEST_URI' => '/page/',
			],
			'expected' => false,
		],
		'shouldReturnTrueWhenCurrentUrlMatchesDebugString' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/page',
				'REQUEST_URI'     => '/page',
			],
			'expected' => true,
		],
		'shouldReturnFalseWhenCurrentUrlDoesNotMatchDebugString' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/other-page',
				'REQUEST_URI'     => '/page',
			],
			'expected' => false,
		],
		'shouldMatchRelativePathInDebugString' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => '/page',
				'REQUEST_URI'     => '/page',
			],
			'expected' => true,
		],
		'shouldReturnTrueWhenCurrentUrlMatchesOneInArray' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => [ '/page-1/', '/page-2/', '/page-3/' ],
				'REQUEST_URI'     => '/page-2',
			],
			'expected' => true,
		],
		'shouldReturnFalseWhenCurrentUrlMatchesNoneInArray' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => [ '/page-1/', '/page-2/', '/page-3/' ],
				'REQUEST_URI'     => '/page-4',
			],
			'expected' => false,
		],
		'shouldIgnoreTrailingSlashInDebugUrl' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/page/',
				'REQUEST_URI'     => '/page',
			],
			'expected' => true,
		],
		'shouldIgnoreTrailingSlashInCurrentUrl' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/page',
				'REQUEST_URI'     => '/page/',
			],
			'expected' => true,
		],
		'shouldIgnoreQueryStringInCurrentUrl' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/page',
				'REQUEST_URI'     => '/page?param=value',
			],
			'expected' => true,
		],
		'shouldIgnoreQueryStringInDebugUrl' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/page?param=value',
				'REQUEST_URI'     => '/page',
			],
			'expected' => true,
		],
		'shouldMatchCaseInsensitively' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/PAGE',
				'REQUEST_URI'     => '/page',
			],
			'expected' => true,
		],
		'shouldMatchCaseInsensitivelyInPath' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => '/Page-Name',
				'REQUEST_URI'     => '/page-name',
			],
			'expected' => true,
		],
		'shouldMatchHomepageWithSlash' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => '/',
				'REQUEST_URI'     => '/',
			],
			'expected' => true,
		],
		'shouldMatchHomepageWithFullUrl' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/',
				'REQUEST_URI'     => '/',
			],
			'expected' => true,
		],
		'shouldMatchHomepageWithoutTrailingSlash' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org',
				'REQUEST_URI'     => '/',
			],
			'expected' => true,
		],

		// Absolute vs relative URL tests
		'shouldMatchAbsoluteUrlWithRelativePath' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://example.org/my-page',
				'REQUEST_URI'     => '/my-page',
			],
			'expected' => true,
		],
		'shouldNotMatchDifferentDomain' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => 'http://other-domain.com/page',
				'REQUEST_URI'     => '/page',
			],
			'expected' => false,
		],
		'shouldCacheResultAcrossMultipleCalls' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => '/page',
				'REQUEST_URI'     => '/page',
				'test_cache'      => true,
			],
			'expected' => true,
		],
		'shouldMatchMixedArrayContent' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => [ 'http://example.org/page-1', '/page-2/', 'http://example.org/page-3' ],
				'REQUEST_URI'     => '/page-2',
			],
			'expected' => true,
		],
		'shouldHandleEmptyStringDebugValue' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => '',
				'REQUEST_URI'     => '/page',
			],
			'expected' => false,
		],
		'shouldHandleEmptyArrayDebugValue' => [
			'config'   => [
				'WP_ROCKET_DEBUG' => [],
				'REQUEST_URI'     => '/page',
			],
			'expected' => false,
		],
	],
];
