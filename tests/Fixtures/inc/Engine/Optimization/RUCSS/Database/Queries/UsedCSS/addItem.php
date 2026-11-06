<?php

$url = 'http://example.org/sample-page';

return [
	'test_data' => [
		'shouldStoreTheExactMd5OfTheUrl'      => [
			'config'   => [
				'items'  => [ [ $url, false ] ],
				'job_id' => null,
			],
			'expected' => [
				'results' => [ true ],
				'rows'    => 1,
			],
		],
		'shouldRejectTheSameUrlAndDevice'     => [
			'config'   => [
				'items'  => [ [ $url, false ], [ $url, false ] ],
				'job_id' => null,
			],
			'expected' => [
				'results' => [ true, false ],
				'rows'    => 1,
			],
		],
		'shouldAcceptTheOtherDevice'          => [
			'config'   => [
				'items'  => [ [ $url, false ], [ $url, true ] ],
				'job_id' => null,
			],
			'expected' => [
				'results' => [ true, true ],
				'rows'    => 2,
			],
		],
		'shouldAcceptACaseVariantOfTheUrl'    => [
			'config'   => [
				'items'  => [ [ $url, false ], [ 'http://example.org/Sample-Page', false ] ],
				'job_id' => null,
			],
			'expected' => [
				'results' => [ true, true ],
				'rows'    => 2,
			],
		],
		'shouldNotCacheTheRowByJobId'         => [
			'config'   => [
				'items'  => [ [ $url, false ] ],
				'job_id' => 'job-42',
			],
			'expected' => [
				'results' => [ true ],
				'rows'    => 1,
			],
		],
	],
];
