<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Frontend\Subscriber;

use ReflectionProperty;
use WP_Error;
use WP_Rocket\Dependencies\Monolog\Handler\AbstractProcessingHandler;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Integration\ASTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Frontend\Subscriber::parse_sitemap
 *
 * @group Preload
 */
class Test_ParseSitemap extends TestCase {
	use ASTrait, HttpRequestTrait;

	protected $config;

	/**
	 * Handler collecting the records written by the real logger.
	 *
	 * @var AbstractProcessingHandler
	 */
	private $log_handler;

	/**
	 * Handlers of the real logger, restored on tear down.
	 *
	 * @var array
	 */
	private $original_handlers;

	/**
	 * Cached debug status of the real logger, restored on tear down.
	 *
	 * @var bool|null
	 */
	private $original_debug_cache;

	public function set_up() {
		parent::set_up();

		$this->capture_logs();

		$this->setup_http();

		// Install the preload cache table.
		self::installPreloadCacheTable();
	}

	public function tear_down() {
		// Uninstall the preload cache table.
		self::uninstallPreloadCacheTable();

		$this->tear_down_http();

		$this->release_logs();

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {

		$this->config         = $config;
		$this->config['http'] = [ $config['sitemap_url'] => $this->sitemap_response() ];

		do_action( 'rocket_preload_job_parse_sitemap', $config['sitemap_url'] );

		$messages = array_column( $this->log_handler->records, 'message' );

		if ( null === $expected['log'] ) {
			$this->assertSame( [], $messages );
		} else {
			$this->assertSame( [ $expected['log'] ], $messages );
			$this->assertSame( 'ERROR', $this->log_handler->records[0]['level_name'] );
		}

		foreach ( $expected['children'] as $child ) {
			$this->assertEquals(
				$expected['children_exists'],
				self::taskExist( 'rocket_preload_job_parse_sitemap', [ $child ] )
			);
		}

		foreach ( $expected['links'] as $link ) {
			$exists = $expected['links_exists'] ? '' : "n't";
			$this->assertEquals(
				$expected['links_exists'],
				self::cacheFound( [ 'url' => $link ] ), "Link {$link} should$exists exist"
			);
		}
	}

	/**
	 * Builds the sitemap response from the data set.
	 *
	 * @return array|WP_Error
	 */
	private function sitemap_response() {
		if ( ! empty( $this->config['process_generate']['is_wp_error'] ) ) {
			return new WP_Error( 'error', 'error_data' );
		} else {
			$message = ! empty( $this->config['process_generate']['empty_body'] ) ? '' : $this->config['process_generate']['response'];
			$code    = $this->config['process_generate']['code'] ?? 200;
			return [
				'body'     => $message,
				'response' => [ 'code' => $code ],
			];
		}
	}

	/**
	 * Forces debug on and swaps the real logger handlers for an in-memory one.
	 *
	 * @return void
	 */
	private function capture_logs() {
		$this->log_handler = new class() extends AbstractProcessingHandler {
			/**
			 * Records received.
			 *
			 * @var array
			 */
			public $records = [];

			/**
			 * Stores the record.
			 *
			 * @param array $record Record.
			 *
			 * @return void
			 */
			protected function write( array $record ) {
				$this->records[] = $record;
			}
		};

		$cache                      = $this->debug_cache_property();
		$this->original_debug_cache = $cache->getValue();
		$cache->setValue( null, true );

		$logger                  = Logger::get_logger();
		$this->original_handlers = $logger->getHandlers();
		$logger->setHandlers( [ $this->log_handler ] );
	}

	/**
	 * Restores the real logger handlers and debug status.
	 *
	 * @return void
	 */
	private function release_logs() {
		Logger::get_logger()->setHandlers( $this->original_handlers );

		$this->debug_cache_property()->setValue( null, $this->original_debug_cache );
	}

	/**
	 * Gets the accessible reflection of the logger debug status cache.
	 *
	 * @return ReflectionProperty
	 */
	private function debug_cache_property() {
		$cache = new ReflectionProperty( Logger::class, 'debug_enabled_cache' );

		if ( PHP_VERSION_ID < 80100 ) {
			$cache->setAccessible( true );
		}

		return $cache;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'parseSitemap' );
	}
}
