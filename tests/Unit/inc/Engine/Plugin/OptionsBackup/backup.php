<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Plugin\OptionsBackup;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Plugin\OptionsBackup;
use WPMedia\PHPUnit\Unit\TestCase as BaseTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Plugin\OptionsBackup::backup
 *
 * @group Plugin
 */
class Test_Backup extends BaseTestCase {

	const CONFIG_PATH = '/wp-rocket-config/';
	const FIXED_DATE  = '2026-06-29-10-00-00';

	/**
	 * @var OptionsBackup
	 */
	private $subject;

	/**
	 * @var Options_Data|Mockery\MockInterface
	 */
	private $options;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->subject = new OptionsBackup( self::CONFIG_PATH, $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( array $config, array $expected ): void {
		$new_version   = $config['new_version'];
		$old_version   = $config['old_version'];
		$is_rollback   = version_compare( $new_version, $old_version, '<' );
		$backup_exists = false;

		if ( ! $is_rollback ) {
			$existing = $config['existing_files'] ?? [];
			Functions\expect( 'glob' )
				->once()
				->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_' . $new_version . '_*.json' )
				->andReturn( $existing );
			$backup_exists = ! empty( $existing );
		}

		if ( ! $is_rollback && ! $backup_exists ) {
			$options = is_array( $config['options'] ) ? $config['options'] : [];

			$this->options->shouldReceive( 'get_options' )
				->once()
				->andReturn( $options );

			if ( ! empty( $options ) ) {
				Functions\expect( 'rocket_init_config_dir' )->once();
				Functions\when( 'gmdate' )->justReturn( self::FIXED_DATE );
				Functions\when( 'wp_json_encode' )->justReturn( (string) json_encode( $options, JSON_PRETTY_PRINT ) );

				$write_result = $config['write_result'] ?? false;
				Functions\expect( 'rocket_put_content' )
					->once()
					->andReturn( $write_result );

				if ( $write_result ) {
					$all_backups = $config['all_backups'] ?? [];
					Functions\expect( 'glob' )
						->once()
						->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_*.json' )
						->andReturn( $all_backups );

					if ( count( $all_backups ) > OptionsBackup::KEEP_COUNT ) {
						$mtimes = $config['mtimes'] ?? [];
						Functions\when( 'filemtime' )->alias(
							function ( string $file ) use ( $mtimes ): int {
								return $mtimes[ $file ] ?? 0;
							}
						);

						$filesystem = Mockery::mock( WP_Filesystem_Direct::class );
						Functions\expect( 'rocket_direct_filesystem' )
							->once()
							->andReturn( $filesystem );

						foreach ( $config['files_to_delete'] ?? [] as $file ) {
							$filesystem->shouldReceive( 'delete' )
								->once()
								->with( $file );
						}
					} else {
						Functions\expect( 'rocket_direct_filesystem' )->never();
					}
				}
			} else {
				Functions\expect( 'rocket_put_content' )->never();
			}
		} else {
			$this->options->shouldNotReceive( 'get_options' );
		}

		$this->assertSame( $expected['result'], $this->subject->backup( $new_version, $old_version ) );
	}
}
