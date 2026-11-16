<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering ::get_rocket_htaccess_mod_rewrite
 *
 * @group Functions
 * @group Htaccess
 */
class Test_GetRocketHtaccessModRewrite extends TestCase {
	private $cache_webp;
	private $webp_rewritecond;

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cache_webp', [ $this, 'set_cache_webp' ] );
		remove_filter( 'rocket_webp_rewritecond', [ $this, 'set_webp_rewritecond' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReferenceWebpEnvOnlyWhenSet( $config, $expected ) {
		$this->cache_webp       = $config['cache_webp'];
		$this->webp_rewritecond = $config['webp_rewritecond'];

		add_filter( 'pre_get_rocket_option_cache_webp', [ $this, 'set_cache_webp' ] );

		if ( null !== $this->webp_rewritecond ) {
			add_filter( 'rocket_webp_rewritecond', [ $this, 'set_webp_rewritecond' ] );
		}

		$rules = get_rocket_htaccess_mod_rewrite();

		foreach ( $expected['contains'] as $needle ) {
			$this->assertStringContainsString( $needle, $rules );
		}

		foreach ( $expected['not_contains'] as $needle ) {
			$this->assertStringNotContainsString( $needle, $rules );
		}
	}

	public function set_cache_webp() {
		return $this->cache_webp;
	}

	public function set_webp_rewritecond() {
		return $this->webp_rewritecond;
	}
}
