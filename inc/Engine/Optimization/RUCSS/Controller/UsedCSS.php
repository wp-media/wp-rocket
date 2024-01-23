<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Admin_Bar;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory\StrategyFactory;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

class UsedCSS implements LoggerAwareInterface {
	use RegexTrait;
	use CSSTrait;
	use LoggerAware;

	/**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	private $used_css_query;

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
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Filesystem instance
	 *
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * RUCSS context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * RUCSS optimize url context.
	 *
	 * @var ContextInterface
	 */
	protected $optimize_url_context;

	/**
	 * External exclusions list, can be urls or attributes.
	 *
	 * @var array
	 */
	private $external_exclusions = [];

	/**
	 * Inline CSS attributes exclusions patterns to be preserved on the page after treeshaking.
	 *
	 * @var string[]
	 */
	private $inline_atts_exclusions = [];

	/**
	 * Inline CSS content exclusions patterns to be preserved on the page after treeshaking.
	 *
	 * @var string[]
	 */
	private $inline_content_exclusions = [];

	/**
	 * Retry Strategy Factory
	 *
	 * @var StrategyFactory
	 */
	protected $strategy_factory;

	/**
	 * Clock instance.
	 *
	 * @var WPRClock
	 */
	protected $wpr_clock;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data     $options Options instance.
	 * @param UsedCSS_Query    $used_css_query Usedcss Query instance.
	 * @param APIClient        $api APIClient instance.
	 * @param QueueInterface   $queue Queue instance.
	 * @param DataManager      $data_manager DataManager instance.
	 * @param Filesystem       $filesystem Filesystem instance.
	 * @param ContextInterface $context RUCSS context.
	 * @param ContextInterface $optimize_url_context RUCSS optimize url context.
	 * @param StrategyFactory  $strategy_factory Strategy Factory used for RUCSS retry process.
	 * @param WPRClock         $clock Clock object instance.
	 */
	public function __construct(
		Options_Data $options,
		UsedCSS_Query $used_css_query,
		APIClient $api,
		QueueInterface $queue,
		DataManager $data_manager,
		Filesystem $filesystem,
		ContextInterface $context,
		ContextInterface $optimize_url_context,
		StrategyFactory $strategy_factory,
		WPRClock $clock
	) {
		$this->options              = $options;
		$this->used_css_query       = $used_css_query;
		$this->api                  = $api;
		$this->queue                = $queue;
		$this->data_manager         = $data_manager;
		$this->filesystem           = $filesystem;
		$this->context              = $context;
		$this->optimize_url_context = $optimize_url_context;
		$this->strategy_factory     = $strategy_factory;
		$this->wpr_clock            = $clock;
	}

	/**
	 * Check if RUCSS option is enabled.
	 *
	 * Used inside the CRON so post object isn't there.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) $this->options->get( 'remove_unused_css', 0 );
	}

	/**
	 * Start treeshaking the current page.
	 *
	 * @param string $html Buffet HTML for current page.
	 *
	 * @return string
	 */
	public function treeshake( string $html ): string {
		if ( ! $this->context->is_allowed() ) {
			return $html;
		}

		$clean_html = $this->hide_comments( $html );
		$clean_html = $this->hide_noscripts( $clean_html );
		$clean_html = $this->hide_scripts( $clean_html );

		if ( ! $this->html_has_title_tag( $clean_html ) ) {
			return $html;
		}

		global $wp;
		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->is_mobile();
		$used_css  = $this->used_css_query->get_row( $url, $is_mobile );

		if ( empty( $used_css ) ) {
			$this->add_url_to_the_queue( $url, $is_mobile );
			return $html;
		}

		if ( 'completed' !== $used_css->status || empty( $used_css->hash ) ) {
			return $html;
		}

		$used_css_content = $this->filesystem->get_used_css( $used_css->hash );

		if ( empty( $used_css_content ) ) {
			$this->used_css_query->delete_by_url( $url );
			return $html;
		}

		$html = $this->remove_used_css_from_html( $clean_html, $html );
		$html = $this->add_used_css_to_html( $html, $used_css_content );
		$html = $this->add_used_fonts_preload( $html, $used_css_content );
		$html = $this->remove_google_font_preconnect( $html );
		$this->used_css_query->update_last_accessed( (int) $used_css->id );

		return $html;
	}

