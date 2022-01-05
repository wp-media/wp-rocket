<?php

declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\XLWooCommerceSalesTriggerSubscriber;

use WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber\WooTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\Ecommerce\XLWooCommerceSalesTriggersSubscriber::modify_delayjs_exclusions_on_product_pages
 * @group   ThirdParty
 * @group   WithWoo
 */
class Test_ModifyDelayJsExclusionsOnProductPages extends TestCase {

	use WooTrait;

	private $product;
	private $single;

	public function setUp(): void {
		parent::setUp();

		$this->product = $this->create_product();
		$this->single = get_post( $this->factory->post->create() );
		$this->unregisterAllCallbacksExcept(
			'rocket_delay_js_exclusions',
			'modify_delayjs_exclusions_on_product_pages'
		);
		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'return_true' ] );
		$this->constants['WCST_VERSION'] = '2.3.4';
	}

	public function tearDown(): void {
		parent::tearDown();

		$this->restoreWpFilter( 'rocket_delay_js_exclusions' );
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'return_true' ] );
		unset( $this->constants['WCST_VERSION'] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function test_should_modify_delay_js_exclusions_when_expected(
		bool $is_product,
		string $original_html,
		string $expected_html
	) {
		$this->go_to( $is_product ? $this->product->get_permalink() : get_permalink( $this->single ) );

		$actual_html = apply_filters( 'rocket_buffer', $original_html );

		$this->assertEquals(
			$this->format_the_html( $expected_html ),
			$this->format_the_html( $actual_html )
		);
	}
}
