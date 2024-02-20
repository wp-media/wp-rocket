<?php

$content_with_url = file_get_contents( __DIR__ . '/CSS/content_with_url.css' );

$content_without_url = file_get_contents( __DIR__ . '/CSS/content_without_url.css' );

return [
	'noBackgroundUrlShouldReturnEmpty'                  => [
		'config'   => [
			'content' => $content_without_url,
		],
		'expected' => [],
	],
	'BackgroundUrlShouldReturnList'                     => [
		'config'   => [
			'content' => $content_with_url,
		],
		'expected' => [
			'.header-bg-image'                             => [
				[
					'selector' => '.header-bg-image',
					'url'      => 'http://localhost/app/themes/avadanta-trade/assets/images/header-bg.jpg',
					'original' => 'url(\'http://localhost/app/themes/avadanta-trade/assets/images/header-bg.jpg\')',
					'block'    => ".header-bg-image
           {
            background-image:url('http://localhost/app/themes/avadanta-trade/assets/images/header-bg.jpg') !important;
           }",
				],
			],
			'.internal-css-background-image'               => [
				[
					'selector' => '.internal-css-background-image',
					'url'      => '/wp-content/rocket-test-data/images/paper.jpeg',
					'original' => 'url("/wp-content/rocket-test-data/images/paper.jpeg")',
					'block'    => '.internal-css-background-image{
width: 100%;
height: 400px;
background-image: url("/wp-content/rocket-test-data/images/paper.jpeg");
background-color: #cccccc;
}',
				],
			],
			'.internal-css-background-images'              => [
				[
					'selector' => '.internal-css-background-images',
					'url'      => 'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png',
					'original' => "url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png')",
					'block'    => ".internal-css-background-images{
width: 100%;
height: 400px;
background-image: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png'), url( \"/wp-content/rocket-test-data/images/paper.jpeg\" );
background-color: #cccccc;
}",
				],
				[
					'selector' => '.internal-css-background-images',
					'url'      => '/wp-content/rocket-test-data/images/paper.jpeg',
					'original' => 'url( "/wp-content/rocket-test-data/images/paper.jpeg" )',
					'block'    => '.internal-css-background-images{
width: 100%;
height: 400px;
background-image: url(\'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png\'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
background-color: #cccccc;
}',
				],
			],
			'.fl-node-reyn24wd18os > .fl-row-content-wrap' => [
				[
					'selector' => '.fl-node-reyn24wd18os > .fl-row-content-wrap',
					'url'      => 'https://www.villmarksbua.no/wp-content/uploads/2022/03/nordic-pocket-saw-foldbar-tursag-for-tre-og-metall.jpg',
					'original' => 'url(https://www.villmarksbua.no/wp-content/uploads/2022/03/nordic-pocket-saw-foldbar-tursag-for-tre-og-metall.jpg)',
					'block'    => '.fl-node-reyn24wd18os > .fl-row-content-wrap {
 background-image: url(https://www.villmarksbua.no/wp-content/uploads/2022/03/nordic-pocket-saw-foldbar-tursag-for-tre-og-metall.jpg);
 }',
				],
			],
			'.internal-css-background-image-gradient'      => [
				[
					'selector' => '.internal-css-background-image-gradient',
					'url'      => '/wp-content/rocket-test-data/images/paper.jpeg',
					'original' => 'url("/wp-content/rocket-test-data/images/paper.jpeg")',
					'block'    => '.internal-css-background-image-gradient{
width: 100%;
height: 400px;
background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url("/wp-content/rocket-test-data/images/paper.jpeg");
}',
				],
			],
			'.internal-css-background'                     => [
				[
					'selector' => '.internal-css-background',
					'url'      => '/wp-content/rocket-test-data/images/test.png',
					'original' => "url('/wp-content/rocket-test-data/images/test.png')",
					'block'    => ".internal-css-background{
background: url('/wp-content/rocket-test-data/images/test.png');
}",
				],
			],
			'.internal-css-background404'                  => [
				[
					'selector' => '.internal-css-background404',
					'url'      => '/wp-content/rocket-test-data/images/testnotExist.png',
					'original' => "url('/wp-content/rocket-test-data/images/testnotExist.png')",
					'block'    => ".internal-css-background404{
background: url('/wp-content/rocket-test-data/images/testnotExist.png');
}",
				],
			],
			'#internal-BG-images'                          => [
				[
					'selector' => '#internal-BG-images',
					'url'      => '/wp-content/rocket-test-data/images/butterfly.avif',
					'original' => 'url(/wp-content/rocket-test-data/images/butterfly.avif)',
					'block'    => '#internal-BG-images {
background: url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;
padding: 15px;
}',
				],
				[
					'selector' => '#internal-BG-images',
					'url'      => '/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff',
					'original' => 'url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff)',
					'block'    => '#internal-BG-images {
background: url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;
padding: 15px;
}',
				],
				[
					'selector' => '#internal-BG-images',
					'url'      => '/wp-content/rocket-test-data/images/butterfly.avif',
					'original' => 'url(/wp-content/rocket-test-data/images/butterfly.avif)',
					'block'    => '#internal-BG-images{background:url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;padding:15px;}',
				],
				[
					'selector' => '#internal-BG-images',
					'url'      => '/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff',
					'original' => 'url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff)',
					'block'    => '#internal-BG-images{background:url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;padding:15px;}',
				],
			],
			'[class^="icon-"], [class*=" icon-"]'          => [
				[
					'selector' => '[class^="icon-"], [class*=" icon-"]',
					'url'      => '../img/glyphicons-halflings.png',
					'original' => 'url("../img/glyphicons-halflings.png")',
					'block'    => '[class^="icon-"], [class*=" icon-"] {
 display: inline-block;
 width: 14px;
 height: 14px;
 margin-top: 1px;
 *margin-right: .3em;
 line-height: 14px;
 vertical-align: text-top;
 background-image: url("../img/glyphicons-halflings.png");
 background-position: 14px 14px;
 background-repeat: no-repeat
 }',
				],
			],
			'[title~="wp-rocket"]'                         => [
				[
					'selector' => '[title~="wp-rocket"]',
					'url'      => '/wp-content/rocket-test-data/images/wp-rocket.svg',
					'original' => 'url("/wp-content/rocket-test-data/images/wp-rocket.svg")',
					'block'    => '[title~="wp-rocket"] {
 background: url("/wp-content/rocket-test-data/images/wp-rocket.svg") no-repeat;
 }',
				],
			],
			'[title~="wp-rocket-image"]'                   => [
				[
					'selector' => '[title~="wp-rocket-image"]',
					'url'      => '/wp-content/rocket-test-data/images/wp-rocket.svg',
					'original' => 'url("/wp-content/rocket-test-data/images/wp-rocket.svg")',
					'block'    => '[title~="wp-rocket-image"] {
 background-image: url("/wp-content/rocket-test-data/images/wp-rocket.svg") no-repeat;
 }',
				],
			],
			'.external-css-background-image'               => [
				[
					'selector' => '.external-css-background-image',
					'url'      => 'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg',
					'original' => 'url("https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg")',
					'block'    => '.external-css-background-image{
width: 100%;
height: 400px;
background-image: url("https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg");
background-color: #cccccc;
}',
				],
			],
			'.external-css-background-images'              => [
				[
					'selector' => '.external-css-background-images',
					'url'      => '/wp-content/rocket-test-data/images/test.png',
					'original' => "url('/wp-content/rocket-test-data/images/test.png')",
					'block'    => '.external-css-background-images{
width: 100%;
height: 400px;
background-image: url(\'/wp-content/rocket-test-data/images/test.png\'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
background-color: #3dd83d;
}',
				],
				[
					'selector' => '.external-css-background-images',
					'url'      => '/wp-content/rocket-test-data/images/paper.jpeg',
					'original' => 'url( "/wp-content/rocket-test-data/images/paper.jpeg" )',
					'block'    => '.external-css-background-images{
width: 100%;
height: 400px;
background-image: url(\'/wp-content/rocket-test-data/images/test.png\'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
background-color: #3dd83d;
}',
				],
			],
			'.external-css-background-image-gradient'      => [
				[
					'selector' => '.external-css-background-image-gradient',
					'url'      => '/wp-content/rocket-test-data/images/paper.jpeg',
					'original' => 'url(/wp-content/rocket-test-data/images/paper.jpeg)',
					'block'    => '.external-css-background-image-gradient{
width: 100%;
height: 400px;
background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url(/wp-content/rocket-test-data/images/paper.jpeg);
}',
				],
			],
			'.external-css-background'                     => [
				[
					'selector' => '.external-css-background',
					'url'      => '/test.png',
					'original' => "url('/test.png')",
					'block'    => ".external-css-background{
background: url('/test.png') no-repeat;
background-image: url('/test2.png');
background-color: #4fc1dd;
}",
				],
				[
					'selector' => '.external-css-background',
					'url'      => '/test2.png',
					'original' => "url('/test2.png')",
					'block'    => ".external-css-background{
background: url('/test.png') no-repeat;
background-image: url('/test2.png');
background-color: #4fc1dd;
}",
				],
			],
			'.external-css-backgroundsvg'                  => [
				[
					'selector' => '.external-css-backgroundsvg',
					'url'      => 'https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg',
					'original' => "url('https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg')",
					'block'    => ".external-css-backgroundsvg{
/*
.my-style-rocks{
background-image:url('https://wprocketest.test/wp-content/uploads/2015/05/132204857.jpg');
}
*/
background: url('https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg') no-repeat;
background-color: #c63dd8;
}",
				],
			],
		],
	],
	'TwoBlocksWithSameSelectorShouldHaveTheRightBlocks' => [
		'config'   => [
			'content' => file_get_contents( __DIR__ . '/CSS/content_same_selector.css' ),
		],
		'expected' => [
			"#internal-BG-images" => [
				[
					"selector" => "#internal-BG-images",
					"url" => "/wp-content/rocket-test-data/images/butterfly.avif",
					"original" => "url(/wp-content/rocket-test-data/images/butterfly.avif)",
					"block" => "#internal-BG-images {
	background: url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;
	padding: 15px;
}"
				],
				[
					"selector" => "#internal-BG-images",
					"url" => "/wp-content/rocket-test-data/images/butterfly.avif",
					"original" => "url(/wp-content/rocket-test-data/images/butterfly.avif)",
					"block" => "#internal-BG-images{background:url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;padding:15px;}"
				],
			]
		],
	],
	'ExtractorShouldExtractAWideRangeOfSelectors'       => [
		'config'   => [
			'content' => file_get_contents( __DIR__ . '/CSS/content_wide_selectors.css' ),
		],
		'expected' => [
			"#internal-BG-images" => [
				[
					"selector" => "#internal-BG-images",
					"url" => "/wp-content/rocket-test-data/images/butterfly.avif",
					"original" => "url(/wp-content/rocket-test-data/images/butterfly.avif)",
					"block" => "#internal-BG-images {
	background: url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;
	padding: 15px;
}"
				],
				[
					"selector" => "#internal-BG-images",
					"url" => "/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff",
					"original" => "url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff)",
					"block" => "#internal-BG-images {
	background: url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;
	padding: 15px;
}"
				]
			],
			"[title~=\"wp-rocket\"]" => [
	[
		"selector" => "[title~=\"wp-rocket\"]",
                     "url" => "/wp-content/rocket-test-data/images/wp-rocket.svg",
                     "original" => "url(\"/wp-content/rocket-test-data/images/wp-rocket.svg\")",
                     "block" => "[title~=\"wp-rocket\"] {
	background: url(\"/wp-content/rocket-test-data/images/wp-rocket.svg\") no-repeat;
}"
                  ]
               ],
   ".internal-css-background-image" => [
	[
		"selector" => ".internal-css-background-image",
		"url" => "/wp-content/rocket-test-data/images/paper.jpeg",
		"original" => "url(\"/wp-content/rocket-test-data/images/paper.jpeg\")",
                           "block" => ".internal-css-background-image{
	width: 100%;
	height: 400px;
	background-image: url(\"/wp-content/rocket-test-data/images/paper.jpeg\");
	background-color: #cccccc;
}"
                        ]
                     ],
   "[title~=\"wp-rocket-image\"]" => [
	[
		"selector" => "[title~=\"wp-rocket-image\"]",
                                 "url" => "/wp-content/rocket-test-data/images/wp-rocket.svg",
                                 "original" => "url(\"/wp-content/rocket-test-data/images/wp-rocket.svg\")",
                                 "block" => "[title~=\"wp-rocket-image\"] {
	background-image: url(\"/wp-content/rocket-test-data/images/wp-rocket.svg\") no-repeat;
}"
                              ]
                           ],
   "h1[not-existing-attribute-relative~=value]" => [
	[
		"selector" => "h1[not-existing-attribute-relative~=value]",
		"url" => "/wp-content/rocket-test-data/images/wp-rocket.svg",
		"original" => "url(\"/wp-content/rocket-test-data/images/wp-rocket.svg\")",
                                       "block" => "h1[not-existing-attribute-relative~=value]{
	background-image: url(\"/wp-content/rocket-test-data/images/wp-rocket.svg\") no-repeat;
}"
                                    ]
                                 ],
   "h1[not-existing-attribute-relative]" => [
	[
		"selector" => "h1[not-existing-attribute-relative]",
		"url" => "/wp-content/rocket-test-data/images/wp-rocket.svg",
		"original" => "url(\"/wp-content/rocket-test-data/images/wp-rocket.svg\")",
                                             "block" => "h1[not-existing-attribute-relative]{
	background-image: url(\"/wp-content/rocket-test-data/images/wp-rocket.svg\") no-repeat;
}"
                                          ]
                                       ]
],
	],

];
