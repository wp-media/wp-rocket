<?php

return [
	'wrong screen id, does nothing'                        => [
		'config'   => [
			'screen_id' => 'options-general',
		],
		'expected' => [
			'update_option_called' => false,
			'cache_cleared'        => false,
			'event'                => null,
		],
	],
	'right screen but lacks capability, does nothing'      => [
		'config'   => [
			'screen_id'        => 'settings_page_wprocket',
			'user_can_manage'  => false,
		],
		'expected' => [
			'update_option_called' => false,
			'cache_cleared'        => false,
			'event'                => null,
		],
	],
	'state unchanged (both not forced), does nothing'      => [
		'config'   => [
			'was_forced' => false,
			'is_forced'  => false,
		],
		'expected' => [
			'update_option_called' => false,
			'cache_cleared'        => false,
			'event'                => null,
		],
	],
	'state unchanged (both forced), does nothing'          => [
		'config'   => [
			'was_forced' => true,
			'is_forced'  => true,
		],
		'expected' => [
			'update_option_called' => false,
			'cache_cleared'        => false,
			'event'                => null,
		],
	],
	'pause leg fires RocketCDN Forced Off'                 => [
		'config'   => [
			'was_forced'          => false,
			'is_forced'           => true,
			'reason'              => 'license_expired',
			'settings_cdn_state'  => 'rocketcdn_paid',
			'cdn_state'           => 'rocketcdn_paid',
		],
		'expected' => [
			'update_option_called' => true,
			'cache_cleared'        => true,
			'event'                => 'RocketCDN Forced Off',
			'event_data'           => [
				'reason'          => 'license_expired',
				'pre_expiry_mode' => 'rocketcdn_paid',
			],
		],
	],
	'resume leg switching to nothing fires no event'       => [
		'config'   => [
			'was_forced' => true,
			'is_forced'  => false,
			'cdn_state'  => 'nothing',
		],
		'expected' => [
			'update_option_called' => true,
			'cache_cleared'        => true,
			'event'                => null,
		],
	],
	'resume leg switching to byocdn fires no event'        => [
		'config'   => [
			'was_forced' => true,
			'is_forced'  => false,
			'cdn_state'  => 'byocdn',
		],
		'expected' => [
			'update_option_called' => true,
			'cache_cleared'        => true,
			'event'                => null,
		],
	],
	'resume leg with active paid subscription: pro_purchase' => [
		'config'   => [
			'was_forced' => true,
			'is_forced'  => false,
			'cdn_state'  => 'rocketcdn_paid',
			'cdn_status' => 'active',
			'is_paid'    => true,
		],
		'expected' => [
			'update_option_called' => true,
			'cache_cleared'        => true,
			'event'                => 'RocketCDN Mode Changed',
			'event_data'           => [
				'cdn_mode'   => 'rocketcdn_paid',
				'cdn_status' => 'active',
				'trigger'    => 'pro_purchase',
			],
		],
	],
	'resume leg without paid subscription: license_renewal' => [
		'config'   => [
			'was_forced' => true,
			'is_forced'  => false,
			'cdn_state'  => 'rocketcdn_free',
			'cdn_status' => 'active',
			'is_paid'    => false,
		],
		'expected' => [
			'update_option_called' => true,
			'cache_cleared'        => true,
			'event'                => 'RocketCDN Mode Changed',
			'event_data'           => [
				'cdn_mode'   => 'rocketcdn_free',
				'cdn_status' => 'active',
				'trigger'    => 'license_renewal',
			],
		],
	],
];
