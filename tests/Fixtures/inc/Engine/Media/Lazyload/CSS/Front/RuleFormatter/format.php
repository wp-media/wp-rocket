<?php


$css = <<<CSS
	.internal-css-background-image{
		width: 100%;
		height: 400px;
		background-image: url("/wp-content/rocket-test-data/images/paper.jpeg");
		background-color: #cccccc;
	}
	.internal-css-background-images{
		width: 100%;
		height: 400px;
		background-image: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
		background-color: #cccccc;
	}
	.internal-css-background-image-gradient{
		width: 100%;
		height: 400px;
		background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url("/wp-content/rocket-test-data/images/paper.jpeg");
	}
	.internal-css-background{
		background: url('/wp-content/rocket-test-data/images/test.png');
	}
	.internal-css-background{
		width: 100%;
		height: 400px;
		background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url("/wp-content/rocket-test-data/images/paper.jpeg");
	}
CSS;


$css_formatted = <<<CSS
	.internal-css-background-image{
		width: 100%;
		height: 400px;
		background-image: url("/wp-content/rocket-test-data/images/paper.jpeg");
		background-color: #cccccc;
	}
	.internal-css-background-images{
		width: 100%;
		height: 400px;
		background-image: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
		background-color: #cccccc;
	}
	.internal-css-background-image-gradient{
		width: 100%;
		height: 400px;
		background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url("/wp-content/rocket-test-data/images/paper.jpeg");
	}
	.internal-css-background{
		background: var(--wpr-bg-_ida122ad12df2);
	}
	.internal-css-background{
		width: 100%;
		height: 400px;
		background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), var(--wpr-bg-_ida122ad12df4);
	}
CSS;


return [
    'shouldReturnAsExpected' => [
        'config' => [
              'css' => $css,
              'data' => [
				  [
					  'selector' => '.internal-css-background',
					  'url' => "url('/wp-content/rocket-test-data/images/test.png')",
					  'original' => "url('/wp-content/rocket-test-data/images/test.png')",
					  'block' => "	.internal-css-background{
		background: url('/wp-content/rocket-test-data/images/test.png');
	}",
					  'hash' => '_ida122ad12df2',
				  ],
				  [
					  'selector' => '.internal-css-background',
					  'url' => "url('/wp-content/rocket-test-data/images/test.png')",
					  'original' =>	'url("/wp-content/rocket-test-data/images/paper.jpeg")',
					  'block' => '.internal-css-background{
		width: 100%;
		height: 400px;
		background-image: linear-gradient(rgba(0, 0, 255, 0.5), rgba(255, 255, 0, 0.5)), url("/wp-content/rocket-test-data/images/paper.jpeg");
	}',
					  'hash' => '_ida122ad12df4',
				  ],
			  ],

        ],
        'expected' => $css_formatted
    ],
];
