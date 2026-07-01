<?php

return [
	'clearsUnionOfOldAndNewCnames'       => [
		'config'   => [
			'old_value' => [
				'cdn_cnames' => [ 'https://old-cdn.example.com' ],
			],
			'new_value' => [
				'cdn_cnames' => [ 'https://new-cdn.example.com' ],
			],
		],
		'expected' => [
			'cleared_cnames' => [ 'https://old-cdn.example.com', 'https://new-cdn.example.com' ],
		],
	],
	'clearsBothWhenSameCnameInOldAndNew' => [
		'config'   => [
			'old_value' => [
				'cdn_cnames' => [ 'https://cdn.example.com' ],
			],
			'new_value' => [
				'cdn_cnames' => [ 'https://cdn.example.com' ],
			],
		],
		'expected' => [
			'cleared_cnames' => [ 'https://cdn.example.com' ],
		],
	],
	'clearsEmptyArrayWhenNoCdnCnames'    => [
		'config'   => [
			'old_value' => [ 'other_setting' => 1 ],
			'new_value' => [ 'other_setting' => 2 ],
		],
		'expected' => [
			'cleared_cnames' => [],
		],
	],
	'clearsOnlyOldWhenNewHasNoCnames'    => [
		'config'   => [
			'old_value' => [
				'cdn_cnames' => [ 'https://cdn.example.com' ],
			],
			'new_value' => [ 'other_setting' => 1 ],
		],
		'expected' => [
			'cleared_cnames' => [ 'https://cdn.example.com' ],
		],
	],
];
