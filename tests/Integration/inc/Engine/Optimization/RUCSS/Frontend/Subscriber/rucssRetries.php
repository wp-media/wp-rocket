<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::rucss_retries
 * @uses   \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::retries_pages_with_unprocessed_css()
 *
 * @group  RUCSS
 */
class Test_RucssRetries extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Frontend/Subscriber/rucssRetries.php';

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->unregisterAllCallbacksExcept( 'rocket_rucss_retries_cron', 'rucss_retries' );

		if ( $config['rucss-enabled'] ) {
			add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		}

		$container    = apply_filters( 'rocket_container', null );
		$usedCssQuery = $container->get( 'rucss_used_css_query' );

		foreach ( $config['items'] as $item ) {
			$usedCssQuery->add_item( $item );
		}

		do_action( 'rocket_rucss_retries_cron' );

		$itemsAfter = $usedCssQuery->get_results(
			[ 'id', 'retries' ],
			[],
			25,
			null,
			ARRAY_A
		);

		$this->assertSame( $expected['items-after'], $itemsAfter );

		foreach ( $expected['purged-files'] as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file )
			);
		}
	}

	public function set_rucss_option(): bool {
		return true;
	}
}
