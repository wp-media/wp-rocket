<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use Mockery;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::show_empty_product_gallery_with_delayJS
 * @group WooCommerce
 * @group ThirdParty
 * @group WithWoo
 */
class Test_ShowEmptyProductGalleryWithDelayJS extends TestCase {

	private $delay_js_option;
	private $product_with_gallery;
	private $product_without_gallery;

	private function create_product( $gallery_image_ids = [] ) {
		$product       = new \WC_Product_Simple();
		$product_data =
			array(
				'name'          => 'Dummy Product',
				'regular_price' => 10,
				'price'         => 10,
				'sku'           => 'DUMMY SKU',
				'manage_stock'  => false,
				'tax_status'    => 'taxable',
				'downloadable'  => false,
				'virtual'       => false,
				'stock_status'  => 'instock',
				'weight'        => '1.1',
				'gallery_image_ids' => $gallery_image_ids,
			);

		$product->set_props( $product_data );

		$product->save();
		return wc_get_product( $product->get_id() );
	}

	public function setUp() : void {
		parent::setUp();

		$this->product_without_gallery = $this->create_product();
		$this->product_with_gallery = $this->create_product( [1, 2, 3] );
	}

	public function tearDown() : void {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->delay_js_option      = $config['is_allowed'] ?? false;
		$in_product_page = $config['in_product_page'] ?? null;
		$has_images      = $config['has_images'] ?? null;

		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js' ] );

		if ( $in_product_page ) {
			$this->go_to( $has_images ? $this->product_with_gallery->get_permalink() : $this->product_without_gallery->get_permalink() );
		}else{
			$this->go_to( home_url() );
		}

		foreach ( wp_styles()->registered as $style ) {
			if ( 'woocommerce-layout' === $style->handle ) {
				$this->assertArrayNotHasKey( 'after', $style->extra );
			}
		}

		do_action( 'wp_enqueue_scripts' );

		$extra_after = [];
		foreach ( wp_styles()->registered as $style ) {
			if ( 'woocommerce-layout' === $style->handle ) {
				$extra_after = $style->extra;
			}
		}

		if ( empty( $expected['style'] ) ) {
			$this->assertArrayNotHasKey( 'after', $extra_after );
		}else{
			$this->assertArrayHasKey( 'after', $extra_after );
			$this->assertNotEmpty( $extra_after['after'] );
			$this->assertContains( '.woocommerce-product-gallery{ opacity: 1 !important; }', $extra_after['after'] );
		}

	}

	public function set_delay_js($options) {
		return $this->delay_js_option;
	}

}
