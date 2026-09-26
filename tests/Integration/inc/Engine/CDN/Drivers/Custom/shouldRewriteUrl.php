<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\Custom;

use WP_Rocket\Engine\CDN\Drivers\Custom;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\Custom::should_rewrite_url
 * @group  CDN
 * @group  AdminOnly
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var Custom
	 */
	private $driver;

	/**
	 * @var array
	 */
	private $cdn_urls = [];

	public function set_up() {
		parent::set_up();

		$container    = apply_filters( 'rocket_container', null );
		$this->driver = $container->get( 'cdn_driver_byocdn' );
	}

	public function tear_down() {
		remove_filter( 'rocket_cdn_cnames', [ $this, 'filter_cdn_cnames' ] );

		parent::tear_down();
	}

	/**
	 * Injects the fixture's configured hostnames the same way CDN::get_cdn_urls()
	 * merges them: unconditionally, regardless of zone.
	 *
	 * @return array
	 */
	public function filter_cdn_cnames(): array {
		return $this->cdn_urls;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		$this->cdn_urls = $config['cdn_urls'];
		add_filter( 'rocket_cdn_cnames', [ $this, 'filter_cdn_cnames' ] );

		// cdn_driver_byocdn is a container singleton (addShared), so CdnHostnameTrait's
		// per-instance memoization would otherwise leak the first data set's hostname
		// answer into every subsequent one.
		$this->set_reflective_property( null, 'has_hostname_memo', $this->driver );
		$this->set_reflective_property( [], 'should_rewrite_memo', $this->driver );

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}
}
