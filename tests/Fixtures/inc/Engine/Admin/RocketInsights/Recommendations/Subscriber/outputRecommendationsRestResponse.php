<?php

return [
	'test_data' => [
		'shouldReturnCachedRecommendationsWithoutFetching' => [
			'config'   => [ 'cached' => true ],
			'expected' => [ 'fetches' => false ],
		],

		'shouldFetchOnCacheMiss'                           => [
			'config'   => [ 'cached' => false ],
			'expected' => [ 'fetches' => true ],
		],
	],
];
