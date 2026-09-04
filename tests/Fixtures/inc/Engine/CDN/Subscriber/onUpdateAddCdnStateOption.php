<?php

return [
	// < 3.22 upgrade: on_update_add_cdn_type_option (prio 10) writes cdn_type;
	// reconcile() bails because cdn_type was absent in old options (did_setting_change
	// requires both sides to be set); on_update_add_cdn_state_option (prio 11) does
	// the sole cdn_state write. Guard prevents a redundant 3rd write if reconcile
	// behaviour ever changes.
	'shouldMigrateCdnStateForPreThreeTwentyTwoUpgrade'               => [
		'config'   => [
			'old_version'     => '3.21.0',
			'initial_options' => [ 'cdn' => 1 ],    // cdn_type absent — pre-3.22 shape
			'cdn_enabled'     => true,
			'subscription'    => [ 'subscription_status' => 'none' ],
		],
		'expected' => [
			'cdn_state'   => 'rocketcdn_free',
			'write_count' => 2,   // cdn_type write + cdn_state write; reconcile bails on both
		],
	],

	// >= 3.22, < 3.23.4 upgrade: on_update_add_cdn_type_option bails; only
	// on_update_add_cdn_state_option runs. reconcile() fires but finds no cdn/cdn_type
	// change → bails. Single write.
	'shouldMigrateCdnStateForThreeTwentyToThreeTwentyThreeUpgrade'   => [
		'config'   => [
			'old_version'     => '3.22.0',
			'initial_options' => [ 'cdn' => 1, 'cdn_type' => 'rocketcdn' ],
			'cdn_enabled'     => true,
			'subscription'    => [ 'subscription_status' => 'none' ],
		],
		'expected' => [
			'cdn_state'   => 'rocketcdn_free',
			'write_count' => 1,   // cdn_state write only
		],
	],

	// >= 3.23.4: both hooks bail immediately — no DB activity.
	'shouldBailOutForAlreadyMigratedSites'                            => [
		'config'   => [
			'old_version'     => '3.23.4',
			'initial_options' => [ 'cdn' => 1, 'cdn_type' => 'rocketcdn', 'cdn_state' => 'rocketcdn_free' ],
			'cdn_enabled'     => true,
			'subscription'    => [ 'subscription_status' => 'none' ],
		],
		'expected' => [
			'cdn_state'   => 'rocketcdn_free',
			'write_count' => 0,
		],
	],

	// < 3.22 with byocdn: cdn=1, cnames present, cdn enabled → cdn_type='byocdn',
	// cdn_state='byocdn'.
	'shouldMigrateByocdnStateForPreThreeTwentyTwoUpgrade'            => [
		'config'   => [
			'old_version'     => '3.21.0',
			'initial_options' => [ 'cdn' => 1 ],
			'cdn_enabled'     => true,
			'cdn_cnames'      => [ 'https://cdn.example.org/' ],
			'subscription'    => [ 'subscription_status' => 'none' ],
		],
		'expected' => [
			'cdn_state'   => 'byocdn',
			'write_count' => 2,
		],
	],
];
