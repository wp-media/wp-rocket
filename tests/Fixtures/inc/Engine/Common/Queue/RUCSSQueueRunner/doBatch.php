<?php

return [
	'test_data' => [
		'shouldNotClaimWhenNoClaimableActions' => [
			'config'   => [
				'size'          => 25,
				'claimable'     => [],
				'claim_actions' => [],
			],
			'expected' => [
				'claims'    => false,
				'processed' => 0,
			],
		],
		'shouldClaimWhenClaimableActions' => [
			'config'   => [
				'size'          => 25,
				'claimable'     => [ 5 ],
				'claim_actions' => [],
			],
			'expected' => [
				'claims'    => true,
				'processed' => 0,
			],
		],
	],
];
