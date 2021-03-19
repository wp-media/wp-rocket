<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Process;

use Brain\Monkey\Filters;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Preload\PartialProcess;

/**
 * @covers \WP_Rocket\Engine\Preload\AbstractProcess::get_mobile_user_agent_prefix
 * @group Preload
 */
class Test_GetMobileUserAgentPrefix extends TestCase {
	private $process;
	private $prefix = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

	public function setUp() : void {
		parent::setUp();

		$this->process = new PartialProcess();
	}

	public function testShouldReturnMobilePrefixWhenNotFiltered() {
		$this->assertSame( $this->prefix, $this->process->get_mobile_user_agent_prefix() );
	}

	public function testShouldReturnMobilePrefixWhenFilteredWithWrongValue() {
		Filters\expectApplied( 'rocket_mobile_preload_user_agent_prefix' )
			->andReturn( '' ); // Simulate a filter.

		$this->assertSame( $this->prefix, $this->process->get_mobile_user_agent_prefix() );

		Filters\expectApplied( 'rocket_mobile_preload_user_agent_prefix' )
			->andReturn( [ 'ho ho ho' ] ); // Simulate a filter.

		$this->assertSame( $this->prefix, $this->process->get_mobile_user_agent_prefix() );
	}

	public function testShouldNotReturnMobilePrefixWhenFilteredWithValidValue() {
		Filters\expectApplied( 'rocket_mobile_preload_user_agent_prefix' )
			->andReturn( 'Internet Explorer' ); // Simulate a filter.

		$this->assertSame( 'Internet Explorer', $this->process->get_mobile_user_agent_prefix() );
	}
}