	/**
	 * Send the request to add url into the queue.
	 *
	 * @param string $url page URL.
	 * @param bool   $is_mobile page is for mobile.
	 *
	 * @return void
	 */
	public function add_url_to_the_queue( string $url, bool $is_mobile ) {
		$used_css_row = $this->used_css_query->get_row( $url, $is_mobile );
		if ( empty( $used_css_row ) ) {
			$this->used_css_query->create_new_job( $url, '', '', $is_mobile );
			return;
		}
		$this->used_css_query->reset_job( (int) $used_css_row->id );
	}
	/**
	 * Delete used css based on URL.
	 *
	 * @param string $url The page URL.
	 *
	 * @return boolean
	 */
	public function delete_used_css( string $url ): bool {
		$used_css_arr = $this->used_css_query->get_rows_by_url( $url );

		if ( empty( $used_css_arr ) ) {
			return false;
		}

		$deleted = true;

		foreach ( $used_css_arr as $used_css ) {
			if ( empty( $used_css->id ) ) {
				continue;
			}

			$deleted = $deleted && $this->used_css_query->delete_item( $used_css->id );

			$count = $this->used_css_query->count_rows_by_hash( $used_css->hash );

			if ( 0 === $count ) {
				$this->filesystem->delete_used_css( $used_css->hash );
			}
		}

		return $deleted;
	}

	/**
	 * Deletes all the used CSS files
	 *
	 * @since 3.11.4
	 *
	 * @return void
	 */
	public function delete_all_used_css() {
		$this->filesystem->delete_all_used_css();
	}

	/**
	 * Alter HTML and remove all CSS which was processed from HTML page.
	 *
	 * @param string $clean_html Cleaned HTML after removing comments, noscripts and scripts.
	 * @param string $html HTML content.
	 *
	 * @return string HTML content.
	 */
	private function remove_used_css_from_html( string $clean_html, string $html ): string {
		$this->set_inline_exclusions_lists();
		$html = $this->remove_external_styles_from_html( $clean_html, $html );
		return $this->remove_internal_styles_from_html( $clean_html, $html );
	}

	/**
	 * Remove external styles from the page's HTML.
	 *
	 * @param string $clean_html Cleaned HTML after removing comments, noscripts and scripts.
	 * @param string $html Actual page's HTML.
	 *
	 * @return string
	 */
	private function remove_external_styles_from_html( string $clean_html, string $html ) {
		$link_styles = $this->find(
			'<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>',
			$clean_html,
			'Uis'
		);

		$preserve_google_font = apply_filters( 'rocket_rucss_preserve_google_font', false );

		$external_exclusions = $this->validate_array_and_quote(
			/**
			 * Filters the array of external exclusions.
			 *
			 * @since 3.11.4
			 *
			 * @param array $external_exclusions Array of patterns used to match against the external style tag.
			 */
			(array) apply_filters( 'rocket_rucss_external_exclusions', $this->external_exclusions )
		);

		foreach ( $link_styles as $style ) {
			if (
				! (bool) preg_match( '/rel=[\'"]?stylesheet[\'"]?/is', $style[0] )
				&&
				! ( (bool) preg_match( '/rel=[\'"]?preload[\'"]?/is', $style[0] ) && (bool) preg_match( '/as=[\'"]?style[\'"]?/is', $style[0] ) )
				||
				( $preserve_google_font && strstr( $style['url'], '//fonts.googleapis.com/css' ) )
			) {
				continue;
			}

			if ( ! empty( $external_exclusions ) && $this->find( implode( '|', $external_exclusions ), $style[0] ) ) {
				continue;
			}

			$html = str_replace( $style[0], '', $html );
		}

		return (string) $html;
	}

