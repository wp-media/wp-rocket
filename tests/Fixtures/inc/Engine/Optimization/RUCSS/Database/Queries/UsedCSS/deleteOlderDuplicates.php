<?php

/*
 * Rows are listed by insertion order and referred to by their 1-based position. A call goes from the
 * row at position `after` (0 for the start) up to the row at position `up_to` ('max' for the last one).
 * `broken_delete` is null, 'all', or the position of the only row whose DELETE fails.
 */
$a       = 'http://example.org/a';
$prefix  = 'http://example.org/' . str_repeat( 'a', 160 );
$call    = function ( $limit, $after = 0, $up_to = 'max', $broken_delete = null ) {
	return [
		'after'         => $after,
		'up_to'         => $up_to,
		'limit'         => $limit,
		'broken_delete' => $broken_delete,
	];
};
$numbers = [];
foreach ( range( 1, 5 ) as $number ) {
	$numbers[] = [ "http://example.org/{$number}", 0 ];
	$numbers[] = [ "http://example.org/{$number}", 0 ];
}

return [
	'test_data' => [
		'shouldKeepOnlyTheNewestRowOfAnExactTriplicate' => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ $a, 0 ], [ $a, 0 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'max_id'    => 3,
				'results'   => [ 2 ],
				'remaining' => [ 3 ],
			],
		],
		'shouldTreatCaseVariantsAsDuplicates'           => [
			'config'   => [
				'rows'  => [ [ 'http://example.org/Page', 0 ], [ 'http://example.org/page', 0 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'max_id'    => 2,
				'results'   => [ 1 ],
				'remaining' => [ 2 ],
			],
		],
		'shouldKeepDesktopAndMobileRows'                => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ $a, 1 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'max_id'    => 2,
				'results'   => [ 0 ],
				'remaining' => [ 1, 2 ],
			],
		],
		'shouldKeepUrlsSharingALongPrefix'              => [
			'config'   => [
				'rows'  => [ [ $prefix . '-one', 0 ], [ $prefix . '-two', 0 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'max_id'    => 2,
				'results'   => [ 0 ],
				'remaining' => [ 1, 2 ],
			],
		],
		'shouldDeleteNoMoreRowsThanTheLimit'            => [
			'config'   => [
				'rows'  => $numbers,
				'calls' => [ $call( 3 ), $call( 3 ) ],
			],
			'expected' => [
				'max_id'    => 10,
				'results'   => [ 3, 2 ],
				'remaining' => [ 2, 4, 6, 8, 10 ],
			],
		],
		'shouldOnlyScanTheGivenIdRange'                 => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ 'http://example.org/b', 0 ], [ $a, 0 ], [ 'http://example.org/b', 0 ] ],
				'calls' => [ $call( 100, 1, 3 ) ],
			],
			'expected' => [
				'max_id'    => 4,
				'results'   => [ 1 ],
				'remaining' => [ 1, 3, 4 ],
			],
		],
		'shouldReturnFalseWhenNoRowOfTheBatchCanBeDeleted' => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ $a, 0 ] ],
				'calls' => [ $call( 100, 0, 'max', 'all' ) ],
			],
			'expected' => [
				'max_id'    => 2,
				'results'   => [ false ],
				'remaining' => [ 1, 2 ],
			],
		],
		'shouldReturnTheBatchSizeWhenOnlyOneDeletionFails' => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ $a, 0 ], [ $a, 0 ], [ $a, 0 ] ],
				'calls' => [ $call( 3, 0, 'max', 1 ) ],
			],
			'expected' => [
				'max_id'    => 4,
				'results'   => [ 3 ],
				'remaining' => [ 1, 4 ],
			],
		],
		'shouldReturnZeroOnACleanTable'                 => [
			'config'   => [
				'rows'  => [ [ $a, 0 ], [ 'http://example.org/b', 0 ] ],
				'calls' => [ $call( 100 ) ],
			],
			'expected' => [
				'max_id'    => 2,
				'results'   => [ 0 ],
				'remaining' => [ 1, 2 ],
			],
		],
		'shouldReturnZeroAsHighestIdOfAnEmptyTable'     => [
			'config'   => [
				'rows'  => [],
				'calls' => [],
			],
			'expected' => [
				'max_id'    => 0,
				'results'   => [],
				'remaining' => [],
			],
		],
	],
];
