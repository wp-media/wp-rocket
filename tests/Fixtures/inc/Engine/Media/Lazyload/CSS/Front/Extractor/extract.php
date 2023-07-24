<?php

$content_with_url = file_get_contents(__DIR__ . '/CSS/content_with_url.php');

$content_without_url = file_get_contents(__DIR__ . '/CSS/content_without_url.php');

return [
    'noBackgroundUrlShouldReturnEmpty' => [
        'config' => [
              'content' => $content_without_url,
        ],
        'expected' => []
    ],
	'BackgroundUrlShouldReturnList' => [
		'config' => [
			'content' => $content_with_url,
		],
		'expected' => [
			'.header-bg-image' => [
 [
			'selector' => '.header-bg-image',
            'url' => 'url(\'http://localhost/app/themes/avadanta-trade/assets/images/header-bg.jpg\')',
            'block' => ".header-bg-image
           {
            background-image:url('http://localhost/app/themes/avadanta-trade/assets/images/header-bg.jpg') !important;
           }"
        ]
    ],
			'.internal-css-background-image' => [
					[
					'selector' => '.internal-css-background-image',
        			'url' => 'url("/wp-content/rocket-test-data/images/paper.jpeg")',
        			'block' => '.internal-css-background-image{
width: 100%;
height: 400px;
background-image: url("/wp-content/rocket-test-data/images/paper.jpeg");
background-color: #cccccc;
}'
					],
				],
			'.internal-css-background-images' => [
				[
					'selector' => '.internal-css-background-images',
					'url' => "url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png')",
					'block' => ".internal-css-background-images{
width: 100%;
height: 400px;
background-image: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png'), url( \"/wp-content/rocket-test-data/images/paper.jpeg\" );
background-color: #cccccc;
}"
				],
				[
					'selector' => '.internal-css-background-images',
					'url' => 'url( "/wp-content/rocket-test-data/images/paper.jpeg" )',
					'block' => '.internal-css-background-images{
width: 100%;
height: 400px;
background-image: url(\'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png\'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
background-color: #cccccc;
}'
				],
			],
			'.internal-css-background-image-gradient' => [
				[
					'selector' => '.internal-css-background-image-gradient',
					'url' => 'url("/wp-content/rocket-test-data/images/paper.jpeg")',
					'block' => '.internal-css-background-image-gradient{
width: 100%;
height: 400px;
background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url("/wp-content/rocket-test-data/images/paper.jpeg");
}'
				],
			],
			'.internal-css-background' => [
				[
					'selector' => '.internal-css-background',
					'url' => "url('/wp-content/rocket-test-data/images/test.png')",
					'block' => ".internal-css-background{
background: url('/wp-content/rocket-test-data/images/test.png');
}"
				],
			],
			'.internal-css-background404' => [
				[
					'selector' => '.internal-css-background404',
					'url' => "url('/wp-content/rocket-test-data/images/testnotExist.png')",
					'block' => ".internal-css-background404{
background: url('/wp-content/rocket-test-data/images/testnotExist.png');
}"
				],
			],
			'#internal-BG-images' => [
				[
					'selector' => '#internal-BG-images',
					'url' => 'url(/wp-content/rocket-test-data/images/butterfly.avif)',
					'block' => '#internal-BG-images {
background: url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;
padding: 15px;
}'
				],
				[
					'selector' => '#internal-BG-images',
					'url' => 'url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff)',
					'block' => '#internal-BG-images {
background: url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;
padding: 15px;
}'
				],
				[
					'selector' => '#internal-BG-images',
					'url' => 'url(/wp-content/rocket-test-data/images/butterfly.avif)',
					'block' => '#internal-BG-images{background:url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;padding:15px;}'
				],
				[
					'selector' => '#internal-BG-images',
					'url' => 'url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff)',
					'block' => '#internal-BG-images{background:url(/wp-content/rocket-test-data/images/butterfly.avif) right bottom no-repeat, url(/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff) left top repeat;padding:15px;}'
				],
			],
			'.external-css-background-image' => [
				[
					'selector' => '.external-css-background-image',
					'url' => 'url("https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg")',
					'block' => '.external-css-background-image{
width: 100%;
height: 400px;
background-image: url("https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg");
background-color: #cccccc;
}'
				],
			],
			'.external-css-background-images' => [
				[
					'selector' => '.external-css-background-images',
					'url' => "url('/wp-content/rocket-test-data/images/test.png')",
					'block' => '.external-css-background-images{
width: 100%;
height: 400px;
background-image: url(\'/wp-content/rocket-test-data/images/test.png\'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
background-color: #3dd83d;
}'
				],
				[
					'selector' => '.external-css-background-images',
					'url' => 'url( "/wp-content/rocket-test-data/images/paper.jpeg" )',
					'block' => '.external-css-background-images{
width: 100%;
height: 400px;
background-image: url(\'/wp-content/rocket-test-data/images/test.png\'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
background-color: #3dd83d;
}'
				],
			],
			'.external-css-background-image-gradient' => [
				[
					'selector' => '.external-css-background-image-gradient',
					'url' => 'url(/wp-content/rocket-test-data/images/paper.jpeg)',
					'block' => '.external-css-background-image-gradient{
width: 100%;
height: 400px;
background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url(/wp-content/rocket-test-data/images/paper.jpeg);
}'
				],
			],
			'.external-css-background' => [
				[
					'selector' => '.external-css-background',
					'url' => "url('/test.png')",
					'block' => ".external-css-background{
background: url('/test.png') no-repeat;
background-color: #4fc1dd;
}"
				],
			],
			'.external-css-backgroundsvg' => [
				[
					'selector' => '.external-css-backgroundsvg',
					'url' => "url('https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg')",
					'block' => ".external-css-backgroundsvg{
/*
.my-style-rocks{
background-image:url('https://wprocketest.test/wp-content/uploads/2015/05/132204857.jpg');
}
*/
background: url('https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg') no-repeat;
background-color: #c63dd8;
}"
				],
			]
		]
	]
];
