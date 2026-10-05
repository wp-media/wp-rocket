<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Admin\AdapterNotice;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Abilities\Admin\AdapterNotice;
use WP_Rocket\Engine\Abilities\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Admin\AdapterNotice::display()
 *
 * @group Abilities
 */
class DisplayTest extends TestCase {
	/**
	 * Checks the notice is rendered only when eligible.
	 *
	 * @dataProvider configTestData
	 *
	 * @param bool $should_display Whether the notice should be displayed.
	 */
	public function testShouldDisplayExpected( bool $should_display ): void {
		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\when( 'admin_url' )->alias(
			static function ( $path ) {
				return 'http://example.org/wp-admin/' . $path;
			}
		);

		$notice = Mockery::mock( AdapterNotice::class . '[should_display]', [ Mockery::mock( Context::class ) ] );
		$notice->shouldReceive( 'should_display' )->andReturn( $should_display );

		if ( $should_display ) {
			Functions\expect( 'rocket_notice_html' )
				->once()
				->with(
					Mockery::on(
						static function ( $args ) {
							return 'info' === $args['status']
								&& 'mcp_adapter_notice' === $args['dismiss_button']
								&& false !== strpos( $args['message'], 'http://example.org/wp-admin/plugin-install.php?s=mcp-adapter&tab=search&type=term' );
						}
					)
				);
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$notice->display();
	}
}
