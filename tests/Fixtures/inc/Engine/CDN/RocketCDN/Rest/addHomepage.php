<?php
return [
	'shouldAddHomepageAndReturnUpdatedList' => [
		'config'   => [
			'prefill_count'     => 0,
			'add_homepage_first' => false,
			'unauthenticated'   => false,
		],
		'expected' => [
			'count' => 1,
			'url'   => true,
		],
	],
	'shouldUseTheSiteTitleAsPageTitle' => [
		'config'   => [
			'prefill_count'     => 0,
			'add_homepage_first' => false,
			'unauthenticated'   => false,
		],
		'expected' => [
			'title' => true,
		],
	],
	'shouldRejectDuplicateHomepage' => [
		'config'   => [
			'prefill_count'     => 0,
			'add_homepage_first' => true,
			'unauthenticated'   => false,
		],
		'expected' => [
			'code'   => 'rocketcdn_page_already_exists',
			'status' => 409,
		],
	],
	'shouldCountHomepageTowardsLimit' => [
		'config'   => [
			'prefill_count'     => 3,
			'add_homepage_first' => false,
			'unauthenticated'   => false,
		],
		'expected' => [
			'code' => 'rocketcdn_page_limit_reached',
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated' => [
		'config'   => [
			'prefill_count'     => 0,
			'add_homepage_first' => false,
			'unauthenticated'   => true,
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
