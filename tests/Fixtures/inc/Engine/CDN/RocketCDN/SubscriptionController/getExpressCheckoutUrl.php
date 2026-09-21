<?php

return [
	'returnEmptyWhenUserDataIsFalse'              => [
		'config'   => [
			'button_url' => false, // false signals get_user_data() itself returns false
		],
		'expected' => '',
	],
	'returnEmptyWhenButtonUrlPropertyNotSet'      => [
		'config'   => [
			'button_url' => null, // null signals button object has no 'url' property
		],
		'expected' => '',
	],
	'returnEmptyWhenButtonUrlIsEmpty'             => [
		'config'   => [
			'button_url' => '',
		],
		'expected' => '',
	],
	'returnUrlWithDashboardQueryArgWhenValidUrl'  => [
		'config'   => [
			'button_url' => 'https://rocketcdn.me/checkout/',
		],
		// admin_url() is stubbed to return 'https://example.com/wp-admin/options-general.php'.
		// The inner add_query_arg appends page and rocketcdn_checkout; rawurlencode encodes
		// the result; the outer add_query_arg appends that as dashboard_url.
		'expected' => 'https://rocketcdn.me/checkout/?dashboard_url=https%3A%2F%2Fexample.com%2Fwp-admin%2Foptions-general.php%3Fpage%3Dwp-rocket%26rocketcdn_checkout%3Dtrue',
	],
];
