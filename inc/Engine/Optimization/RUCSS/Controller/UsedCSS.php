<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Logger\Logger;

class UsedCSS {
	use RegexTrait, CSSTrait;

	/**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	private $used_css_query;

	/**
	 * Resources Query instance.
	 *
	 * @var ResourcesQuery
	 */
	private $resources_query;

	/**
	 * Purge instance
	 *
	 * @var Purge
	 */
	private $purge;

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
	 * Queue instance.
	 *
	 * @var QueueInterface
	 */
	private $queue;

	/**
	 * Inline exclusions regexes not to removed from the page after treeshaking.
	 *
	 * @var string[]
	 */
	private $inline_exclusions = [
		'rocket-lazyload-inline-css',
	];

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data   $options         Options instance.
	 * @param UsedCSS_Query  $used_css_query  Usedcss Query instance.
	 * @param ResourcesQuery $resources_query Resources Query instance.
	 * @param Purge          $purge           Purge instance.
	 * @param APIClient      $api             Apiclient instance.
	 */
	public function __construct(
		Options_Data $options,
		UsedCSS_Query $used_css_query,
		ResourcesQuery $resources_query,
		Purge $purge,
		APIClient $api,
		QueueInterface $queue
	) {
		$this->options         = $options;
		$this->used_css_query  = $used_css_query;
		$this->resources_query = $resources_query;
		$this->purge           = $purge;
		$this->api             = $api;
		$this->queue           = $queue;
	}