	/**
	 * Remove internal styles from the page's HTML.
	 *
	 * @param string $clean_html Cleaned HTML after removing comments, noscripts and scripts.
	 * @param string $html Actual page's HTML.
	 *
	 * @return string
	 */
	private function remove_internal_styles_from_html( string $clean_html, string $html ) {
		$inline_styles = $this->find(
			'<style(?<atts>.*)>(?<content>.*)<\/style\s*>',
			$clean_html
		);

		$inline_atts_exclusions = $this->validate_array_and_quote(
			/**
			 * Filters the array of inline CSS attributes patterns to preserve
			 *
			 * @since 3.11
			 *
			 * @param array $inline_atts_exclusions Array of patterns used to match against the inline CSS attributes.
			 */
			(array) apply_filters( 'rocket_rucss_inline_atts_exclusions', $this->inline_atts_exclusions )
		);

		$inline_content_exclusions = $this->validate_array_and_quote(
			/**
			 * Filters the array of inline CSS content patterns to preserve
			 *
			 * @since 3.11
			 *
			 * @param array $inline_atts_exclusions Array of patterns used to match against the inline CSS content.
			 */
			(array) apply_filters( 'rocket_rucss_inline_content_exclusions', $this->inline_content_exclusions )
		);

		foreach ( $inline_styles as $style ) {
			if ( ! empty( $inline_atts_exclusions ) && $this->find( implode( '|', $inline_atts_exclusions ), $style['atts'] ) ) {
				continue;
			}

			if ( ! empty( $inline_content_exclusions ) && $this->find( implode( '|', $inline_content_exclusions ), $style['content'] ) ) {
				continue;
			}

			/**
			 * Filters the status of preserving inline style tags.
			 *
			 * @since 3.11.4
			 *
			 * @param bool $preserve_status Status of preserve.
			 * @param array $style Full match style tag.
			 */
			if ( apply_filters( 'rocket_rucss_preserve_inline_style_tags', true, $style ) ) {
				$content = trim( $style['content'] );

				if ( empty( $content ) ) {
					continue;
				}

				$empty_tag = str_replace( $style['content'], '', $style[0] );
				$html      = str_replace( $style[0], $empty_tag, $html );

				continue;
			}

			$html = str_replace( $style[0], '', $html );
		}

		return $html;
	}

