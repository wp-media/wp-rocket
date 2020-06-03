<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber;
use WP_Rocket\Tests\StubTrait;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::add_subscription_modal
 * @group  RocketCDN
 */
class Test_AddSubscriptionModal extends TestCase {
	use StubTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->stubRocketGetConstant();

		Functions\stubs(
			[
				'home_url'      => 'http://example.org',
				'rest_url'      => 'http://example.org/wp-json/',
			]
		);

		$this->page = new AdminPageSubscriber(
			Mockery::mock( APIClient::class ),
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Beacon::class ),
			'views/settings/rocketcdn'
		);
	}

	public function tearDown() {
		$this->resetStubProperties();

		parent::tearDown();
	}

	public function testShouldDisplayNothingWhenWhiteLabel() {
		$this->white_label = true;

		$this->assertNull( $this->page->add_subscription_modal() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		Functions\when( 'rocket_is_live_site' )->justReturn( false );

		$this->assertNull( $this->page->add_subscription_modal() );
	}

	public function testShouldDisplayModalWithProductionURL() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'add_query_arg' )->justReturn( 'https://wp-rocket.me/cdn/iframe?website=http://example.org&callback=http://example.org/wp-json/wp-rocket/v1/rocketcdn/');

		$expected = <<<HTML
<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
	<div class="wpr-rocketcdn-modal__overlay" tabindex="-1">
		<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true" aria-labelledby="wpr-rocketcdn-modal-title">
			<div id="wpr-rocketcdn-modal-content">
				<iframe id="rocketcdn-iframe" src="https://wp-rocket.me/cdn/iframe?website=http://example.org&callback=http://example.org/wp-json/wp-rocket/v1/rocketcdn/" width="674" height="425"></iframe>
			</div>
		</div>
	</div>
</div>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}

	private function getActualHtml() {
		ob_start();
		$this->page->add_subscription_modal();

		return $this->format_the_html( ob_get_clean() );
	}
}