	/**
	 * Determines if we treeshake the CSS.
	 *
	 * @return boolean
	 */
	public function is_allowed(): bool {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( rocket_bypass() ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'remove_unused_css' ) ) {
			return false;
		}

		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return false;
		}

		// Bailout if user is logged in
		if ( is_user_logged_in() ) {
			return false;
		}

		return true;
	}

	/**
	 * Can optimize? used inside the CRON so post object isn't there.
	 *
	 * @return bool
	 */
	private function can_optimize() {
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return false;
		}

		return true;
	}

	private function can_optimize_url() {
		if ( rocket_bypass() ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'remove_unused_css' ) ) {
			return false;
		}

		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Start treeshaking the current page.
	 *
	 * @param string $html Buffet HTML for current page.
	 *
	 * @return string
	 */
	public function treeshake( string $html ): string {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		global $wp;
		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->is_mobile();
		$used_css  = $this->used_css_query->get_css( $url, $is_mobile );

		if ( empty( $used_css ) ) {
			$row_id = (int) $this->used_css_query->create_new_job( $url, $is_mobile );

			// Send the request to add this url into the queue and get the jobId and queueName.

			/**
			 * Filters the RUCSS safelist
			 *
			 * @since 3.11
			 *
			 * @param array $safelist Array of safelist values.
			 */
			$safelist = apply_filters( 'rocket_rucss_safelist', $this->options->get( 'remove_unused_css_safelist', [] ) );

			$config = [
				'treeshake'      => 1,
				'rucss_safelist' => $safelist,
				'is_mobile'      => $is_mobile,
				'is_home'        => $this->is_home( $url ),
			];

			$add_to_queue_response = $this->api->add_to_queue( $url, $config );
			if ( 200 !== $add_to_queue_response['code'] ) {
				Logger::error(
					'Error when contacting the RUCSS API.',
					[
						'rucss error',
						'url'     => $url,
						'code'    => $add_to_queue_response['code'],
						'message' => $add_to_queue_response['message'],
					]
				);

				if ( $row_id ) {
					$this->used_css_query->make_status_failed( $row_id );
				}

				return $html;
			}

			//We got jobid and queue name so save them into the DB and change status to be pending
			$this->used_css_query->make_status_pending( $row_id, $add_to_queue_response['contents']['jobId'], $add_to_queue_response['contents']['queueName'] );

			return $html;
		}

		if ( 'completed' !== $used_css->status || empty( $used_css->css ) ) {
			return $html;
		}

		$html = $this->remove_used_css_from_html( $html );
		$html = $this->add_used_css_to_html( $html, $used_css );

		$this->used_css_query->update_last_accessed( (int) $used_css->id );

		return $html;
	}

	/**
	 * Get job status based on URL.
	 *
	 * @param string $url Url to get job status for.
	 *
	 * @return string
	 */
	public function get_job_status( string $url ): string {
		$used_css = $this->used_css_query->get_item_by( 'url', $url );

		if ( empty( $used_css ) ) {
			return '';
		}

		return $used_css->status;
	}

	/**
	 * Delete used css based on URL.
	 *
	 * @param string $url The page URL.
	 *
	 * @return boolean
	 */
	public function delete_used_css( string $url ): bool {
		$used_css_arr = $this->used_css_query->query( [ 'url' => $url ] );

		if ( empty( $used_css_arr ) ) {
			return false;
		}

		$deleted = true;

		foreach ( $used_css_arr as $used_css ) {
			if ( empty( $used_css->id ) ) {
				continue;
			}

			$deleted = $deleted && $this->used_css_query->delete_item( $used_css->id );
		}

		return $deleted;
	}

	/**
	 * Alter HTML and remove all CSS which was processed from HTML page.
	 *
	 * @param string $html            HTML content.
	 *
	 * @return string HTML content.
	 */
	private function remove_used_css_from_html( string $html ): string {
		$clean_html = $this->hide_comments( $html );
		$clean_html = $this->hide_noscripts( $clean_html );
		$clean_html = $this->hide_scripts( $clean_html );

		$link_styles = $this->find(
			'<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>',
			$clean_html,
			'Uis'
		);

		$inline_styles = $this->find(
			'<style(?<atts>.*)>(?<content>.*)<\/style\s*>',
			$clean_html
		);

		foreach ( $link_styles as $style ) {
			if (
				! (bool) preg_match( '/rel=[\'"]stylesheet[\'"]/is', $style[0] )
				||
				strstr( $style['url'], '//fonts.googleapis.com/css' )
			) {
				continue;
			}
			$html = str_replace( $style[0], '', $html );
		}

		$inline_exclusions = (array) array_map(
			function ( $item ) {
				return preg_quote( $item, '/' );
			},
			$this->inline_exclusions
		);

		foreach ( $inline_styles as $style ) {
			if ( ! empty( $inline_exclusions ) && $this->find( implode( '|', $inline_exclusions ), $style['atts'] ) ) {
				continue;
			}

			$html = str_replace( $style[0], '', $html );
		}

		return $html;
	}

	/**
	 * Alter HTML string and add the used CSS style in <head> tag,
	 *
	 * @param string      $html     HTML content.
	 * @param UsedCSS_Row $used_css Used CSS DB row.
	 *
	 * @return string HTML content.
	 */
	private function add_used_css_to_html( string $html, UsedCSS_Row $used_css ): string {
		$replace = preg_replace(
			'#</title>#iU',
			'</title>' . $this->get_used_css_markup( $used_css ),
			$html,
			1
		);

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Hides <noscript> blocks from the HTML to be parsed.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	private function hide_noscripts( string $html ): string {
		$replace = preg_replace( '#<noscript[^>]*>.*?<\/noscript\s*>#mis', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Hides unwanted blocks from the HTML to be parsed.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	private function hide_comments( string $html ): string {
		$replace = preg_replace( '#<!--\s*noptimize\s*-->.*?<!--\s*/\s*noptimize\s*-->#is', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		$replace = preg_replace( '/<!--(.*)-->/Uis', '', $replace );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Hides scripts from the HTML to be parsed when removing CSS from it
	 *
	 * @since 3.10.2
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	private function hide_scripts( string $html ): string {
		$replace = preg_replace( '#<script[^>]*>.*?<\/script\s*>#mis', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Return Markup for used_css into the page.
	 *
	 * @param UsedCSS_Row $used_css Used CSS DB Row.
	 *
	 * @return string
	 */
	private function get_used_css_markup( UsedCSS_Row $used_css ): string {
		$used_css_contents = $this->handle_charsets( $used_css->css, false );
		return sprintf(
			'<style id="wpr-usedcss">%s</style>',
			$used_css_contents
		);
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return boolean
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&&
			$this->options->get( 'do_caching_mobile_files', 0 )
			&&
			wp_is_mobile();
	}

	/**
	 * Check if current page is the home page.
	 *
	 * @param string $url Current page url.
	 *
	 * @return bool
	 */
	private function is_home( string $url ): bool {
		return untrailingslashit( $url ) === untrailingslashit( home_url() );
	}

	/**
	 * Process pending jobs inside CRON iteration.
	 *
	 * @return void
	 */
	public function process_pending_jobs() {
		Logger::debug( 'RUCSS: Start processing pending jobs inside CRON.' );

		if ( ! $this->can_optimize() ) {
			Logger::debug( 'RUCSS: Stop processing CRON iteration because option is disabled.' );

			return;
		}

		// Get some items from the DB with status=pending & job_id isn't empty.

		/**
		 * Filters the pending jobs count.
		 *
		 * @since 3.11
		 *
		 * @param int $rows Number of rows to grab with each CRON iteration.
		 */
		$rows = apply_filters( 'rocket_rucss_pending_jobs_cron_rows_count', 100 );

		Logger::debug( "RUCSS: Start getting number of {$rows} pending jobs." );

		$pending_jobs = $this->used_css_query->get_pending_jobs( $rows );
		if ( ! $pending_jobs ) {
			Logger::debug( 'RUCSS: No pending jobs are there.' );

			return;
		}

		foreach ( $pending_jobs as $used_css_row ) {
			Logger::debug( "RUCSS: Send the job for url {$used_css_row->url} to Async task to check its job status." );

			// Change status to in-progress.
			$this->used_css_query->make_status_inprogress( (int) $used_css_row->id );

			$this->queue->add_job_status_check_async( (int) $used_css_row->id );
		}
	}

	/**
	 * Check job status by DB row ID.
	 *
	 * @param int $id DB Row ID.
	 *
	 * @return void
	 */
	public function check_job_status( int $id ) {
		Logger::debug( 'RUCSS: Start checking job status for row ID: ' . $id );

		$row_details = $this->used_css_query->get_item( $id );
		if ( ! $row_details ) {
			Logger::debug( 'RUCSS: Row ID not found ', compact( 'id' ) );

			// Nothing in DB, bailout.
			return;
		}

		// Send the request to get the job status from SaaS.
		$job_details = $this->api->get_queue_job_status( $row_details->job_id, $row_details->queue_name );
		if (
			200 !== $job_details['code']
			||
			empty( $job_details['contents'] )
			||
			empty( $job_details['contents']['shakedCSS'] )
		) {
			Logger::debug( 'RUCSS: Job status failed for url: ' . $row_details->url, $job_details );

			// Failure, check the retries number.
			if ( $row_details->retries >= 3 ) {
				Logger::debug( 'RUCSS: Job failed 3 times for url: ' . $row_details->url );

				$this->used_css_query->make_status_failed( $id );

				return;
			}

			// Increment the retries number with 1 and Change status to pending again.
			$this->used_css_query->increment_retries( $id, $row_details->retries );
			//@Todo: Maybe we can add this row to the async job to get the status before the next cron

			return;
		}

		//Everything is fine, save the usedcss into DB, change status to completed and reset queue_name and job_id.
		Logger::debug( 'RUCSS: Save used CSS for url: ' . $row_details->url );

		$css = $this->apply_font_display_swap( $job_details['contents']['shakedCSS'] );

		/**
		 * Filters Used CSS content before saving into DB.
		 *
		 * @since 3.9.0.2
		 *
		 * @param string $usedcss Used CSS.
		 */
		$css = apply_filters( 'rocket_usedcss_content', $css );

		$this->used_css_query->make_status_completed( $id, $css );

		do_action( 'rucss_complete_job_status', $row_details->url, $job_details );

	}

	public function add_clear_usedcss_bar_item( $wp_admin_bar ) {
		if ( ! current_user_can( 'rocket_remove_unused_css' ) ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		if ( ! $this->can_optimize_url() ) {
			return;
		}

		$referer = '';
		$action  = 'rocket_clear_usedcss_url';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );
			$referer     = '&_wp_http_referer=' . rawurlencode( remove_query_arg( 'fl_builder', $referer_url ) );
		}

		/**
		 * Clear usedCSS for this URL (frontend).
		 */
		$wp_admin_bar->add_menu(
			[
				'parent' => 'wp-rocket',
				'id'     => 'remove-usedcss-url',
				'title'  => __( 'Clear this URL\'s UsedCSS', 'rocket' ),
				'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . $referer ), 'remove_usedcss_url' ),
			]
		);
	}

	public function clear_url_usedcss( string $url ) {
		$this->used_css_query->delete_by_url( $url );
	}
}
