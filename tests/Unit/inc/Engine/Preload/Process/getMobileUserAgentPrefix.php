<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\AbstractProcess;

use Brain\Monkey\Filters;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Preload\AbstractProcess;

/**
 * @covers \WP_Rocket\Engine\Preload\AbstractProcess::get_mobile_user_agent_prefix
 * @group Preload
 */
class Test_GetMobileUserAgentPrefix extends TestCase {
	private $prefix = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

	public function testShouldReturnMobilePrefixWhenNotFiltered() {
		$stub = $this->getMockForAbstractClass( AbstractProcess::class );

		$this->assertSame( $this->prefix, $stub->get_mobile_user_agent_prefix() );
	}

	public function testShouldReturnMobilePrefixWhenFilteredWithWrongValue() {
		$stub = $this->getMockForAbstractClass( AbstractProcess::class );

		Filters\expectApplied( 'rocket_mobile_preload_user_agent_prefix' )
			->andReturn( '' ); // Simulate a filter.

		$this->assertSame( $this->prefix, $stub->get_mobile_user_agent_prefix() );

		Filters\expectApplied( 'rocket_mobile_preload_user_agent_prefix' )
			->andReturn( [ 'ho ho ho' ] ); // Simulate a filter.

		$this->assertSame( $this->prefix, $stub->get_mobile_user_agent_prefix() );
	}

	public function testShouldNotReturnMobilePrefixWhenFilteredWithValidValue() {
		$stub = $this->getMockForAbstractClass( AbstractProcess::class );

		Filters\expectApplied( 'rocket_mobile_preload_user_agent_prefix' )
			->andReturn( 'Internet Explorer' ); // Simulate a filter.

		$this->assertSame( 'Internet Explorer', $stub->get_mobile_user_agent_prefix() );
	}
}
