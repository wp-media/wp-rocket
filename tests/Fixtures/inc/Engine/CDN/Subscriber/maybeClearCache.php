<?php

return [
	'testShouldClearFreePagesWhenNothingToFree' => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'nothing' ],
			'new_value' => [ 'cdn_state' => 'rocketcdn_free' ],
		],
		'expected' => [ 'method' => 'free_pages' ],
	],
	'testShouldClearFreePagesWhenFreeToNothing' => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'rocketcdn_free' ],
			'new_value' => [ 'cdn_state' => 'nothing' ],
		],
		'expected' => [ 'method' => 'free_pages' ],
	],
	'testShouldClearAllWhenNothingToPaid'       => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'nothing' ],
			'new_value' => [ 'cdn_state' => 'rocketcdn_paid' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenPaidToNothing'       => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'rocketcdn_paid' ],
			'new_value' => [ 'cdn_state' => 'nothing' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenNothingToByocdn'     => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'nothing' ],
			'new_value' => [ 'cdn_state' => 'byocdn' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenByocdnToNothing'     => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'byocdn' ],
			'new_value' => [ 'cdn_state' => 'nothing' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenPaidToByocdn'        => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'rocketcdn_paid' ],
			'new_value' => [ 'cdn_state' => 'byocdn' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenByocdnToPaid'        => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'byocdn' ],
			'new_value' => [ 'cdn_state' => 'rocketcdn_paid' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenPaidToFree'          => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'rocketcdn_paid' ],
			'new_value' => [ 'cdn_state' => 'rocketcdn_free' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenFreeToPaid'          => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'rocketcdn_free' ],
			'new_value' => [ 'cdn_state' => 'rocketcdn_paid' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenFreeToByocdn'        => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'rocketcdn_free' ],
			'new_value' => [ 'cdn_state' => 'byocdn' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldClearAllWhenByocdnToFree'        => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'byocdn' ],
			'new_value' => [ 'cdn_state' => 'rocketcdn_free' ],
		],
		'expected' => [ 'method' => 'all' ],
	],
	'testShouldNotClearWhenCdnStateUnchanged'   => [
		'config'   => [
			'old_value' => [ 'cdn_state' => 'rocketcdn_paid' ],
			'new_value' => [ 'cdn_state' => 'rocketcdn_paid' ],
		],
		'expected' => [ 'method' => 'none' ],
	],
	'testShouldNotClearWhenCdnStateKeyAbsent'   => [
		'config'   => [
			'old_value' => [],
			'new_value' => [],
		],
		'expected' => [ 'method' => 'none' ],
	],
];
