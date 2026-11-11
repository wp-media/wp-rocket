<?php

return [
	'testShouldReturnCountsMatchingSeededRows'         => [
		[
			'url'    => '/pending-1',
			'status' => 'pending',
		],
		[
			'url'    => '/pending-2',
			'status' => 'pending',
		],
		[
			'url'    => '/completed-1',
			'status' => 'completed',
		],
		[
			'url'    => '/failed-1',
			'status' => 'failed',
		],
	],
	'testShouldReturnNullEstimateWhenTrackingDisabled' => [
		[
			'url'    => '/pending-only',
			'status' => 'pending',
		],
	],
];
