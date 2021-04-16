<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Warmup\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\Subscriber::collect_resources
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::delete_used_css
 *
 * @group RUCSS
 */
class Test_CollectResources extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Warmup/Subscriber/collectResources.php';

	private $input = [];

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function setUp(): void {
		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
		];

		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'collect_resources', 1 );
	}

	public function tearDown() : void {
		unset( $GLOBALS['wp'] );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();

		$this->restoreWpFilter( 'rocket_buffer' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input, $expected ){

		$this->donotrocketoptimize = isset( $input['DONOTROCKETOPTIMIZE'] ) ? $input['DONOTROCKETOPTIMIZE'] : false;

		$this->input = $input;

		if ( isset( $input['rocket_bypass'] ) ) {
			$GLOBALS['wp']->query_vars['nowprocket'] = $input['rocket_bypass'];
		}

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		if ( isset( $input['post_metabox_option_excluded'] ) ) {
			global $post;

			$post = $this->factory->post->create_and_get();
			add_post_meta( $post->ID, '_rocket_exclude_remove_unused_css', $input['post_metabox_option_excluded'], true );
		}

		$this->assertSame( $input['html'], apply_filters( 'rocket_buffer', $input['html'] ) );
	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
