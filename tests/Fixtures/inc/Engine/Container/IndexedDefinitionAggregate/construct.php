<?php

return [
	'test_data' => [
		'indexes a pre-seeded definition'          => [
			[
				'seed'           => [ [ 'foo', 'FooConcrete' ] ],
				'raw'            => [],
				'check'          => 'foo',
				'has'            => true,
				'expected_index' => 0,
			],
		],
		'first wins on duplicate seeded alias'     => [
			[
				'seed'           => [ [ 'foo', 'FirstConcrete' ], [ 'foo', 'SecondConcrete' ] ],
				'raw'            => [],
				'check'          => 'foo',
				'has'            => true,
				'expected_index' => 0,
			],
		],
		'ignores non-DefinitionInterface entries'  => [
			[
				'seed'           => [],
				'raw'            => [ 'not-a-definition' ],
				'check'          => 'not-a-definition',
				'has'            => false,
				'expected_index' => null,
			],
		],
	],
];
