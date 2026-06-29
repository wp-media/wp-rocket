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
class TestBackup extends BaseTestCase {

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

	public function testShouldReturnFalseOnRollback(): void {
		$this->assertFalse( $this->subject->backup( '3.21.0', '3.22.0' ) );
	}

	public function testShouldReturnFalseWhenBackupAlreadyExistsForVersion(): void {
		Functions\expect( 'glob' )
			->once()
			->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.1_*.json' )
			->andReturn( [ self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.1_2026-06-01-10-00-00.json' ] );

		$this->options->shouldNotReceive( 'get_options' );

		$this->assertFalse( $this->subject->backup( '3.22.1', '3.22.0' ) );
	}

	public function testShouldReturnFalseWhenOptionsEmpty(): void {
		Functions\expect( 'glob' )
			->once()
			->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.1_*.json' )
			->andReturn( [] );

		$this->options->shouldReceive( 'get_options' )
			->once()
			->andReturn( [] );

		Functions\expect( 'rocket_put_content' )->never();

		$this->assertFalse( $this->subject->backup( '3.22.1', '3.22.0' ) );
	}

	public function testShouldReturnFalseWhenWriteFails(): void {
		$options_array = [ 'version' => '3.22.0', 'minify_css' => 1 ];

		Functions\expect( 'glob' )
			->once()
			->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.1_*.json' )
			->andReturn( [] );

		$this->options->shouldReceive( 'get_options' )
			->once()
			->andReturn( $options_array );

		Functions\expect( 'rocket_init_config_dir' )->once();
		Functions\when( 'gmdate' )->justReturn( self::FIXED_DATE );
		Functions\when( 'wp_json_encode' )->justReturn( (string) json_encode( $options_array, JSON_PRETTY_PRINT ) );

		Functions\expect( 'rocket_put_content' )
			->once()
			->andReturn( false );

		$this->assertFalse( $this->subject->backup( '3.22.1', '3.22.0' ) );
	}

	public function testShouldReturnTrueAndSkipGcWhenUnderLimit(): void {
		$options_array = [ 'version' => '3.22.0', 'minify_css' => 1 ];

		Functions\expect( 'glob' )
			->once()
			->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.1_*.json' )
			->andReturn( [] );

		$this->options->shouldReceive( 'get_options' )
			->once()
			->andReturn( $options_array );

		Functions\expect( 'rocket_init_config_dir' )->once();
		Functions\when( 'gmdate' )->justReturn( self::FIXED_DATE );
		Functions\when( 'wp_json_encode' )->justReturn( (string) json_encode( $options_array, JSON_PRETTY_PRINT ) );

		Functions\expect( 'rocket_put_content' )
			->once()
			->andReturn( true );

		Functions\expect( 'glob' )
			->once()
			->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_*.json' )
			->andReturn( [ self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.1_' . self::FIXED_DATE . '.json' ] );

		Functions\expect( 'rocket_direct_filesystem' )->never();

		$this->assertTrue( $this->subject->backup( '3.22.1', '3.22.0' ) );
	}

	public function testShouldDeleteOldestFileWhenGcThresholdExceeded(): void {
		$options_array = [ 'version' => '3.22.3', 'minify_css' => 1 ];
		$all_files     = [
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.4_2026-06-29-10-00-04.json',
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.3_2026-06-29-10-00-03.json',
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.2_2026-06-29-10-00-02.json',
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.1_2026-06-29-10-00-01.json',
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.0_2026-06-29-10-00-00.json',
		];
		$mtimes        = [
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.4_2026-06-29-10-00-04.json' => 1751189204,
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.3_2026-06-29-10-00-03.json' => 1751189203,
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.2_2026-06-29-10-00-02.json' => 1751189202,
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.1_2026-06-29-10-00-01.json' => 1751189201,
			self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.0_2026-06-29-10-00-00.json' => 1751189200,
		];

		Functions\expect( 'glob' )
			->once()
			->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.4_*.json' )
			->andReturn( [] );

		$this->options->shouldReceive( 'get_options' )
			->once()
			->andReturn( $options_array );

		Functions\expect( 'rocket_init_config_dir' )->once();
		Functions\when( 'gmdate' )->justReturn( '2026-06-29-10-00-04' );
		Functions\when( 'wp_json_encode' )->justReturn( (string) json_encode( $options_array, JSON_PRETTY_PRINT ) );

		Functions\expect( 'rocket_put_content' )
			->once()
			->andReturn( true );

		Functions\expect( 'glob' )
			->once()
			->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_*.json' )
			->andReturn( $all_files );

		// filemtime is called repeatedly by usort — use when() alias to avoid count issues.
		Functions\when( 'filemtime' )->alias(
			function ( string $file ) use ( $mtimes ): int {
				return $mtimes[ $file ] ?? 0;
			}
		);

		$filesystem = Mockery::mock( WP_Filesystem_Direct::class );
		Functions\expect( 'rocket_direct_filesystem' )
			->once()
			->andReturn( $filesystem );

		$filesystem->shouldReceive( 'delete' )
			->once()
			->with( self::CONFIG_PATH . 'wp_rocket_settings_backup_3.22.0_2026-06-29-10-00-00.json' );

		$this->assertTrue( $this->subject->backup( '3.22.4', '3.22.3' ) );
	}
}
