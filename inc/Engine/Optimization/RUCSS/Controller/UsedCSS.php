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
		$used_css  = $this->get_used_css( $url, $is_mobile );

		if ( empty( $used_css ) ) {
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

				//Increment retries.
				$retries = 0;
				if ( isset( $used_css->retries ) ) {
					$retries = $used_css->retries;
				}

				$data = [
					'url'        => $url,
					'retries'    => $retries + 1,
					'is_mobile'  => $is_mobile,
					'job_id'     => null,
					'queue_name' => '',
					'status'     => 'failed',
					'modified'   => current_time( 'mysql', true ),
				];

				$this->save_or_update_used_css( $data );

				return $html;
			}

			//We got jobid and queue name so save them into the DB and change status to be pending
			$data = [
				'url'        => $url,
				'retries'    => 0,
				'is_mobile'  => $is_mobile,
				'job_id'     => $add_to_queue_response['contents']['jobId'],
				'queue_name' => $add_to_queue_response['contents']['queueName'],
				'status'     => 'pending',
				'modified'   => current_time( 'mysql', true ),
			];

			$this->save_or_update_used_css( $data );

			return $html;
		}

		if ( 'completed' !== $used_css->status || empty( $used_css->css ) ) {
			return $html;
		}

		$html = $this->remove_used_css_from_html( $html, $used_css->unprocessedcss ?? [] );
		$html = $this->add_used_css_to_html( $html, $used_css );

		$this->update_last_accessed( (int) $used_css->id );

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
	 * Resets retries to 1 and cleans URL cache for retrying the regeneration of the used CSS.
	 *
	 * @return void
	 */
	public function retries_pages_with_unprocessed_css() {
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		$used_css_list = $this->get_used_css_with_unprocessed_css();

		foreach ( $used_css_list as $used_css_item ) {
			// Resets retries to 1.
			$this->used_css_query->update_item(
				$used_css_item->id,
				[ 'retries' => 1 ]
			);
			// Cleans page cache.
			$this->purge->purge_url( $used_css_item->url );
		}
	}

	/**
	 * Get UsedCSS from DB table based on page url.
	 *
	 * @param string $url       The page URL.
	 * @param bool   $is_mobile Page is_mobile.
	 *
	 * @return UsedCSS_Row|false
	 */
	private function get_used_css( string $url, bool $is_mobile = false ) {
		$query = $this->used_css_query->query(
			[
				'url'       => $url,
				'is_mobile' => $is_mobile,
			]
		);

		if ( empty( $query[0] ) ) {
			return false;
		}

		return $query[0];
	}

	/**
	 * Get UsedCSS from DB table which has unprocessed CSS files.
	 *
	 * @return array
	 */
	private function get_used_css_with_unprocessed_css() {
		$query = $this->used_css_query->query(
			[
				'unprocessedcss__not_in' => [
					'not_in' => '[]',
				],
			]
		);

		return $query;
	}

	/**
	 * Insert or update used css row based on URL.
	 *
	 * @param array $data           {
	 *                              Data to be saved / updated in database.
	 *
	 * @type string $url            The page URL.
	 * @type string $css            The page used css.
	 * @type string $unprocessedcss A json_encoded array of the page unprocessed CSS list.
	 * @type int    $retries        No of automatically retries for generating the unused css.
	 * @type bool   $is_mobile      Is mobile page.
	 * }
	 *
	 * @return UsedCSS_Row|false
	 */
	private function save_or_update_used_css( array $data ) {
		$used_css = $this->get_used_css( $data['url'], $data['is_mobile'] );

		if ( isset( $data['css'] ) ) {
			$data['css'] = $this->apply_font_display_swap( $data['css'] );

			/**
			 * Filters Used CSS content before saving into DB.
			 *
			 * @since 3.9.0.2
			 *
			 * @param string $usedcss Used CSS.
			 */
			$data['css'] = apply_filters( 'rocket_usedcss_content', $data['css'] );
		}

		if ( empty( $used_css ) ) {
			$inserted = $this->insert_used_css( $data );

			if ( ! $inserted ) {
				return false;
			}
			return $inserted;
		}

		$updated = $this->update_used_css( (int) $used_css->id, $data );

		if ( ! $updated ) {
			return false;
		}
		return $updated;
	}

	/**
	 * Insert used CSS.
	 *
	 * @param array $data Data to be inserted in used_css table.
	 *
	 * @return object|false
	 */
	private function insert_used_css( array $data ) {
		$id = $this->used_css_query->add_item( $data );

		if ( empty( $id ) ) {
			return false;
		}

		return $this->used_css_query->get_item( $id );
	}

	/**
	 * Update used CSS.
	 *
	 * @param integer $id   Used CSS ID.
	 * @param array   $data Data to be updated in used_css table.
	 *
	 * @return object|false
	 */
	private function update_used_css( int $id, array $data ) {
		$updated = $this->used_css_query->update_item( $id, $data );
		if ( ! $updated ) {
			return false;
		}

		return $this->used_css_query->get_item( $id );
	}

	/**
	 * Alter HTML and remove all CSS which was processed from HTML page.
	 *
	 * @param string $html            HTML content.
	 * @param array  $unprocessed_css List with unprocesses CSS links or inline.
	 *
	 * @return string HTML content.
	 */
	private function remove_used_css_from_html( string $html, array $unprocessed_css ): string {
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

		$unprocessed_links  = $this->unprocessed_flat_array( 'link', $unprocessed_css );
		$unprocessed_styles = $this->unprocessed_flat_array( 'inline', $unprocessed_css );

		foreach ( $link_styles as $style ) {
			if (
				! (bool) preg_match( '/rel=[\'"]stylesheet[\'"]/is', $style[0] )
				||
				strstr( $style['url'], '//fonts.googleapis.com/css' )
				||
				in_array( htmlspecialchars_decode( $style['url'] ), $unprocessed_links, true )
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
			if ( in_array( $this->strip_line_breaks( $style['content'] ), $unprocessed_styles, true ) ) {
				continue;
			}

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
	 * Update UsedCSS Row last_accessed date to current date.
	 *
	 * @param int $id Used CSS id.
	 *
	 * @return bool
	 */
	private function update_last_accessed( int $id ): bool {
		return (bool) $this->used_css_query->update_item(
			$id,
			[
				'last_accessed' => current_time( 'mysql', true ),
			]
		);
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
	 * Create dedicated array of unprocessed css.
	 *
	 * @param string $type            CSS type (link / inline).
	 * @param array  $unprocessed_css Array with unprocessed CSS.
	 *
	 * @return array Array with type of unprocessed CSS.
	 */
	private function unprocessed_flat_array( string $type, array $unprocessed_css ): array {
		$unprocessed_array = [];
		foreach ( $unprocessed_css as $css ) {
			if ( $type === $css['type'] ) {
				$unprocessed_array[] = $this->strip_line_breaks( $css['content'] );
			}
		}

		return $unprocessed_array;
	}

	/**
	 * Strip line breaks.
	 *
	 * @param string $value - Value to be processed.
	 *
	 * @return string
	 */
	private function strip_line_breaks( string $value ): string {
		$value = str_replace( [ "\r", "\n", "\r\n", "\t" ], '', $value );

		return trim( $value );
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
		return $url === untrailingslashit( home_url() );
	}

	/**
	 * Schedules RUCSS to retry pages with missing CSS files.
	 * Retries happen after 30 minutes.
	 *
	 * @return void
	 */
	private function schedule_rucss_retry() {
		$scheduled = wp_next_scheduled( 'rocket_rucss_retries_cron' );

		if ( $scheduled ) {
			return;
		}

		wp_schedule_single_event( time() + ( 0.5 * HOUR_IN_SECONDS ), 'rocket_rucss_retries_cron' );
	}

	/**
	 * Remove any unprocessed items from the resources table.
	 *
	 * @since 3.9
	 *
	 * @param array $unprocessed_css Unprocessed CSS Items.
	 *
	 * @return void
	 */
	private function remove_unprocessed_from_resources( $unprocessed_css ) {
		foreach ( $unprocessed_css as $resource ) {
			$this->resources_query->remove_by_url( $resource['content'] );
		}
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

			$this->queue->add_job_status_check_async( $used_css_row->id );
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

				$params = [
					'status'     => 'failed',
					'queue_name' => '',
					'job_id'     => '',
				];
				$this->used_css_query->update_item( $id, $params );

				return;
			}

			// Increment the retries number with 1.
			$this->used_css_query->increment_retries( $id, $row_details->retries );
			//@Todo: Maybe we can add this row to the async job to get the status before the next cron

			return;
		}

		//Everything is fine, save the usedcss into DB, change status to completed and reset queue_name and job_id.
		Logger::debug( 'RUCSS: Save used CSS for url: ' . $row_details->url );

		$params = [
			'css'        => $job_details['contents']['shakedCSS'],
			'status'     => 'completed',
			'queue_name' => '',
			'job_id'     => '',
		];
		$this->used_css_query->update_item( $id, $params );

		//Flush cache for this url.
		Logger::debug( 'RUCSS: Purge the cache for url: ' . $row_details->url );
		$this->purge->purge_url( $row_details->url );

		do_action( 'rucss_complete_job_status', $row_details->url, $job_details );

	}
}
