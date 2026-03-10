<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\Render;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render::get_recommendations_widget
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class GetRecommendationsWidgetTest extends TestCase {
	/**
	 * Container instance.
	 *
	 * @var mixed
	 */
	protected static $container;

	/**
	 * Render instance.
	 *
	 * @var \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render
	 */
	private $render;

	/**
	 * Transient name for recommendations.
	 *
	 * @var string
	 */
	private const TRANSIENT_NAME = 'wpr_ri_recommendations';

	/**
	 * Set up before class.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$container = apply_filters( 'rocket_container', null );
	}

	/**
	 * Set up before each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Clear transient before each test.
		delete_transient( self::TRANSIENT_NAME );

		// Enable Rocket Insights for the test.
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Get the Render instance from the container.
		$this->render = self::$container->get( 'ri_recommendations_render' );
	}

	/**
	 * Tear down after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		// Clear transient after each test.
		delete_transient( self::TRANSIENT_NAME );

		// Remove Rocket Insights enabled filter.
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		parent::tear_down();
	}

	/**
	 * Test that the recommendations widget renders the correct state.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected values.
	 * @return void
	 */
	public function testShouldRenderCorrectState( $config, $expected ) {
		$this->setUpTest( $config );

		// Get the widget HTML.
		$output = $this->render->get_recommendations_widget();

		$this->assertOutput( $output, $expected );
	}

	/**
	 * Set up the test environment based on config.
	 *
	 * @param array $config Test configuration.
	 * @return void
	 */
	private function setUpTest( array $config ): void {
		// Set up transient data if provided.
		if ( isset( $config['transient_data'] ) ) {
			set_transient( self::TRANSIENT_NAME, $config['transient_data'], DAY_IN_SECONDS );
		}
	}

	/**
	 * Assert the output matches expected values.
	 *
	 * @param string $output   The rendered HTML output.
	 * @param array  $expected Expected values.
	 * @return void
	 */
	private function assertOutput( string $output, array $expected ): void {
		// Check the data-state attribute on the container.
		if ( isset( $expected['state'] ) ) {
			$this->assertStringContainsString(
				'data-state="' . $expected['state'] . '"',
				$output,
				"Expected data-state='{$expected['state']}' attribute not found in output."
			);
		}

		// Check for specific strings that should be present.
		if ( isset( $expected['contains'] ) ) {
			foreach ( $expected['contains'] as $string ) {
				$this->assertStringContainsString(
					$string,
					$output,
					"Expected string '{$string}' not found in output."
				);
			}
		}

		// Check for specific strings that should NOT be present.
		if ( isset( $expected['not_contains'] ) ) {
			foreach ( $expected['not_contains'] as $string ) {
				$this->assertStringNotContainsString(
					$string,
					$output,
					"Unexpected string '{$string}' found in output."
				);
			}
		}

		// Check recommendation count by counting the item container divs.
		if ( isset( $expected['recommendation_count'] ) ) {
			// Count actual item containers (class="wpr-recommendation-item" at start of element).
			$count = preg_match_all( '/class="wpr-recommendation-item"/', $output );
			$this->assertSame(
				$expected['recommendation_count'],
				$count,
				"Expected {$expected['recommendation_count']} recommendations, found {$count}."
			);
		}

		// Check for Load More button.
		if ( isset( $expected['show_load_more'] ) ) {
			if ( $expected['show_load_more'] ) {
				$this->assertStringContainsString(
					'wpr-recommendations__load-more',
					$output,
					'Expected Load More button not found.'
				);
			} else {
				$this->assertStringNotContainsString(
					'wpr-recommendations__load-more',
					$output,
					'Unexpected Load More button found.'
				);
			}
		}

		// Check impact tags.
		if ( isset( $expected['impact_tags'] ) ) {
			foreach ( $expected['impact_tags'] as $tag ) {
				$this->assertStringContainsString(
					$tag,
					$output,
					"Expected impact tag '{$tag}' not found in output."
				);
			}
		}
	}
}
