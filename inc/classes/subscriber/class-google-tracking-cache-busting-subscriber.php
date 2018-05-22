<?php
namespace WP_Rocket\Subscriber;

use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Admin\Options_Data as Options;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Event subscriber for Google tracking cache busting
 *
 * @since 3.1
 * @author Remy Perona
 */
class Google_Tracking_Cache_Busting_Subscriber {
	/**
	 * Instance of the Busting Factory class
	 *
	 * @var Busting_Factory
	 */
	private $busting_factory;

	/**
	 * Instance of the HtmlPageCrawler class
	 *
	 * @var HtmlPageCrawler
	 */
	private $crawler;

	/**
	 * Instance of the Option_Data class
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Busting_Factory $busting_factory Instance of the Busting Factory class.
	 * @param HtmlPageCrawler $crawler Instance of the HtmlPageCrawler class.
	 * @param Options         $options Instance of the Option_Data class.
	 */
	public function __construct( Busting_Factory $busting_factory, HtmlPageCrawler $crawler, Options $options ) {
		$this->busting_factory = $busting_factory;
		$this->crawler         = $crawler;
		$this->options         = $options;
	}

	/**
	 * Custom constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Busting_Factory $busting_factory Instance of the Busting Factory class.
	 * @param HtmlPageCrawler $crawler Instance of the HtmlPageCrawler class.
	 * @param Options         $options Instance of the Option_Data class.
	 * @return void
	 */
	public static function init( Busting_Factory $busting_factory, HtmlPageCrawler $crawler, Options $options ) {
		$self = new self( $busting_factory, $crawler, $options );

		add_filter( 'rocket_buffer', [ $self, 'cache_busting_google_tracking' ] );
		add_action( 'init', [ $self, 'schedule_tracking_cache_update' ] );
		add_action( 'rocket_google_tracking_cache_update', [ $self, 'update_tracking_cache' ] );
		add_filter( 'cron_schedules', [ $self, 'rocket_purge_cron_schedule' ] );
	}

	/**
	 * Checks if the cache busting should happen
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Processes the cache busting on the HTML content
	 *
	 * Google Analytics replacement is performed first, and if no replacement occured, Google Tag Manager replacement is performed.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function cache_busting_google_tracking( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		$processor = $this->busting_factory->type( 'ga' );
		$crawler   = $this->crawler;
		$crawler   = $crawler::create( $html );
		$html      = $processor->replace_url( $crawler );

		if ( $processor->is_replaced() ) {
			return $html;
		}

		$processor = $this->busting_factory->type( 'gtm' );

		return $processor->replace_url( $crawler );
	}

	/**
	 * Schedules the auto-update of Google Analytics cache busting file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function schedule_tracking_cache_update() {
		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return;
		}

		if ( ! wp_next_scheduled( 'rocket_google_tracking_cache_update' ) ) {
			wp_schedule_event( time(), 'weekly', 'rocket_google_tracking_cache_update' );
		}
	}

	/**
	 * Updates Google Analytics cache busting file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function update_tracking_cache() {
		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return false;
		}

		$processor = $this->busting_factory->type( 'ga' );

		return $processor->save( 'https://www.google-analytics.com/analytics.js', 'ga-local' );
	}

	/**
	 * Adds weekly interval to cron schedules
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Array $schedules An array of intervals used by cron jobs.
	 * @return Array
	 */
	public function add_schedule( $schedules ) {
		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return;
		}

		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'weekly', 'rocket' ),
		);

		return $schedules;
	}
}
