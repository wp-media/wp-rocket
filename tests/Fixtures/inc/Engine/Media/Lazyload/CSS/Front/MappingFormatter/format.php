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
        		'style' => '#first_id{--wpr-bg-a122ad12df3: url(\'http://example.org\');}',
				'hash' => 'a122ad12df3',
				'url' => 'http://example.org',
			],
			[
				'selector' => '#second_id',
        		'style' => '#second_id{--wpr-bg-a122ad12df2: url(\'http://example.org/test\');}',
				'hash' => 'a122ad12df2',
				'url' => 'http://example.org/test',
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
				'style' => '#first_id::before{--wpr-bg-a122ad12df3: url(\'http://example.org\');}',
				'hash' => 'a122ad12df3',
				'url' => 'http://example.org',
			],
			[
				'selector' => '#second_id, #test',
				'style' => '#second_id::after, #test{--wpr-bg-a122ad12df2: url(\'http://example.org/test\');}',
				'hash' => 'a122ad12df2',
				'url' => 'http://example.org/test',
			],
			[
				'selector' => 'body',
				'style' => '::after{--wpr-bg-a122ad12df3: url(\'http://example.org/test\');}',
				'hash' => 'a122ad12df3',
				'url' => 'http://example.org/test',
			],
			[
				'selector' => 'body',
        		'style' => '::after{--wpr-bg-a122ad12df3: url(\'http://example.org/test\');}',
				'hash' => 'a122ad12df3',
				'url' => 'http://example.org/test',
			],
			[
				'selector' => 'dd:nth-last-of-type(3n)',
				'style' => 'dd:nth-last-of-type(3n){--wpr-bg-a21ss2: url(\'images/underline.png\');}',
				'hash' => 'a21ss2',
				'url' => 'images/underline.png',
			],
			[
				'selector' => '.background-image~*',
				'style' => '.background-image~:after{--wpr-bg-a21ss4: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss4',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => '.background-image>*',
				'style' => '.background-image>:before,.background-image>:after{--wpr-bg-a21ss8: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss8',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => '.background-image>*',
				'style' => '.background-image>::before,.background-image>::after{--wpr-bg-a21ss18: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss18',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => '.background-image>*',
				'style' => '.background-image>::before{--wpr-bg-a21ss18: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss18',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => '.background-image',
				'style' => '.background-image::first-letter{--wpr-bg-a21ss25: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss25',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => '.subscribe',
				'style' => '.subscribe:active{--wpr-bg-a21ss25: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss25',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => '.subscribe',
				'style' => '.subscribe:focus{--wpr-bg-a21ss25: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss25',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => '.subscribe',
				'style' => '.subscribe:hover{--wpr-bg-a21ss25: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss25',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => 'a',
				'style' => 'a:visited{--wpr-bg-a21ss25: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss25',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg'
			],
			[
				'selector' => '.subscribe',
				'style' => '.subscribe:focus-within{--wpr-bg-a21ss25: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss25',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
			[
				'selector' => '.subscribe',
				'style' => '.subscribe:focus-visible{--wpr-bg-a21ss25: url(\'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg\');}',
				'hash' => 'a21ss25',
				'url' => 'images/maxime-lebrun-6g3Akg708E0-unsplash.jpg',
			],
		]
	],
	'shouldOnlySplitSelectorListOnTopLevelCommas' => [
		'config' => [
			'data' => [
				[
					'hash' => 'a31ss1',
					'selector' => ':is( .jet-listing-dynamic-post-18848:not( [class*=elementor-dcss-] ),.elementor-dcss-2500093068552996 ) .elementor-element.elementor-element-c1a7be2:not(.elementor-motion-effects-element-type-background),:is( .jet-listing-dynamic-post-18848:not( [class*=elementor-dcss-] ),.elementor-dcss-2500093068552996 ) .elementor-element.elementor-element-c1a7be2>.elementor-motion-effects-container>.elementor-motion-effects-layer',
					'url' => 'images/underline.png',
				],
				[
					'hash' => 'a31ss2',
					'selector' => ':is(.a,.b) .c:not(.x),:is(.a,.b) .c>.d',
					'url' => 'images/underline.png',
				],
				[
					'hash' => 'a31ss3',
					'selector' => ':is(.a,.b) .c:hover,:is(.a,.b) .c:focus',
					'url' => 'images/underline.png',
				],
				[
					'hash' => 'a31ss4',
					'selector' => ':where(.a, .b) .c::before',
					'url' => 'images/underline.png',
				],
				[
					'hash' => 'a31ss5',
					'selector' => '.a[data-x="1,2"]:hover,.b',
					'url' => 'images/underline.png',
				],
				[
					'hash' => 'a31ss6',
					'selector' => '.a\\,b:hover,.c',
					'url' => 'images/underline.png',
				],
			],
		],
		'expected' => [
			[
				'selector' => ':is( .jet-listing-dynamic-post-18848:not( [class*=elementor-dcss-] ),.elementor-dcss-2500093068552996 ) .elementor-element.elementor-element-c1a7be2:not(.elementor-motion-effects-element-type-background),:is( .jet-listing-dynamic-post-18848:not( [class*=elementor-dcss-] ),.elementor-dcss-2500093068552996 ) .elementor-element.elementor-element-c1a7be2>.elementor-motion-effects-container>.elementor-motion-effects-layer',
				'style' => ':is( .jet-listing-dynamic-post-18848:not( [class*=elementor-dcss-] ),.elementor-dcss-2500093068552996 ) .elementor-element.elementor-element-c1a7be2:not(.elementor-motion-effects-element-type-background),:is( .jet-listing-dynamic-post-18848:not( [class*=elementor-dcss-] ),.elementor-dcss-2500093068552996 ) .elementor-element.elementor-element-c1a7be2>.elementor-motion-effects-container>.elementor-motion-effects-layer{--wpr-bg-a31ss1: url(\'images/underline.png\');}',
				'hash' => 'a31ss1',
				'url' => 'images/underline.png',
			],
			[
				'selector' => ':is(.a,.b) .c:not(.x),:is(.a,.b) .c>.d',
				'style' => ':is(.a,.b) .c:not(.x),:is(.a,.b) .c>.d{--wpr-bg-a31ss2: url(\'images/underline.png\');}',
				'hash' => 'a31ss2',
				'url' => 'images/underline.png',
			],
			[
				'selector' => ':is(.a,.b) .c',
				'style' => ':is(.a,.b) .c:hover,:is(.a,.b) .c:focus{--wpr-bg-a31ss3: url(\'images/underline.png\');}',
				'hash' => 'a31ss3',
				'url' => 'images/underline.png',
			],
			[
				'selector' => ':where(.a, .b) .c',
				'style' => ':where(.a, .b) .c::before{--wpr-bg-a31ss4: url(\'images/underline.png\');}',
				'hash' => 'a31ss4',
				'url' => 'images/underline.png',
			],
			[
				'selector' => '.a[data-x="1,2"],.b',
				'style' => '.a[data-x="1,2"]:hover,.b{--wpr-bg-a31ss5: url(\'images/underline.png\');}',
				'hash' => 'a31ss5',
				'url' => 'images/underline.png',
			],
			[
				'selector' => '.a\\,b,.c',
				'style' => '.a\\,b:hover,.c{--wpr-bg-a31ss6: url(\'images/underline.png\');}',
				'hash' => 'a31ss6',
				'url' => 'images/underline.png',
			],
		],
	],
];
