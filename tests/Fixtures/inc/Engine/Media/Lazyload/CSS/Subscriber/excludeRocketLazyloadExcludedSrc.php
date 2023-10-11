<?php
return [
    'emptyListShouldReturnSame' => [
        'config' => [
              'excluded' => [],
			  'urls' => [
				  [
					  'selector' => '.class #id',
					  'style' => ':root{--wpr-bg-1sd2s1s:url(https://example.org/test);}',
				  ],
				  [
					  'selector' => '.class #id2',
					  'style' => ':root{--wpr-bg-1sd2s1s:url(https://example.org/test2);}',
				  ]
			  ],
			  'excluded_src' => []

        ],
        'expected' => [

        ]
    ],
	'excludedBySelectorShouldExclude' => [
		'config' => [
			'excluded' => [],
			'urls' => [
				[
					'selector' => '.class #id',
					'style' => ':root{--wpr-bg-1sd2s1s:url(https://example.org/test);}',
				],
				[
					'selector' => '.class #id2',
					'style' => ':root{--wpr-bg-1sd2s1s:url(https://example.org/test2);}',
				]
			],
			'excluded_src' => [
				'#id2',
			]

		],
		'expected' => [
			[
				'selector' => '.class #id2',
				'style' => ':root{--wpr-bg-1sd2s1s:url(https://example.org/test2);}',
			],
		]
	],
	'excludedByUrlShouldExclude' => [
		'config' => [
			'excluded' => [],
			'urls' => [
				[
					'selector' => '.class #id',
					'style' => ':root{--wpr-bg-1sd2s1s:url(https://example.org/test);}',
				],
				[
					'selector' => '.class #id2',
					'style' => ':root{--wpr-bg-1sd2s1s:url(https://example.org/test2);}',
				]
			],
			'excluded_src' => [
				'https://example.org/test2',
			]

		],
		'expected' => [
			[
				'selector' => '.class #id2',
				'style' => ':root{--wpr-bg-1sd2s1s:url(https://example.org/test2);}',
			],
		]
	],
];
