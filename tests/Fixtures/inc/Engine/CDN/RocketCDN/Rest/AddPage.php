<?php

return [
	'shouldAddValidPostPageAndReturnUpdatedList' => [
		'config'   => [
			'url'            => 'post_url',
			'prefill_count'  => 0,
			'add_first'      => false,
			'unauthenticated' => false,
		],
		'expected' => [
			'count' => 1,
			'limit' => 3,
			'url'   => true,
		],
	],
	'shouldAddHomepageUrlViaAddPage'             => [
		'config'   => [
			'url'            => 'homepage',
			'prefill_count'  => 0,
			'add_first'      => false,
			'unauthenticated' => false,
		],
		'expected' => [
			'count' => 1,
			'url'   => true,
		],
	],
	'shouldRejectExternalUrl'                    => [
		'config'   => [
			'url'            => 'https://external-site.com/page',
			'prefill_count'  => 0,
			'add_first'      => false,
			'unauthenticated' => false,
		],
		'expected' => [
			'code'   => 'rocketcdn_url_not_found',
			'status' => 400,
		],
	],
	'shouldRejectNonExistentPageOnSite'          => [
		'config'   => [
			'url'            => 'http://example.org/this-page-does-not-exist',
			'prefill_count'  => 0,
			'add_first'      => false,
			'unauthenticated' => false,
		],
		'expected' => [
			'code'   => 'rocketcdn_url_not_found',
			'status' => 400,
		],
	],
	'shouldRejectDuplicateUrl'                   => [
		'config'   => [
			'url'            => 'post_url',
			'prefill_count'  => 0,
			'add_first'      => true,
			'unauthenticated' => false,
		],
		'expected' => [
			'code'   => 'rocketcdn_page_already_exists',
			'status' => 409,
		],
	],
	'shouldRejectWhenFreeTierLimitReached'       => [
		'config'   => [
			'url'            => 'post_url',
			'prefill_count'  => 3,
			'add_first'      => false,
			'unauthenticated' => false,
		],
		'expected' => [
			'code'   => 'rocketcdn_page_limit_reached',
			'status' => 400,
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated'   => [
		'config'   => [
			'url'            => 'post_url',
			'prefill_count'  => 0,
			'add_first'      => false,
			'unauthenticated' => true,
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
