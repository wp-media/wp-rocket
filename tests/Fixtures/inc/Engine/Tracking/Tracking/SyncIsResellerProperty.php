<?php

return [
	'shouldSyncResellerPropertyWhenTrackingEnabledAndReseller'    => [
		'config' => [
			'can_track'       => true,
			'transient_exists' => false,
			'is_reseller'     => true,
		],
	],
	'shouldSyncResellerPropertyWhenTrackingEnabledAndNotReseller' => [
		'config' => [
			'can_track'       => true,
			'transient_exists' => false,
			'is_reseller'     => false,
		],
	],
	'shouldNotSyncResellerPropertyWhenTrackingDisabled'           => [
		'config' => [
			'can_track'       => false,
			'transient_exists' => false,
			'is_reseller'     => false,
		],
	],
	'shouldNotSyncWhenAlreadySyncedToday'                         => [
		'config' => [
			'can_track'       => true,
			'transient_exists' => true,
			'is_reseller'     => false,
		],
	],
];
