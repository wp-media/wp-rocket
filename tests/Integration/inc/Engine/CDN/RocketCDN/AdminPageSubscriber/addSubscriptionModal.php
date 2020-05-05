<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::add_subscription_modal
 *
 * @uses ::rocket_is_live_site
 * @uses ::rocket_get_constant
 *
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_AddSubscriptionModal extends TestCase {

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_settings_page_footer' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		$callback = function() {
			return 'http://localhost';
		};

		add_filter( 'home_url', $callback );

		$this->assertEmpty( $this->getActualHtml() );

		remove_filter( 'home_url', $callback );
	}

	public function testShouldDisplayModalWithProductionURL() {
		$expected = <<<HTML
<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
	<div class="wpr-rocketcdn-modal__overlay" tabindex="-1">
		<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true" aria-labelledby="wpr-rocketcdn-modal-title">
			<div id="wpr-rocketcdn-modal-content">
				<iframe id="rocketcdn-iframe" src="https://wp-rocket.me/cdn/iframe?website=http://example.org&#038;callback=http://example.org/index.php?rest_route=/wp-rocket/v1/rocketcdn/" width="674" height="425"></iframe>
			</div>
		</div>
	</div>
</div>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}
}
