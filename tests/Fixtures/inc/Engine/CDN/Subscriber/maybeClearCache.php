<?php

return [
	'testShouldClearFreePagesWhenNothingToFree'    => [
		[ [ 'nothing' ], [ 'rocketcdn_free' ] ],
		[ 'free_pages' ],
	],
	'testShouldClearFreePagesWhenFreeToNothing'    => [
		[ [ 'rocketcdn_free' ], [ 'nothing' ] ],
		[ 'free_pages' ],
	],
	'testShouldClearAllWhenNothingToPaid'          => [
		[ [ 'nothing' ], [ 'rocketcdn_paid' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenPaidToNothing'          => [
		[ [ 'rocketcdn_paid' ], [ 'nothing' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenNothingToByocdn'        => [
		[ [ 'nothing' ], [ 'byocdn' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenByocdnToNothing'        => [
		[ [ 'byocdn' ], [ 'nothing' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenPaidToByocdn'           => [
		[ [ 'rocketcdn_paid' ], [ 'byocdn' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenByocdnToPaid'           => [
		[ [ 'byocdn' ], [ 'rocketcdn_paid' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenPaidToFree'             => [
		[ [ 'rocketcdn_paid' ], [ 'rocketcdn_free' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenFreeToPaid'             => [
		[ [ 'rocketcdn_free' ], [ 'rocketcdn_paid' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenFreeToByocdn'           => [
		[ [ 'rocketcdn_free' ], [ 'byocdn' ] ],
		[ 'all' ],
	],
	'testShouldClearAllWhenByocdnToFree'           => [
		[ [ 'byocdn' ], [ 'rocketcdn_free' ] ],
		[ 'all' ],
	],
	'testShouldNotClearWhenCdnStateUnchanged'      => [
		[ [ 'rocketcdn_paid' ], [ 'rocketcdn_paid' ] ],
		[ 'none' ],
	],
	'testShouldNotClearWhenCdnStateKeyAbsent'      => [
		[ [], [] ],
		[ 'none' ],
	],
];
