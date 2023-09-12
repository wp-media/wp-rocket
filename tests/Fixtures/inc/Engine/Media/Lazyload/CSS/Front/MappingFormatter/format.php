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
					'selector' => '::after',
					'url' => 'http://example.org/test',
				],
				[
					'hash' => 'a21ss2',
					'selector' => 'dd:nth-last-of-type(3n)',
					'url' => 'images/underline.png'
				],
				[
					'hash' => 'a21ss4',
					'selector' => '.background-image~:after',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss8',
					'selector' => '.background-image>:before,.background-image>:after',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss18',
					'selector' => '.background-image>::before,.background-image>::after',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss18',
					'selector' => '.background-image>::before',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss25',
					'selector' => '.background-image::first-letter',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss25',
					'selector' => '.subscribe:active',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss25',
					'selector' => '.subscribe:focus',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss25',
					'selector' => '.subscribe:hover',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss25',
					'selector' => 'a:visited',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss25',
					'selector' => '.subscribe:focus-within',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
				[
					'hash' => 'a21ss25',
					'selector' => '.subscribe:focus-visible',
					'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
				],
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
				'selector' => 'dd:nth-last-of-type(3n)',
				'style' => ':root{--wpr-bg-a21ss2: images/underline.png;}'
			],
			[
				'selector' => '.background-image~*',
				'style' => ':root{--wpr-bg-a21ss4: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.background-image>*',
				'style' => ':root{--wpr-bg-a21ss8: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.background-image>*',
				'style' => ':root{--wpr-bg-a21ss18: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.background-image>*',
				'style' => ':root{--wpr-bg-a21ss18: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.background-image',
				'style' => ':root{--wpr-bg-a21ss25: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.subscribe',
				'style' => ':root{--wpr-bg-a21ss25: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.subscribe',
				'style' => ':root{--wpr-bg-a21ss25: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.subscribe',
				'style' => ':root{--wpr-bg-a21ss25: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => 'a',
				'style' => ':root{--wpr-bg-a21ss25: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.subscribe',
				'style' => ':root{--wpr-bg-a21ss25: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
			[
				'selector' => '.subscribe',
				'style' => ':root{--wpr-bg-a21ss25: images/maxime-lebrun-6g3Akg708E0-unsplash.jpg;}'
			],
		]
	],
];
