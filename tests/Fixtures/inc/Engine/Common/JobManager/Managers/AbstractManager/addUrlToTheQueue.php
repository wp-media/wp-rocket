<?php

return [
	'test_data' => [
		'shouldReturnNullWhenNotAllowed'            => [
			'config'   => [
				'is_allowed' => false,
				'row'        => false,
				'created'    => false,
				'winner'     => false,
			],
			'expected' => [
				'result' => null,
				'reset'  => false,
			],
		],
		'shouldResetAndReturnIdWhenRowExists'       => [
			'config'   => [
				'is_allowed' => true,
				'row'        => (object) [ 'id' => 5 ],
				'created'    => false,
				'winner'     => false,
			],
			'expected' => [
				'result' => 5,
				'reset'  => true,
			],
		],
		'shouldReturnNewIdWhenNoRow'                => [
			'config'   => [
				'is_allowed' => true,
				'row'        => false,
				'created'    => 8,
				'winner'     => false,
			],
			'expected' => [
				'result' => 8,
				'reset'  => false,
			],
		],
		'shouldReturnWinnerIdWithoutResetOnLostRace' => [
			'config'   => [
				'is_allowed' => true,
				'row'        => false,
				'created'    => false,
				'winner'     => (object) [ 'id' => 12 ],
			],
			'expected' => [
				'result' => 12,
				'reset'  => false,
			],
		],
		'shouldReturnFalseWhenInsertFailsWithoutWinner' => [
			'config'   => [
				'is_allowed' => true,
				'row'        => false,
				'created'    => false,
				'winner'     => false,
			],
			'expected' => [
				'result' => false,
				'reset'  => false,
			],
		],
	],
];
