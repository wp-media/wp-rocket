<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>Multiple Fonts Every 100px</title>
	<!-- already preloaded -->
	<!-- unused -->

	<!-- used -->

	<style>

		@font-face {
			font-family: "フォント";
			src: url("/wp-content/rocket-test-data/fonts/フォント.woff2") format("woff2"),
			url("/wp-content/rocket-test-data/fonts/フォント.woff") format("woff");
			font-weight: normal;
			font-style: normal;
		}
		/* Load your custom fonts */
		@font-face {
			font-family: 'FontOne';
			src: url('/wp-content/rocket-test-data/fonts/OpenSans-Regular.woff2') format('woff2');
		}

		@font-face {
			font-family: 'FontTwo';
			src: Url('/wp-content/rocket-test-data/fonts/OpenSans-Bold.ttf') format('truetype');
		}

		/* Font URL have space added with %20 */
		@font-face {
			font-family: 'FontThree';

			src: url('/wp-content/rocket-test-data/fonts/test%20font/fa-solid-900.ttf') format('truetype');
		}

		/* font with non-latin char */
		@font-face {
			font-family: 'FontFour';
			src: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/fonts/test-font-with-nonlatinCharÖ.woff') format('woff');
		}
		@font-face {
			font-family: 'FontFive';
			src: url('https://rocketlabsqa.ovh/wp-content/rocket-test-data/fonts/ballet-v3-latin-regular.ttf') format('truetype');
		}
		@font-face {
			font-family: 'FontSix';
			src: url('/wp-content/rocket-test-data/fonts/fa-solid-900.woff2') format('woff2');
		}
		@font-face {
			font-family: 'FontSeven';
			src: url('/wp-content/rocket-test-data/fonts/Softizen.ttf') format('truetype');
		}
		@font-face {
			font-family: 'FontEight';
			src: url('/wp-content/rocket-test-data/fonts/fa-solid-900.woff') format('woff');
		}
		@font-face {
			font-family: 'FontNine';
			src: url('/wp-content/rocket-test-data/fonts/ceviche-one-v11-latin-regular.woff2') format('woff2');
		}
		@font-face {
			font-family: 'FontTen';
			src: URL('/wp-content/rocket-test-data/fonts/OpenSans-Regular.woff') format('woff');
		}
		.systemFontused {
			font-family: Arial;
			margin: 0;
			padding: 0;
			background-color: #f4f4f4;
		}

		.systemFontNotused {
			font-family: sans-serif;
			margin: 0;
			padding: 0;
			background-color: #f4f4f4;
		}




		/* Unused Fonts */
		@font-face {
			font-family: 'UnusedFontOne';
			src: url('/wp-content/rocket-test-data/fonts/bellefair-v6-latin-regular.woff2') format('woff2');
		}

		@font-face {
			font-family: 'UnusedFontTwo';
			src: url('/wp-content/rocket-test-data/fonts/bellefair-v6-latin-regular.woff') format('woff');
		}



		body {
			margin: 0;
			padding: 0;
		}

		.font-section {
			height: 100px;
			padding: 20px;
			border-bottom: 1px solid #ccc;
			box-sizing: border-box;
		}

		.font-0{font-family:"フォント";}

		.font-1 { font-family: 'FontOne'; }
		.font-2 { font-family: 'FontTwo'; }
		.font-3 { font-family: 'FontThree'; }
		.font-4 { font-family: 'FontFour'; }
		.font-5 { font-family: 'FontFive'; }
		.font-6 { font-family: 'FontSix'; }

		.font-7 { font-family: 'FontSeven'; }
		.font-8 { font-family: 'FontEight'; }
		.font-9 { font-family: 'FontNine'; }
		.font-10 { font-family: 'FontTen'; }


	</style>
	<?php wp_head() ?>
</head>
<body>
<div class="font-0">This is Font Zero family "フォント";</div>
<div class="font-section font-1">This is Font One (0–100px)</div>
<div class="font-section font-2">This is Font Two (100–200px)</div>
<div class="font-section font-3">This is Font Three (200–300px)</div>
<div class="font-section systemFont">This is system Font ATF (300–400px)</div>

<div class="font-section font-4">This is Font four(400–500px)</div>
<div class="font-section font-5">This is Font Five (500–600px)</div>
<div class="font-section font-6">This is Font 6 (500–600px)</div>
<div class="font-section font-7">This is Font 7(600–700px)</div>
<div class="font-section font-8">.This is Font 8 (700–800px)</div>
<div class="font-section font-9">.This is Font 9 (800–900px)</div>
<div class="font-section font-10">.This is Font 10 (900–1000px)</div>
<div class="font-section systemFont">.This is system font below fold (1000–1100px)</div>
<!-- Continue until 1200px -->
<?php wp_footer() ?>
</body>
</html>
