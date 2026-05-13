<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\PreconnectExternalDomains\Context\Context;

use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group PreconnectExternalDomains
 */
class Test_IsAllowed extends TestCase {
	private $context;

	private $options;

	public function set_up() {
		parent::set_up();
		$this->options = Mockery::mock( Options_Data::class );

		$this->context = new Context( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Filters\expectApplied( 'rocket_preconnect_external_domains_optimization' )
			->andReturn( $config['filter'] );

		$this->assertSame(
			$expected,
			$this->context->is_allowed()
		);
	}
}
