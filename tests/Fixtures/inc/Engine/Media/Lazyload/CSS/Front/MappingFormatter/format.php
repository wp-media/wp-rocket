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
				'selector' => '#first_ida122ad12df3',
        		'style' => ':root{--wpr-bg-`#first_ida122ad12df3`: http://example.org;}'
			],
			[
				'selector' => '#second_ida122ad12df2',
        		'style' => ':root{--wpr-bg-`#second_ida122ad12df2`: http://example.org/test;}'
			]
        ]
    ],

];
