<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Addon\MaxCache\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::get_status
 *
 * @group MaxCache
 */
class TestGetStatus extends TestCase {
	/**
	 * Checks the line the Add-ons tab shows for a given state.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Site and plugin state for the case.
	 * @param array $expected Fragments the line must and must not carry.
	 *
	 * @return void
	 */
	public function testShouldReportExpectedStatus( $config, $expected ) {
		Functions\when( '__' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				return 'rocket_disable_htaccess' === $tag ? $config['writing_refused'] : $value;
			}
		);

		$maxcache = Mockery::mock( MaxCache::class );
		$maxcache->shouldReceive( 'options' )->andReturn(
			new Options_Data(
				[
					'maxcache'          => $config['option'],
					'cache_logged_user' => $config['cache_logged_user'],
				]
			)
		);
		$maxcache->shouldReceive( 'has_directives' )->andReturn( $config['directives'] );
		$maxcache->shouldReceive( 'is_enabled' )->andReturn( $config['enabled'] );
		$maxcache->shouldReceive( 'get_unsupported_reason' )->andReturn( $config['reason'] );
		$maxcache->shouldReceive( 'serves_logged_in_users' )->andReturn( $config['serves_logged_in'] );

		$subscriber = new Subscriber( $maxcache, Mockery::mock( Options::class ) );

		$status = $subscriber->get_status( 'the plugin says nothing about this add-on' );

		foreach ( $expected['contains'] as $fragment ) {
			$this->assertStringContainsString( $fragment, $status );
		}

		foreach ( $expected['not_contains'] as $fragment ) {
			$this->assertStringNotContainsString( $fragment, $status );
		}
	}

	/**
	 * Merges each case over a state the add-on reports nothing about.
	 *
	 * @return array
	 */
	public function configTestData() {
		$cases = $this->getTestData( __DIR__, 'getStatus' );

		foreach ( $cases as $name => $case ) {
			$cases[ $name ]['config'] = array_merge(
				[
					'option'            => 1,
					'enabled'           => true,
					'directives'        => true,
					'reason'            => '',
					'writing_refused'   => false,
					'cache_logged_user' => 0,
					'serves_logged_in'  => true,
				],
				$case['config']
			);
		}

		return $cases;
	}
}