	/**
	 * Alter HTML string and add the used CSS style in <head> tag,
	 *
	 * @param string $html     HTML content.
	 * @param string $used_css Used CSS content.
	 *
	 * @return string HTML content.
	 */
	private function add_used_css_to_html( string $html, string $used_css ): string {
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
	 * Return Markup for used_css into the page.
	 *
	 * @param string $used_css Used CSS content.
	 *
	 * @return string
	 */
	private function get_used_css_markup( string $used_css ): string {
		/**
		 * Filters Used CSS content before output.
		 *
		 * @since 3.9.0.2
		 *
		 * @param string $used_css Used CSS content.
		 */
		$used_css = apply_filters( 'rocket_usedcss_content', $used_css );

		$used_css = str_replace( '\\', '\\\\', $used_css );// Guard the backslashes before passing the content to preg_replace.
		$used_css = $this->handle_charsets( $used_css, false );

		return sprintf(
			'<style id="wpr-usedcss">%s</style>',
			$used_css
		);
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return boolean
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}

	/**
	 * Check if current page is the home page.
	 *
	 * @param string $url Current page url.
	 *
	 * @return bool
	 */
	private function is_home( string $url ): bool {
		/**
		 * Filters the home url.
		 *
		 * @since 3.11.4
		 *
		 * @param string  $home_url home url.
		 * @param string  $url url of current page.
		 */
		$home_url = apply_filters( 'rocket_rucss_is_home_url', home_url(), $url );
		return untrailingslashit( $url ) === untrailingslashit( $home_url );
	}

	/**
	 * Process pending jobs inside cron iteration.
	 *
	 * @return void
	 */
	public function process_pending_jobs() {
		$this->logger::debug( 'RUCSS: Start processing pending jobs inside cron.' );

		if ( ! $this->is_enabled() ) {
			$this->logger::debug( 'RUCSS: Stop processing cron iteration because option is disabled.' );

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

		$this->logger::debug( "RUCSS: Start getting number of {$rows} pending jobs." );

		$pending_jobs = $this->used_css_query->get_pending_jobs( $rows );
		if ( ! $pending_jobs ) {
			$this->logger::debug( 'RUCSS: No pending jobs are there.' );

			return;
		}

		foreach ( $pending_jobs as $used_css_row ) {
			$current_time = $this->wpr_clock->current_time( 'timestamp', true );
			if ( strtotime( $used_css_row->next_retry_time ) < $current_time ) {
				$this->logger::debug( "RUCSS: Send the job for url {$used_css_row->url} to Async task to check its job status." );

				// Change status to in-progress.
				$this->used_css_query->make_status_inprogress( (int) $used_css_row->id );
				$this->queue->add_job_status_check_async( (int) $used_css_row->id );
			}
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
		$this->logger::debug( 'RUCSS: Start checking job status for row ID: ' . $id );

		$row_details = $this->used_css_query->get_item( $id );
		if ( ! $row_details ) {
			$this->logger::debug( 'RUCSS: Row ID not found ', compact( 'id' ) );

			// Nothing in DB, bailout.
			return;
		}

		// Send the request to get the job status from SaaS.
		$job_details = $this->api->get_queue_job_status( $row_details->job_id, $row_details->queue_name, $this->is_home( $row_details->url ) );

		/**
		 * Filters the rocket min rucss css result size.
		 *
		 * @since 3.13.3
		 *
		 * @param int min size.
		 */
		$min_rucss_size = apply_filters( 'rocket_min_rucss_size', 150 );
		if ( ! is_numeric( $min_rucss_size ) ) {
			$min_rucss_size = 150;
		}

		if ( isset( $job_details['contents']['shakedCSS_size'] ) && intval( $job_details['contents']['shakedCSS_size'] ) < $min_rucss_size ) {
			$message = 'RUCSS: shakedCSS size is less than ' . $min_rucss_size;
			$this->logger::error( $message );
			$this->used_css_query->make_status_failed( $id, '500', $message );
			return;
		}

		if (
			200 !== (int) $job_details['code']
		) {
			$this->logger::debug( 'RUCSS: Job status failed for url: ' . $row_details->url, $job_details );
			$this->strategy_factory->manage( $row_details, $job_details );

			return;
		}
		/**
		 * Unlock preload URL.
		 *
		 * @param string $url URL to unlock
		 */
		do_action( 'rocket_preload_unlock_url', $row_details->url );

		$css = $this->apply_font_display_swap( $job_details['contents']['shakedCSS'] );

		/**
		 * RUCSS hash.
		 *
		 * @param string $hash RUCSS hash.
		 * @param string $css RUCSS content.
		 * @param UsedCSSRow $row_details Job details.
		 */
		$hash = (string) apply_filters( 'rocket_rucss_hash',  md5( $css ), $css, $row_details );

		if ( ! $this->filesystem->write_used_css( $hash, $css ) ) {
			$message = 'RUCSS: Could not write used CSS to the filesystem: ' . $row_details->url;
			$this->logger::error( $message );
			$this->used_css_query->make_status_failed( $id, '', $message );

			return;
		}

		// Everything is fine, save the usedcss into DB, change status to completed and reset queue_name and job_id.
		$this->logger::debug( 'RUCSS: Save used CSS for url: ' . $row_details->url );

		$this->used_css_query->make_status_completed( $id, $hash );

		/**
		 * Fires after successfully saving the used CSS for an URL
		 *
		 * @param string $url URL used to generated the used CSS.
		 * @param array  $job_details Result of the request to get the job status from SaaS.
		 */
		do_action( 'rocket_rucss_complete_job_status', $row_details->url, $job_details );
	}

	/**
	 * Add clear UsedCSS adminbar item.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Adminbar object.
	 *
	 * @return void
	 */
	public function add_clear_usedcss_bar_item( WP_Admin_Bar $wp_admin_bar ) {
		global $post;

		if ( ! $this->optimize_url_context->is_allowed() ) {
			return;
		}

		/**
		 * Filters the rocket `clear used css of this url` option on admin bar menu.
		 *
		 * @since 3.12.1
		 *
		 * @param bool  $should_skip Should skip adding `clear used css of this url` option in admin bar.
		 * @param type  $post Post object.
		 */
		if ( apply_filters( 'rocket_skip_admin_bar_clear_used_css_option', false, $post ) ) {
			return;
		}

		$referer = '';
		$action  = 'rocket_clear_usedcss_url';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );

			/**
			 * Filters to act on the referer url for the admin bar.
			 *
			 * @param string $uri Current uri
			 */
			$referer = (string) apply_filters( 'rocket_admin_bar_referer', esc_url( $referer_url ) );
			$referer = '&_wp_http_referer=' . rawurlencode( remove_query_arg( 'fl_builder', $referer ) );
		}

		/**
		 * Clear usedCSS for this URL (frontend).
		 */
		$wp_admin_bar->add_menu(
			[
				'parent' => 'wp-rocket',
				'id'     => 'clear-usedcss-url',
				'title'  => __( 'Clear Used CSS of this URL', 'rocket' ),
				'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . $referer ), $action ),
			]
		);
	}

	/**
	 * Clear specific url.
	 *
	 * @param string $url Page url.
	 *
	 * @return void
	 */
	public function clear_url_usedcss( string $url ) {
		$this->delete_used_css( $url );

		/**
		 * Fires after clearing usedcss for specific url.
		 *
		 * @since 3.11
		 *
		 * @param string $url Current page URL.
		 */
		do_action( 'rocket_rucss_after_clearing_usedcss', $url );
	}

	/**
	 * Get the count of not completed rows.
	 *
	 * @return int
	 */
	public function get_not_completed_count() {
		return $this->used_css_query->get_not_completed_count();
	}

	/**
	 * Clear failed urls.
	 *
	 * @return void
	 */
	public function clear_failed_urls() {
		/**
		 * Delay before failed rucss jobs are deleted.
		 *
		 * @param string $delay delay before failed rucss jobs are deleted.
		 */
		$delay = (string) apply_filters( 'rocket_delay_remove_rucss_failed_jobs', '3 days' );

		if ( '' === $delay || '0' === $delay ) {
			$delay = '3 days';
		}
		$parts = explode( ' ', $delay );

		$value = 3;
		$unit  = 'days';

		if ( count( $parts ) === 2 && $parts[0] >= 0 ) {
			$value = (float) $parts[0];
			$unit  = $parts[1];
		}
		$rows = $this->used_css_query->get_failed_rows( $value, $unit );

		if ( empty( $rows ) ) {
			return;
		}

		$failed_urls = [];

		foreach ( $rows as  $row ) {
			$failed_urls[] = $row->url;

			$id = (int) $row->id;

			if ( empty( $id ) ) {
				continue;
			}

			$this->add_url_to_the_queue( $row->url, (bool) $row->is_mobile );
		}

		/**
		 * Fires after clearing failed urls.
		 *
		 * @param array $urls Failed urls.
		 */
		do_action( 'rocket_rucss_after_clearing_failed_url', $failed_urls );
	}

	/**
	 * Add preload links for the fonts in the used CSS
	 *
	 * @param string $html HTML content.
	 * @param string $used_css Used CSS content.
	 *
	 * @return string
	 */
	private function add_used_fonts_preload( string $html, string $used_css ): string {
		/**
		 * Filters the fonts preload from the used CSS
		 *
		 * @since 3.11
		 *
		 * @param bool $enable True to enable, false to disable.
		 */
		if ( ! apply_filters( 'rocket_enable_rucss_fonts_preload', true ) ) {
			return $html;
		}

		if ( ! preg_match_all( '/@font-face\s*{\s*(?<content>[^}]+)}/is', $used_css, $font_faces, PREG_SET_ORDER ) ) {
			return $html;
		}

		if ( empty( $font_faces ) ) {
			return $html;
		}

		$urls = [];

		foreach ( $font_faces as $font_face ) {
			if ( empty( $font_face['content'] ) ) {
				continue;
			}

			$font_url = $this->extract_first_font( $font_face['content'] );

			/**
			 * Filters font URL with CDN hostname
			 *
			 * @since 3.11.4
			 *
			 * @param type  $url url to be rewritten.
			 */
			$font_url = apply_filters( 'rocket_font_url', $font_url );

			if ( empty( $font_url ) ) {
				continue;
			}

			$urls[] = $font_url;
		}

		if ( empty( $urls ) ) {
			return $html;
		}

		$urls = array_unique( $urls );

		$replace = preg_replace(
			'#</title>#iU',
			'</title>' . $this->preload_links( $urls ),
			$html,
			1
		);

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Remove preconnect tag for google api.
	 *
	 * @param string $html html content.
	 *
	 * @return string
	 */
	protected function remove_google_font_preconnect( string $html ): string {
		$clean_html = $this->hide_comments( $html );
		$clean_html = $this->hide_noscripts( $clean_html );
		$clean_html = $this->hide_scripts( $clean_html );
		$links      = $this->find(
			'<link\s+([^>]+[\s"\'])?rel\s*=\s*[\'"]((preconnect)|(dns-prefetch))[\'"]([^>]+)?\/?>',
			$clean_html,
			'Uis'
		);

		foreach ( $links as $link ) {
			if ( preg_match( '/href=[\'"](https:)?\/\/fonts.googleapis.com\/?[\'"]/', $link[0] ) ) {
				$html = str_replace( $link[0], '', $html );
			}
		}

		return $html;
	}

	/**
	 * Extracts the first font URL from the font-face declaration
	 *
	 * Skips .eot fonts if it exists
	 *
	 * @since 3.11
	 *
	 * @param string $font_face Font-face declaration content.
	 *
	 * @return string
	 */
	private function extract_first_font( string $font_face ): string {
		if ( ! preg_match_all( '/src:\s*(?<urls>[^;}]*)/is', $font_face, $sources, PREG_SET_ORDER ) ) {
			return '';
		}

		foreach ( $sources as $src ) {
			if ( empty( $src['urls'] ) ) {
				continue;
			}

			$urls = explode( ',', $src['urls'] );

			foreach ( $urls as $url ) {
				if ( false !== strpos( $url, '.eot' ) ) {
					continue;
				}

				if ( ! preg_match( '/url\(\s*[\'"]?(?<url>[^\'")]+)[\'"]?\)/is', $url, $matches ) ) {
					continue;
				}

				return trim( $matches['url'] );
			}
		}

		return '';
	}

	/**
	 * Converts an array of URLs to preload link tags
	 *
	 * @param array $urls An array of URLs.
	 *
	 * @return string
	 */
	private function preload_links( array $urls ): string {
		$links = '';

		foreach ( $urls as $url ) {
			$links .= '<link rel="preload" as="font" href="' . esc_url( $url ) . '" crossorigin>';
		}

		return $links;
	}

	/**
	 * Set Rucss inline attr exclusions
	 *
	 *  @return void
	 */
	private function set_inline_exclusions_lists() {
		$wpr_dynamic_lists               = $this->data_manager->get_lists();
		$this->inline_atts_exclusions    = isset( $wpr_dynamic_lists->rucss_inline_atts_exclusions ) ? $wpr_dynamic_lists->rucss_inline_atts_exclusions : [];
		$this->inline_content_exclusions = isset( $wpr_dynamic_lists->rucss_inline_content_exclusions ) ? $wpr_dynamic_lists->rucss_inline_content_exclusions : [];
	}

	/**
	 * Displays a notice if the used CSS folder is not writable
	 *
	 * @since 3.11.4
	 *
	 * @return void
	 */
	public function notice_write_permissions() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( $this->filesystem->is_writable_folder() ) {
			return;
		}

		$message = rocket_notice_writing_permissions( trim( str_replace( rocket_get_constant( 'ABSPATH', '' ), '', rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH', '' ) ), '/' ) );

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}

	/**
	 * Validate the items in array to be strings only and preg_quote them.
	 *
	 * @param array $items Array to be validated and quoted.
	 *
	 * @return array|string[]
	 */
	private function validate_array_and_quote( array $items ) {
		$items_array = array_filter( $items, 'is_string' );

		return array_map(
			static function ( $item ) {
				return preg_quote( $item, '/' );
			},
			$items_array
		);
	}

	/**
	 * Check if database has at least one completed row.
	 *
	 * @return bool
	 */
	public function has_one_completed_row_at_least() {
		return $this->used_css_query->get_completed_count() > 0;
	}

	/**
	 * Process on submit jobs.
	 *
	 * @return void
	 */
	public function process_on_submit_jobs() {

		if ( ! $this->is_enabled() ) {
			$this->logger::debug( 'RUCSS: Stop processing cron iteration because option is disabled.' );

			return;
		}

		/**
		 * Pending rows cont.
		 *
		 * @param int $count Number of rows.
		 */
		$pending_job = (int) apply_filters( 'rocket_rucss_pending_jobs_cron_rows_count', 100 );

		/**
		 * Maximum processing rows.
		 *
		 * @param int $max Max processing rows.
		 */
		$max_pending_rows = (int) apply_filters( 'rocket_rucss_max_pending_jobs', 3 * $pending_job, $pending_job );
		$rows             = $this->used_css_query->get_on_submit_jobs( $max_pending_rows );

		foreach ( $rows as $row ) {
			$response = $this->send_api( $row->url, (bool) $row->is_mobile );
			if ( false === $response || ! isset( $response['contents'], $response['contents']['jobId'], $response['contents']['queueName'] ) ) {

				$this->used_css_query->make_status_failed( (int) $row->id, '',  '' );
				continue;
			}

			/**
			 * Lock preload URL.
			 *
			 * @param string $url URL to lock
			 */
			do_action( 'rocket_preload_lock_url', $row->url );

			$this->used_css_query->make_status_pending(
				(int) $row->id,
				$response['contents']['jobId'],
				$response['contents']['queueName'],
				(bool) $row->is_mobile
			);
		}
	}

	/**
	 * Send the job to the API.
	 *
	 * @param string $url URL to work on.
	 * @param bool   $is_mobile Is the page for mobile.
	 * @return array|false
	 */
	protected function send_api( string $url, bool $is_mobile ) {
		/**
		 * Filters the RUCSS safelist
		 *
		 * @since 3.11
		 *
		 * @param array $safelist Array of safelist values.
		 */
		$safelist = apply_filters( 'rocket_rucss_safelist', $this->options->get( 'remove_unused_css_safelist', [] ) );

		/**
		 * Filters the styles attributes to be skipped (blocked) by RUCSS.
		 *
		 * @since 3.14
		 *
		 * @param array $skipped_attr Array of safelist values.
		 */
		$skipped_attr = apply_filters( 'rocket_rucss_skip_styles_with_attr', [] );
		$skipped_attr = ( is_array( $skipped_attr ) ) ? $skipped_attr : [];

		$config = [
			'treeshake'      => 1,
			'rucss_safelist' => $safelist,
			'skip_attr'      => $skipped_attr,
			'is_mobile'      => $is_mobile,
			'is_home'        => $this->is_home( $url ),
		];

		$add_to_queue_response = $this->api->add_to_queue( $url, $config );
		if ( 200 !== $add_to_queue_response['code'] ) {
			$this->logger::error(
				'Error when contacting the RUCSS API.',
				[
					'rucss error',
					'url'     => $url,
					'code'    => $add_to_queue_response['code'],
					'message' => $add_to_queue_response['message'],
				]
			);

			return false;
		}

		return $add_to_queue_response;
	}
}
