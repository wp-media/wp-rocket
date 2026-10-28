<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Meta;

use Brain\Monkey\{Filters, Functions};
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Support\Meta;
use WP_Rocket\Dependencies\Detection\MobileDetect;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Meta::add_meta_generator
 *
 * @group Support
 */
class TestAddMetaGenerator extends TestCase {
	private $meta;
	private $options;
	private $mobile_detect;
	private $context;

	public function set_up() {
		parent::set_up();

		$this->options       = Mockery::mock( Options_Data::class );
		$this->mobile_detect = Mockery::mock( MobileDetect::class );
		$this->context       = Mockery::mock( Context::class );
		$this->meta          = new Meta( $this->mobile_detect, $this->options, $this->context );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		Functions\when( 'rocket_bypass' )->alias( function() use ( $config ) {
			return isset( $config['nowprocket'] ) ? (bool) $config['nowprocket'] : false;
		} );

		$this->white_label_footprint = $config['white_label_footprint'] ?? null;
		$this->donotrocketoptimize = $config['donotrocketoptimize'] ?? null;
		$this->rocket_version = '3.17';

		if ( isset( $config['disable_meta'] ) ) {
			Filters\expectApplied( 'rocket_disable_meta_generator' )
				->once()
				->andReturn( $config['disable_meta'] );
		}

		if ( isset( $config['display_version'] ) ) {
			Filters\expectApplied( 'rocket_display_meta_generator_content_version' )
				->andReturn( $config['display_version'] );
		}

		if ( ! empty( $config['context_not_called'] ) ) {
			$this->context->shouldNotReceive( 'is_schedule_allowed' );
			$this->context->shouldNotReceive( 'is_reseller_or_non_live' );
		} else {
			$schedule_allowed = $config['insights_schedule_allowed'] ?? false;

			$this->context->shouldReceive( 'is_schedule_allowed' )
				->andReturn( $schedule_allowed );

			if ( $schedule_allowed ) {
				$this->context->shouldReceive( 'is_reseller_or_non_live' )
					->andReturn( $config['insights_non_live'] ?? false );
			} else {
				$this->context->shouldNotReceive( 'is_reseller_or_non_live' );
			}
		}

		Functions\when( 'is_user_logged_in' )->justReturn( $config['is_user_logged_in'] ?? false );

		if ( isset( $config['do_caching_mobile_files'] ) ) {
			$this->options->shouldReceive( 'get' )
			->with( 'do_caching_mobile_files', 0 )
			->andReturn( $config['do_caching_mobile_files'] );
		}

		if ( isset( $config['cdn'] ) ) {
			$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( $config['cdn'] );
		}

		Functions\when( 'rocket_get_dns_prefetch_domains' )->justReturn( [] );

		if ( isset( $config['preload_links'] ) ) {
			$this->options->shouldReceive( 'get' )
			->with( 'preload_links', 0 )
			->andReturn( $config['preload_links'] );
		}

		if ( isset( $config['host_fonts_locally'] ) ) {
			$this->options->shouldReceive( 'get' )
			->with( 'host_fonts_locally', 0 )
			->andReturn( $config['host_fonts_locally'] );
		}

		$this->mobile_detect->shouldReceive( 'isMobile' )
			->andReturn( $config['is_mobile'] ?? false );

		$this->assertSame(
			$expected,
			$this->meta->add_meta_generator( $html )
		);
	}
}
