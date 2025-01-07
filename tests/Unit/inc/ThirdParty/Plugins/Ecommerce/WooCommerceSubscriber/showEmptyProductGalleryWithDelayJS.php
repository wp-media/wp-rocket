<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use Mockery;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::show_empty_product_gallery_with_delayJS
 * @group WooCommerce
 * @group ThirdParty
 */
class Test_ShowEmptyProductGalleryWithDelayJS extends TestCase {

	private $subscriber;
	private $delayjs_html;

	public function setUp() : void {
		parent::setUp();

		$this->delayjs_html = Mockery::mock( HTML::class );
		$this->subscriber   = new WooCommerceSubscriber( $this->delayjs_html );
	}

	public function tearDown() : void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$is_allowed      = $config['is_allowed'] ?? null;
		$in_product_page = $config['in_product_page'] ?? null;
		$has_images      = $config['has_images'] ?? null;

		if ( ! is_null( $is_allowed ) ) {
			$this->delayjs_html->shouldReceive( 'is_allowed' )->once()->andReturn( $is_allowed );
		}

		if ( ! is_null( $in_product_page ) ) {
			Functions\expect( 'is_product' )
				->once()
				->withNoArgs()
				->andReturn( $in_product_page );
		}

		if ( ! is_null( $has_images ) ) {
			Functions\expect( 'get_the_ID' )
				->once()
				->withNoArgs()
				->andReturn( 'product_id' );

			$product = new class( $has_images ) {
				private $has_images;
				public function __construct( $has_images ) {
					$this->has_images = $has_images;
				}

				public function get_gallery_image_ids() {
					return (int) $this->has_images;
				}
			};

			Functions\expect( 'wc_get_product' )
				->once()
				->with( 'product_id' )
				->andReturn( $product );
		}

		$this->expectOutputString( $expected );

		$this->subscriber->show_empty_product_gallery_with_delayJS();
	}

}
