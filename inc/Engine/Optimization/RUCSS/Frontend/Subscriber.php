<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Frontend;

use WP_Rocket\Logger\Logger;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

class Subscriber implements Subscriber_Interface {
	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * APIClient instance
	 *
	 * @var APIClient
	 */
	private $api;

	/**
	 * UsedCss instance
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Name of the cron.
	 *
	 * @var string
	 */
	const CRON_NAME = 'rocket_rucss_retries_cron';

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options  Plugin options instance.
	 * @param UsedCSS      $used_css UsedCSS instance.
	 * @param APIClient    $api      APIClient instance.
	 */
	public function __construct( Options_Data $options, UsedCSS $used_css, APIClient $api ) {
		$this->options  = $options;
		$this->used_css = $used_css;
		$this->api      = $api;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_buffer' => [ 'treeshake', 12 ],
			self::CRON_NAME => 'rucss_retries',
		];
	}

	/**
	 * Apply TreeShaked CSS to the current HTML page.
	 *
	 * @param string $html  HTML content.
	 *
	 * @return string  HTML content.
	 */
	public function treeshake( string $html ) : string {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		global $wp;
		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->is_mobile();
		$used_css  = $this->used_css->get_used_css( $url, $is_mobile );

		$this->used_css->set_cpcss_enabled( (bool) $this->options->get( 'async_css', 0 ) );

		if ( empty( $used_css ) || ( $used_css->retries < 3 ) ) {
			$config = [
				'treeshake'      => 1,
				'wpr_email'      => $this->options->get( 'consumer_email', '' ),
				'wpr_key'        => $this->options->get( 'consumer_key', '' ),
				'rucss_safelist' => $this->options->get( 'remove_unused_css_safelist', [] ),
			];

			$treeshaked_result = $this->api->optimize( $html, $url, $config );

			if ( 200 !== $treeshaked_result['code'] ) {
				Logger::error(
					'Error when contacting the RUCSS API.',
					[
						'rucss error',
						'url'     => $url,
						'code'    => $treeshaked_result['code'],
						'message' => $treeshaked_result['message'],
					]
				);
				return $html;
			}

			$retries = 0;
			if ( isset( $used_css->retries ) ) {
				$retries = $used_css->retries;
			}

			if ( ! empty( $treeshaked_result['unprocessed_css'] ) ) {
				$this->schedule_rucss_retry();
			}

			$data = [
				'url'            => $url,
				'css'            => $treeshaked_result['css'],
				'unprocessedcss' => wp_json_encode( $treeshaked_result['unprocessed_css'] ),
				'retries'        => empty( $treeshaked_result['unprocessed_css'] ) ? 3 : $retries + 1,
				'is_mobile'      => $is_mobile,
				'modified'       => current_time( 'mysql', true ),
			];

			$used_css = $this->used_css->save_or_update_used_css( $data );

			if ( ! $used_css ) {
				return $html;
			}
		}

		$html = $this->used_css->remove_used_css_from_html( $html, $used_css->unprocessedcss );

		$html = $this->used_css->add_used_css_to_html( $html, $used_css->css, $used_css->url, (bool) $used_css->is_mobile );

		$this->used_css->update_last_accessed( (int) $used_css->id );

		return $html;
	}

	/**
	 * Schedules RUCSS to retry pages with missing CSS files.
	 * Retries happen after 30 minutes.
	 *
	 * @return void
	 */
	public function schedule_rucss_retry() {
		$scheduled = wp_next_scheduled( self::CRON_NAME );

		if ( $scheduled ) {
			return;
		}

		wp_schedule_single_event( time() + ( 0.5 * HOUR_IN_SECONDS ), self::CRON_NAME );
	}

	/**
	 * Retries to regenerate the used css.
	 *
	 * @return void
	 */
	public function rucss_retries() {
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		$this->used_css->retries_pages_with_unprocessed_css();
	}

	/**
	 * Determines if we treeshake the CSS.
	 *
	 * @return boolean
	 */
	public function is_allowed() : bool {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( rocket_bypass() ) {
			return false;
		}

		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return false;
		}

		// Bailout if user is logged in and cache for logged in customers is active.
		if ( is_user_logged_in() && (bool) $this->options->get( 'cache_logged_user', 0 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return boolean
	 */
	public function is_mobile() : bool {
		return (bool) $this->options->get( 'cache_mobile', 0 ) &&
				(bool) $this->options->get( 'do_caching_mobile_files', 0 ) &&
				wp_is_mobile();
	}
}
