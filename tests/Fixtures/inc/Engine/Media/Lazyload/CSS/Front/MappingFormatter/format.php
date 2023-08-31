<?php
return [
    'shouldDoAsExpected' => [
        'config' => [
              'data' => [
					[
						'hash' => 'a122ad12df3',
						'selector' => '#first_id',
						'url' => 'http://example.org',
					],
				  [
					  'hash' => 'a122ad12df2',
					  'selector' => '#second_id',
					  'url' => 'http://example.org/test',
				  ]
			  ],
        ],
        'expected' => [
			[
				'selector' => '#first_id',
        		'style' => ':root{--wpr-bg-a122ad12df3: http://example.org;}'
			],
			[
				'selector' => '#second_id',
        		'style' => ':root{--wpr-bg-a122ad12df2: http://example.org/test;}'
			]
        ]
    ],
	'shouldRemoveMetaElements' => [
		'config' => [
			'data' => [
				[
					'hash' => 'a122ad12df3',
					'selector' => '#first_id::before',
					'url' => 'http://example.org',
				],
				[
					'hash' => 'a122ad12df2',
					'selector' => '#second_id::after, #test',
					'url' => 'http://example.org/test',
				],
				[
					'hash' => 'a122ad12df3',
					'selector' => '::after',
					'url' => 'http://example.org/test',
				],
				[
					'hash' => 'a122ad12df3',
					'selector' => ':after',
					'url' => 'http://example.org/test',
				],
				[
					'hash' => 'a122ad12df3',
					'selector' => ':last',
					'url' => 'http://example.org/test',
				]
			],
		],
		'expected' => [
			[
				'selector' => '#first_id',
				'style' => ':root{--wpr-bg-a122ad12df3: http://example.org;}'
			],
			[
				'selector' => '#second_id, #test',
				'style' => ':root{--wpr-bg-a122ad12df2: http://example.org/test;}'
			],
			[
				'selector' => 'body',
				'style' => ':root{--wpr-bg-a122ad12df3: http://example.org/test;}'
			],
			[
				'selector' => 'body',
				'style' => ':root{--wpr-bg-a122ad12df3: http://example.org/test;}'
			],
			[
				'selector' => ':last',
				'style' => ':root{--wpr-bg-a122ad12df3: http://example.org/test;}'
			]
		]
	],
];
