<?php
return [
	'vfs_dir' => 'public/',

	'test_data' => [
		'shouldReturnEmptyValues' => [
			'config' => [],
			'expected' => []
		],
		'shouldReturnValidValues' => [
			'config' => [
				0,
				'test_data',
				null,
				false
			],
			'expected' => [
				'test_data'
			]
		],
		'shouldReturnUniqueValues' => [
			'config' => [
				'test1',
				'test2',
				'test1',
				'test2'
			],
			'expected' => [
				'test1',
				'test2'
			]
		],
	]
];
