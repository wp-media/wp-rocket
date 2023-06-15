<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use wpdb;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::clear_related_post_cache
 * @group  HealthCheck
 */
class Test_ClearRelatedPostCache extends TestCase {
    private $elementor;
	private $wpdb;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
	}

	protected function setUp(): void {
		parent::setUp();

        Functions\expect( 'get_option' )->with( 'stylesheet' )->andReturn( 'twentytwelve' );

        // Load the file once.
		if ( ! function_exists( 'rocket_clean_post' ) ) {
			require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
		}

        $this->elementor = new Elementor( Mockery::mock( Options_Data::class ), null, Mockery::mock( HTML::class ) );
		$GLOBALS['wpdb'] = $this->wpdb = new wpdb();
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config ) {

        $this->wpdb->setPosts( $config['results'] );
        $this->wpdb->starts_with = true;

        if ( empty( $config['results'] ) ) {
            Functions\expect( 'get_post_status' )->never();
            Functions\expect( 'rocket_clean_post' )->never();
        }
        else{
            Functions\expect( 'get_post_status' )
                ->twice()
                ->andReturn( $config['post_status'] );
                
            foreach ( $config['results'] as $result ) {
                if ( 'publish' === $config['post_status'] ) {
                    Functions\expect( 'rocket_clean_post' )->with( $result->post_id )->andReturnNull();
                }
            }
        }

        $this->elementor->clear_related_post_cache( $config['template_id'] );
	}
}
