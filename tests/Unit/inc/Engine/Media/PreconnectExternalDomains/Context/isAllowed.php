<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\PreconnectExternalDomains\Context\Context;

use Brain\Monkey\{Filters, Functions};
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
		$this->options->expects()->get( 'wp_rocket_no_licence', 0 )->andReturn($config['licence']);

		Filters\expectApplied( 'rocket_preconnect_external_domains_optimization' )
			->andReturn( $config['filter'] );

		$this->assertSame(
			$expected,
			$this->context->is_allowed()
		);
	}
}
