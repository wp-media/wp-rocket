<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CdnStateTranslator;

use Mockery;
use WP_Rocket\Engine\CDN\CdnStateTranslator;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

class Test_StateToLegacy extends TestCase {
	/**
	 * @var CdnStateTranslator
	 */
	private $translator;

	public function set_up() {
		parent::set_up();

		$this->translator = new CdnStateTranslator( Mockery::mock( SubscriptionController::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedLegacyShape( string $state, array $expected ) {
		$this->assertSame( $expected, $this->translator->state_to_legacy( $state ) );
	}
}
