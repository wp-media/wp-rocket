<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\ThirdParty\Themes\Divi;
use WP_Theme;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::maybe_disable_youtube_preview
 * @uses   ::is_divi
 *
 * @group  ThirdParty
 */
class Test_MaybeDisableYoutubePreview extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/maybeDisableYoutubePreview.php';
	private static $container;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'divi' ) );
	}

	public function setUp() {
		parent::setUp();

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'divi' ) );
	}

	public function tearDown() {
		global $wp_theme_directories;
		unset( $wp_theme_directories['virtual'] );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testSetsCorrectOptions( $config, $expected ) {
		// For testing Child themes we need to build an integration fixture framework for WP_Theme objects.
		if ( isset ( $config['int-skip'] ) && $config['int-skip'] ) {
			$this->assertTrue( true );
			return;
		}

		$options     = self::$container->get( 'options' );
		$options_api = self::$container->get( 'options_api' );
		$options_api->set( 'settings', [] );

		$divi        = new Divi( $options_api, $options );

		add_filter(
			'pre_option_stylesheet',
			function () use ( $config ) {
				return $config['stylesheet'];
			}
		);
		add_filter(
			'pre_option_stylesheet_root',
			function () {
				global $wp_theme_directories;

				$wp_theme_directories['virtual'] = $this->filesystem->getUrl( 'wp-content/themes/' );

				return $this->filesystem->getUrl( 'wp-content/themes/' );
			}
		);

		$theme = new WP_Theme( $config['stylesheet'], 'wp-content/themes/' );

		switch_theme( $config['stylesheet'] );

		$divi->maybe_disable_youtube_preview( $config['stylesheet'], $theme );

		$this->assertSame( $expected['settings'], $options_api->get( 'settings' ) );
	}
}
