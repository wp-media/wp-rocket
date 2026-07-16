<?php

return [
	'shouldSyncResellerPropertyWhenTrackingEnabledAndReseller'    => [
		'config' => [
			'can_track'   => true,
			'is_reseller' => true,
		],
	],
	'shouldSyncResellerPropertyWhenTrackingEnabledAndNotReseller' => [
		'config' => [
			'can_track'   => true,
			'is_reseller' => false,
		],
	],
	'shouldNotSyncResellerPropertyWhenTrackingDisabled'           => [
		'config' => [
			'can_track'   => false,
			'is_reseller' => true,
		],
	],
];
