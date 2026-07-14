<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_reseller_limit_banner
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_reseller_limit_banner
 * @group  RocketCDN
 * @group  RocketCDNNotices
 */
class Test_DisplayResellerLimitBanner extends TestCase {

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var Mockery\MockInterface|NoticesSubscriber
	 */
	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		Functions\stubTranslationFunctions();

		$api_client              = Mockery::mock( APIClient::class );
		$beacon                  = Mockery::mock( Beacon::class );
		$user_client             = Mockery::mock( UserClient::class );
		$tracking                = Mockery::mock( Tracking::class );
		$options                 = Mockery::mock( Options_Data::class );
		$subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user              = Mockery::mock( User::class );

		$this->subscriber = Mockery::mock(
			NoticesSubscriber::class . '[generate]',
			[
				$api_client,
				$beacon,
				$user_client,
				$tracking,
				'',
				$options,
				$subscription_controller,
				$this->user,
			]
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldOnlyRenderWhenResellerAndLimitReached( $config, $expected ): void {
		$this->user->shouldReceive( 'is_reseller_account' )
			->andReturn( $config['is_reseller'] );

		if ( null === $expected ) {
			$this->subscriber->shouldNotReceive( 'generate' );
		} else {
			$this->subscriber->shouldReceive( 'generate' )
				->once()
				->with( $expected['template'], Mockery::type( 'array' ) )
				->andReturnUsing( function ( $template, $data ) use ( $expected ) {
					$this->assertSame( $expected['template'], $template );
					$this->assertArrayHasKey( 'heading', $data );
					$this->assertArrayHasKey( 'description', $data );
					$this->assertArrayHasKey( 'is_hidden', $data );
					// esc_html__() encodes apostrophes as &#039; in test stubs; strip for comparison.
					$this->assertStringContainsString(
						str_replace( "'", '', $expected['heading'] ),
						str_replace( '&#039;', '', $data['heading'] )
					);
					$this->assertStringContainsString(
						str_replace( "'", '', $expected['description'] ),
						str_replace( '&#039;', '', $data['description'] )
					);
					$this->assertSame( $expected['is_hidden'], $data['is_hidden'] );
					return '';
				} );
		}

		$this->subscriber->display_reseller_limit_banner( $config['cta_data'] );
	}
}
