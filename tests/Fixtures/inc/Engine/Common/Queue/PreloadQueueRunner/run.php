<?php
return [
	'whenHasMaximumConcurrentBatchesShouldReturnZero' => [
		'config' => [
			'time_limit' => 10000,
			'batch_size' => 100,
			'has_max' => [
				true
			],
			'do_batch' => false,
			'processed' => 10,
			'context' => 'context'
		],
		'expected' => 0
	],
	'whenHasNotMaximumConcurrentBatchesShouldReturnNumber' => [
		'config' => [
			'time_limit' => 10000,
			'batch_size' => 100,
			'has_max' => [
				false,
				true
			],
			'do_batch' => true,
			'processed' => 10,
			'context' => 'context'
		],
		'expected' => 10
	],
];
