<?php

/*
 * Rows are listed by insertion order and referred to by their 1-based position. Each call
 * passes `limit`; `broken_delete` is null, 'all', or the position of the only row whose DELETE fails.
 */
$a      = 'http://example.org/a';
$prefix = 'http://example.org/' . str_repeat( 'a', 180 );
$call   = function ( $limit, $broken_delete = null ) {
	return [
		'limit'         => $limit,
		'broken_delete' => $broken_delete,
	];
};
$groups = [];
foreach ( range( 1, 5 ) as $number ) {
	$groups[] = [ "http://example.org/{$number}", 0 ];
	$groups[] = [ "http://example.org/{$number}", 0 ];
}

return [
	'test_data' => [
		'shouldDeleteTheOlderRowOfAPair'                  => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ $a, 0 ] ],
				'calls' => [ $call( 100 ), $call( 100 ) ],
			],
			'expected' => [
				'results'   => [ 1, 0 ],
				'remaining' => [ 2 ],
			],
		],
		'shouldTakeTwoPassesToReduceATripletToItsNewestRow' => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ $a, 0 ], [ $a, 0 ] ],
				'calls' => [ $call( 100 ), $call( 100 ), $call( 100 ) ],
			],
			'expected' => [
				'results'   => [ 1, 1, 0 ],
				'remaining' => [ 3 ],
			],
		],
		'shouldTreatCaseVariantsAsDuplicates'             => [
			'config'   => [
				'rows'  => [ [ 'http://example.org/Page', 0 ], [ 'http://example.org/page', 0 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'results'   => [ 1 ],
				'remaining' => [ 2 ],
			],
		],
		'shouldKeepDesktopAndMobileRows'                  => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ $a, 1 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'results'   => [ 0 ],
				'remaining' => [ 1, 2 ],
			],
		],
		'shouldKeepUrlsSharingALongPrefix'                => [
			'config'   => [
				'rows'  => [ [ $prefix . '-one', 0 ], [ $prefix . '-two', 0 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'results'   => [ 0 ],
				'remaining' => [ 1, 2 ],
			],
		],
		'shouldDeleteNoMoreRowsThanTheLimit'              => [
			'config'   => [
				'rows'  => $groups,
				'calls' => [ $call( 3 ), $call( 3 ) ],
			],
			'expected' => [
				'results'   => [ 3, 2 ],
				'remaining' => [ 2, 4, 6, 8, 10 ],
			],
		],
		'shouldReturnFalseWhenNoRowOfTheBatchCanBeDeleted' => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ $a, 0 ] ],
				'calls' => [ $call( 100, 'all' ) ],
			],
			'expected' => [
				'results'   => [ false ],
				'remaining' => [ 1, 2 ],
			],
		],
		'shouldReturnTheFoundCountWhenOnlyOneDeletionFails' => [
			'config'   => [
				'rows'  => [ [ 'http://example.org/1', 0 ], [ 'http://example.org/1', 0 ], [ 'http://example.org/2', 0 ], [ 'http://example.org/2', 0 ], [ 'http://example.org/3', 0 ], [ 'http://example.org/3', 0 ] ],
				'calls' => [ $call( 3, 1 ) ],
			],
			'expected' => [
				'results'   => [ 3 ],
				'remaining' => [ 1, 2, 4, 6 ],
			],
		],
		'shouldReturnZeroOnACleanTable'                   => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ 'http://example.org/b', 0 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'results'   => [ 0 ],
				'remaining' => [ 1, 2 ],
			],
		],
	],
];
