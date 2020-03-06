<?php

namespace WP_Rocket\Tests\Integration\inc\optimization\CSS\Combine_Google_Fonts;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Optimization\CSS\Combine_Google_Fonts;

/**
 * @covers \WP_Rocket\Optimization\CSS\Combine_Google_Fonts::optimize
 * @uses   \WP_Rocket\Logger\Logger
 * @group  Optimize
 */
class Test_Optimize extends TestCase {
	/**
     * @dataProvider addDataProvider
     */
	public function testShouldCombineGoogleFonts( $original, $combined ) {
		$combine = new Combine_Google_Fonts();

		$this->assertSame(
			$combined,
			$combine->optimize( $original )
		);
	}

	public function addDataProvider() {
        return [
			[
				"<html>" .
					"<head>" .
						"<title>Sample Page</title>" .
						"<link rel='stylesheet' id='dt-web-fonts-css'  href='//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2' type='text/css' media='all' />" .
						"<link rel='stylesheet' id='ultimate-google-fonts-css'  href='https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|' type='text/css' media='all' />" .
						"<link href='https://fonts.googleapis.com/css?family=Josefin+Sans:300' rel='stylesheet' property='stylesheet' type='text/css' media='all'>" .
					"</head>" .
					"<body>" .
					"</body>" .
				"</html>",
				"<html>" .
					"<head>" .
						'<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&display=swap" />' .
					"</head>" .
					"<body>" .
					"</body>" .
				"</html>",
			],
			[
			 "<html>" .
			     "<head>" .
			         "<title>Sample Page</title>" .
			         "<link rel='stylesheet' id='dt-web-fonts-css'  href='https://fonts.googleapis.com/css?family=Lato:100,300,400,600,700,900%7COpen+Sans:700,300,600,400%7CRaleway:900%7CPlayfair+Display%7C&#038;ver=9adfdbd43f39a234a09de4319e14b851' type='text/css' media='all' />" .
			         "<link rel='stylesheet' id='ultimate-google-fonts-css'  href='https://fonts.googleapis.com/css?family=Open+Sans%7CJockey+One:400&#038;subset=latin&#038;ver=1549273751' type='text/css' media='all' />" .
			         '<link href="https://fonts.googleapis.com/css?family=Abril+Fatface:regular&#038;ver=9adfdbd43f39a234a09de4319e14b851" rel="stylesheet" property="stylesheet" type="text/css" media="all">' .
			     "</head>" .
			     "<body>" .
			     "</body>" .
			 "</html>",
			 "<html>" .
			     "<head>" .
			         '<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato%3A100%2C300%2C400%2C600%2C700%2C900%7COpen%20Sans%3A700%2C300%2C600%2C400%7CRaleway%3A900%7CPlayfair%20Display%7COpen%20Sans%7CJockey%20One%3A400%7CAbril%20Fatface%3Aregular&subset=latin&display=swap" />' .
			     "</head>" .
			     "<body>" .
			     "</body>" .
			 "</html>",
			]
		];
    }
}
