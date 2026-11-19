<?php

return [
	'test_data' => [
		'retrievable after add'                 => [
			[
				'method'    => 'add',
				'add'       => 'foo',
				'check'     => 'foo',
				'duplicate' => false,
				'shared'    => false,
			],
		],
		'first definition wins on duplicate add' => [
			[
				'method'    => 'add',
				'add'       => 'foo',
				'check'     => 'foo',
				'duplicate' => true,
				'shared'    => false,
			],
		],
		'addShared indexes and marks shared'    => [
			[
				'method'    => 'addShared',
				'add'       => 'foo',
				'check'     => 'foo',
				'duplicate' => false,
				'shared'    => true,
			],
		],
		'normalises leading backslash on add'   => [
			[
				'method'    => 'add',
				'add'       => '\\Foo\\Bar',
				'check'     => 'Foo\\Bar',
				'duplicate' => false,
				'shared'    => false,
			],
		],
	],
];
