<?php

return [
	'testShouldDoNothingWhenVersionAbove319' => [
		'config' => [
			'new' => '3.20',
			'old' => '3.19',
			'options' => [
				'dns_prefetch' => []
			]
		],
		'expected' => [
			'options' => [
				'dns_prefetch' => []
			]
		],
	],
	'testShouldDoNothingIfDnsPrefetchValueIsEmpty' => [
		'config' => [
			'new' => '3.19',
			'old' => '3.18',
			'options' => [
				'dns_prefetch' => []
			]
		],
		'expected' => [
			'options' => [
				'dns_prefetch' => [],
			],
		],
	],
	'testShouldDeleteDnsPrefetchValueWhenNotEmptyAndVersionUnder319' => [
		'config' => [
			'new' => '3.19',
			'old' => '3.18',
			'options' => [
				'dns_prefetch' => [
					'//example.org',
                    '//example2.org',
                    '//example3.org',
                ],
			]
		],
		'expected' => [
			'options' => [
				'dns_prefetch' => []
			]
		],
	],
];
