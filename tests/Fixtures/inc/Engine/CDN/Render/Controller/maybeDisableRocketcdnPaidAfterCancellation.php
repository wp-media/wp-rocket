<?php

use WP_Rocket\Engine\CDN\Context;

return [
	'wrong screen, does nothing'                     => [
		'config'   => [
			'screen_id' => 'options-general',
		],
		'expected' => [
			'update_option_called' => false,
			'settings_saved'       => false,
			'event_fired'          => false,
		],
	],
	'not on rocketcdn driver, does nothing'          => [
		'config'   => [
			'applied_cdn_state' => Context::CDN_STATE_NOTHING,
		],
		'expected' => [
			'update_option_called' => false,
			'settings_saved'       => false,
			'event_fired'          => false,
		],
	],
	'active subscription, does nothing'              => [
		'config'   => [
			'has_active_subscription' => true,
		],
		'expected' => [
			'update_option_called' => false,
			'settings_saved'       => false,
			'event_fired'          => false,
		],
	],
	'paid still in grace period, does nothing'       => [
		'config'   => [
			'is_paid'            => true,
			'is_in_grace_period' => true,
		],
		'expected' => [
			'update_option_called' => false,
			'settings_saved'       => false,
			'event_fired'          => false,
		],
	],
	'license invalid, does nothing'                  => [
		'config'   => [
			'is_license_invalid' => true,
		],
		'expected' => [
			'update_option_called' => false,
			'settings_saved'       => false,
			'event_fired'          => false,
		],
	],
	'not persistently forced off, does nothing'      => [
		'config'   => [
			'forced_off_persistent' => false,
		],
		'expected' => [
			'update_option_called' => false,
			'settings_saved'       => false,
			'event_fired'          => false,
		],
	],
	'cancelled outside grace period, disables paid CDN and tracks with fresh state' => [
		'config'   => [
			'forced_off_persistent' => true,
		],
		'expected' => [
			'update_option_called' => true,
			'settings_saved'       => true,
			'event_fired'          => true,
		],
	],
];
