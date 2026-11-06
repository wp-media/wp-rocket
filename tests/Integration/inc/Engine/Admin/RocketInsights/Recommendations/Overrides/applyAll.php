<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\Overrides;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Overrides::apply_all
 *
 * @group RocketInsights
 * @group RocketCDN
 * @group Recommendations
 */
class Test_ApplyAll extends TestCase {

	/**
	 * Test that the container composes the RocketCDN override into the registry.
	 */
	public function testShouldApplyRocketCdnOverrideWhenResolvedFromContainer() {
		add_filter( 'pre_get_rocket_option_cdn_state', [ $this, 'return_free_state' ] );

		$container = apply_filters( 'rocket_container', null );
		$result    = $container->get( 'ri_recommendation_overrides' )->apply_all(
			[
				[
					'option_slug' => 'plugin_rocketcdn',
					'title'       => 'API title',
				],
			]
		);

		remove_filter( 'pre_get_rocket_option_cdn_state', [ $this, 'return_free_state' ] );

		$this->assertSame( 'Upgrade to RocketCDN Pro', $result[0]['title'] );
		$this->assertSame( 'Upgrade', $result[0]['cta_label'] );
		$this->assertSame( 'wpr-rocketcdn-open', $result[0]['cta_class'] );
	}

	/**
	 * Returns the RocketCDN Free state.
	 *
	 * @return string
	 */
	public function return_free_state() {
		return 'rocketcdn_free';
	}
}
