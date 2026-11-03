<?php

return [
	'test_data' => [
		'registered id returns true'                => [
			[
				'add'      => 'foo',
				'check'    => 'foo',
				'expected' => true,
			],
		],
		'unknown id returns false'                  => [
			[
				'add'      => null,
				'check'    => 'unknown',
				'expected' => false,
			],
		],
		'leading backslash on add, lookup without'  => [
			[
				'add'      => '\\Foo\\Bar',
				'check'    => 'Foo\\Bar',
				'expected' => true,
			],
		],
		'no leading backslash on add, lookup with'  => [
			[
				'add'      => 'Foo\\Bar',
				'check'    => '\\Foo\\Bar',
				'expected' => true,
			],
		],
	],
];
