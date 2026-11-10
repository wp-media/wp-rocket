<?php

return [
	'test_data' => [
		'shouldShowLoadMoreWhenMoreThanThreeRemain'   => [
			'config'   => [
				'raw_count' => 5,
				'removed'   => 1,
			],
			'expected' => [ 'load_more' => true ],
		],
		'shouldHideLoadMoreWhenRemovalLeavesThree'    => [
			'config'   => [
				'raw_count' => 4,
				'removed'   => 1,
			],
			'expected' => [ 'load_more' => false ],
		],
		'shouldHideLoadMoreWhenThreeWithoutRemoval'   => [
			'config'   => [
				'raw_count' => 3,
				'removed'   => 0,
			],
			'expected' => [ 'load_more' => false ],
		],
	],
];
