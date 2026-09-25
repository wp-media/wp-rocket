<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Addon\MaxCache\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::carry_switch_state_through_the_form
 *
 * What this puts in the form decides how sanitize_option() reads an unchecked box afterwards: a
 * marker means the switch was drawn and the box is an answer, its absence means the form predates
 * the switch and the box says nothing.
 *
 * @group MaxCache
 */
class TestCarrySwitchStateThroughTheForm extends TestCase {
	/**
	 * Checks which field rides along for a given state of the settings page.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Site state for the case.
	 * @param array $expected Field expected in the list, and the one that must not be there.
	 *
	 * @return void
	 */
	public function testShouldCarryExpectedField( $config, $expected ) {
		Functions\when( 'rocket_valid_key' )->justReturn( $config['licensed'] );
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				switch ( $tag ) {
					case 'rocket_maxcache_available':
						return $config['available'];
					case 'rocket_display_input_maxcache':
						return $config['displayed'];
					default:
						return $value;
				}
			}
		);

		$subscriber = new Subscriber( Mockery::mock( MaxCache::class ), Mockery::mock( Options::class ) );

		$fields = $subscriber->carry_switch_state_through_the_form( [ 'cache_mobile' ] );

		$this->assertContains( $expected['carries'], $fields );
		$this->assertNotContains( $expected['not_carries'], $fields );
		// Whatever the plugin was already collecting stays.
		$this->assertContains( 'cache_mobile', $fields );
	}

	/**
	 * Merges a case over a site that draws the switch.
	 *
	 * @return array
	 */
	public function configTestData() {
		$cases = $this->getTestData( __DIR__, 'carrySwitchStateThroughTheForm' );

		foreach ( $cases as $name => $case ) {
			$cases[ $name ]['config'] = array_merge(
				[
					'available' => true,
					'displayed' => true,
					'licensed'  => true,
				],
				$case['config']
			);
		}

		return $cases;
	}
}
