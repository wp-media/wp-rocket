<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::add_subscription_modal
 * @group  RocketCDN
 */
class Test_AddSubscriptionModal extends TestCase {
	private $page;

	public function setUp() {
		parent::setUp();

		$this->page       = new AdminPageSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			$this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
			''
		);
	}

	private function getActualHtml() {
		ob_start();
		$this->page->add_subscription_modal();
		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * Test should display the modal HTML with the production URL in the iframe
	 */
	public function testShouldDisplayModalWithProductionURL() {
		Functions\expect( 'rocket_get_constant' )
			->ordered()
			->once()
			->with( 'WP_ROCKET_DEBUG', false )
			->andReturn( false )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_WEB_MAIN' )
			->andReturn( 'https://wp-rocket.me' );

		Functions\stubs(
			[
				'add_query_arg' => 'https://wp-rocket.me/cdn/iframe?website=http://example.org&callback=http://example.org/wp-json/wp-rocket/v1/rocketcdn/',
				'home_url' => 'http://example.org',
				'rest_url' => 'http://example.org/wp-json/',
				'esc_url'
			]
		);

		$expected = <<<HTML
<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
			<div class="wpr-rocketcdn-modal__overlay" tabindex="-1" data-micromodal-close>
				<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true"
					aria-labelledby="wpr-rocketcdn-modal-title">
					<div id="wpr-rocketcdn-modal-content">
						<iframe id="rocketcdn-iframe" src="https://wp-rocket.me/cdn/iframe?website=http://example.org&callback=http://example.org/wp-json/wp-rocket/v1/rocketcdn/" width="674"
						height="425"></iframe>
					</div>
				</div>
			</div>
		</div>
HTML;

		$this->assertSame( $this->format_the_html($expected), $this->getActualHtml() );
	}

	/**
	 * Test should display the modal HTML with the development URL in the iframe
	 */
	public function testShouldDisplayModalWithDevURL() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_DEBUG', false )
			->andReturn( true );

		Functions\stubs(
			[
				'add_query_arg' => 'https://dave.wp-rocket.me/cdn/iframe?website=http://example.org&callback=http://example.org/wp-json/wp-rocket/v1/rocketcdn/',
				'home_url' => 'http://example.org',
				'rest_url' => 'http://example.org/wp-json/',
				'esc_url'
			]
		);

		$expected = <<<HTML
<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
			<div class="wpr-rocketcdn-modal__overlay" tabindex="-1" data-micromodal-close>
				<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true"
					aria-labelledby="wpr-rocketcdn-modal-title">
					<div id="wpr-rocketcdn-modal-content">
						<iframe id="rocketcdn-iframe" src="https://dave.wp-rocket.me/cdn/iframe?website=http://example.org&callback=http://example.org/wp-json/wp-rocket/v1/rocketcdn/" width="674"
						height="425"></iframe>
					</div>
				</div>
			</div>
		</div>
HTML;

		$this->assertSame( $this->format_the_html($expected), $this->getActualHtml() );
	}
}
