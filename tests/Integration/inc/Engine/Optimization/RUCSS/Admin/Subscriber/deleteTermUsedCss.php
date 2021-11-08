<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::delete_term_used_css
 *
 * @group  RUCSS
 */
class Test_DeleteTermUsedCss extends TestCase {
	use DBTrait;

	private $rucss_option;

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function tearDown(): void {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config ) {
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

		$this->rucss_option = $config['remove_unused_css'];
		$this->set_permalink_structure( "/%postname%/" );

		$term = $this->factory->term->create_and_get([
			'name' => 'test_taxonomy'
		]);

		$item = [
			'url' => untrailingslashit( get_term_link( $term->term_id ) ),
		];

		$rucss_usedcss_query->add_item( $item );

		$result = $rucss_usedcss_query->query();
		$this->assertCount( 1, $result );

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		if ( $config['wp_error'] ) {
			do_action( 'edit_term', 0, 0, 'category' );

		} else {
			do_action( 'edit_term', $term->term_id, $term->term_taxonomy_id, $term->taxonomy );
		}


		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$resultAfterDelete = $rucss_usedcss_query->query();

		if ( $config['removed'] ) {
			$this->assertCount( 0, $resultAfterDelete );
		} else {
			$this->assertCount( 1, $resultAfterDelete );
		}
	}

	public function set_rucss_option() {
		return $this->rucss_option;
	}
}
