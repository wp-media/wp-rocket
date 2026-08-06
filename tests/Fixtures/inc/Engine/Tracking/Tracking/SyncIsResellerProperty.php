<?php

return [
	'shouldSyncResellerPropertyWhenTrackingEnabledAndReseller'    => [
		'config' => [
			'is_admin'         => true,
			'can_track'        => true,
			'transient_exists' => false,
			'is_reseller'      => true,
		],
	],
	'shouldSyncResellerPropertyWhenTrackingEnabledAndNotReseller' => [
		'config' => [
			'is_admin'         => true,
			'can_track'        => true,
			'transient_exists' => false,
			'is_reseller'      => false,
		],
	],
	'shouldNotSyncResellerPropertyWhenTrackingDisabled'           => [
		'config' => [
			'is_admin'         => true,
			'can_track'        => false,
			'transient_exists' => false,
			'is_reseller'      => false,
		],
	],
	'shouldNotSyncWhenAlreadySyncedToday'                         => [
		'config' => [
			'is_admin'         => true,
			'can_track'        => true,
			'transient_exists' => true,
			'is_reseller'      => false,
		],
	],
	'shouldNotSyncWhenNotInAdmin'                                 => [
		'config' => [
			'is_admin'         => false,
			'can_track'        => true,
			'transient_exists' => false,
			'is_reseller'      => false,
		],
	],
];
