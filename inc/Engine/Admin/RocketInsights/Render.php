<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;

class Render extends Abstract_Render {
	/**
	 * Plan instance.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Beacon instance.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Query instance.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Constructor for the Render class.
	 *
	 * Initializes the Render instance with the provided template path and CreditManager.
	 *
	 * @param string  $template_path   Path to the template file.
	 * @param Plan    $plan Plan instance.
	 * @param Context $context Instance of PerformanceMonitoringContext for managing performance monitoring context.
	 * @param Beacon  $beacon          Beacon instance.
	 * @param Query   $query           Query instance.
	 */
	public function __construct( $template_path, Plan $plan, Context $context, Beacon $beacon, Query $query ) {
		parent::__construct( $template_path );

		$this->plan    = $plan;
		$this->context = $context;
		$this->beacon  = $beacon;
		$this->query   = $query;
	}

	/**
	 * Prepare score data array for performance score rendering.
	 *
	 * @param object $row Database row object.
	 * @return array Score data array.
	 */
	private function prepare_score_data( $row ): array {
		$score_data = [
			'score'        => $row->score,
			'status'       => $row->status,
			'is_blurred'   => $row->is_blurred,
			'is_dashboard' => false,
		];

		if ( 'failed' !== $row->status ) {
			$score_data['status-color'] = $this->get_score_color_status( (int) $row->score );
		}

		return $score_data;
	}

	/**
	 * Get color status class based on performance score.
	 *
	 * @param int $score Performance score (0-100).
	 * @return string Color status class.
	 */
	public function get_score_color_status( int $score ): string {
		if ( $score <= 50 ) {
			return 'status-red';
		}
		if ( $score <= 85 ) {
			return 'status-yellow';
		}
		return 'status-green';
	}

	/**
	 * Generate global score row HTML.
	 *
	 * @param array $data Data for the global score row.
	 * @return string The rendered HTML for the global score row.
	 */
	public function get_global_score_row( array $data ) {
		$data['status_text'] = $this->get_monitoring_status_text();
		return $this->generate( 'partials/rocket-insights/global-score-row', $data );
	}


	/**
	 * Render global score row.
	 *
	 * @param array $data Data for the global score row.
	 * @return void
	 */
	public function render_global_score_row( array $data ) {
		echo $this->get_global_score_row( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render performance urls table.
	 *
	 * @param array $data Data for the performance urls table.
	 * @return void
	 */
	public function render_rocket_insights_urls_table( array $data ) {
		$rocket_insights_beacon = $this->beacon->get_suggest( 'rocket_insights' );

		$data['has_credit']    = $this->plan->has_credit();
		$data['can_add_url']   = $this->context->is_adding_page_allowed();
		$data['reach_max_url'] = ! $data['can_add_url'];
		$data['help']          = $rocket_insights_beacon;

		echo $this->generate( 'partials/rocket-insights/urls-table', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render performance score.
	 *
	 * @param array $data Data for the performance score.
	 * @return void
	 */
	public function render_performance_score( array $data ) {
		$data['status-color'] = $this->get_score_color_status( (int) $data['score'] );

		echo $this->generate( 'partials/rocket-insights/performance-score', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render global score widget.
	 *
	 * @param array $data Data for the widget.
	 * @return void
	 */
	public function render_global_score_widget( array $data ) {
		echo $this->get_global_score_widget( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Generate the global score widget HTML.
	 *
	 * @param array $data Data for the global score widget.
	 * @return string The rendered HTML for the global score widget.
	 */
	public function get_global_score_widget( array $data ): string {
		return $this->generate(
			'partials/rocket-insights/global-score-widget',
			$this->prepare_global_score_widget_data( $data )
		);
	}

	/**
	 * Generate the content for the global score widget.
	 *
	 * @param array $data Data for the global score widget content.
	 * @return string The rendered HTML for the global score widget content.
	 */
	public function get_global_score_widget_content( array $data ): string {
		return $this->render_parts_with_data(
			'rocket-insights/global-score-widget-content',
			$this->prepare_global_score_widget_data( $data ),
			true
		);
	}

	/**
	 * Retrieves the data array for the global score widget.
	 *
	 * @param array $data Input data for the global score widget.
	 * @return array The prepared data for the global score widget.
	 */
	private function prepare_global_score_widget_data( array $data ) {
		$is_adding_page_allowed = $this->context->is_adding_page_allowed();

		return array_merge(
			$data,
			[
				'has_credit'    => $this->plan->has_credit(),
				'can_add_url'   => $is_adding_page_allowed,
				'reach_max_url' => ! $is_adding_page_allowed,
				'status_text'   => $this->get_monitoring_status_text(),
			]
		);
	}

	/**
	 * Generates the HTML for a single performance monitoring list row.
	 *
	 * @param object $data The data object representing a single row (page) in the performance monitoring list.
	 * @return string The rendered HTML for the performance monitoring row.
	 */
	public function get_performance_monitoring_list_row( object $data ): string {
		$data->has_credit = $this->plan->has_credit();

		return $this->generate( 'partials/rocket-insights/table-row', $data );
	}

	/**
	 * Render the HTML for a single performance monitoring list row.
	 *
	 * @param object $data The data object representing a single row (page) in the performance monitoring list.
	 * @return void
	 */
	public function render_performance_monitoring_list_row( object $data ) {
		echo $this->get_performance_monitoring_list_row( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the license banner section from views.
	 *
	 * @param array $data Data to render the license banner section.
	 *
	 * @return void
	 */
	public function render_license_banner_section( array $data ) {
		echo $this->generate( 'partials/rocket-insights/license-banner', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the plan price in the license banner section from views.
	 *
	 * @param string $price    Price value.
	 * @param string $currency Currency symbol, default is '$'.
	 * @param string $period   Billing period, default is 'month'.
	 *
	 * @return void
	 */
	public function render_license_banner_plan_price( string $price, string $currency = '$', string $period = 'month' ) {
		global $wp_locale;
		$dot   = $wp_locale->number_format['decimal_point'] ?? '.';
		$price = number_format_i18n( $price, 2 );
		$price = explode( $dot, $price );
		$data  = [
			'price_number'  => $price[0],
			'price_decimal' => $dot . $price[1],
			'currency'      => $currency,
			'period'        => $period,
		];
		echo $this->generate( 'partials/rocket-insights/license-banner-plan-price', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Returns the appropriate monitoring status text based on schedule allowance.
	 *
	 * @return string The translated status text for monitored or tracked pages.
	 */
	private function get_monitoring_status_text(): string {
		if ( $this->context->is_schedule_allowed() ) {
			return __( 'Monitored Pages', 'rocket' );
		}

		return __( 'Tracked Pages', 'rocket' );
	}

	/**
	 * Generates the appropriate "Add Page" button markup for the given UI context.
	 *
	 * @since 3.20
	 *
	 * @param string $type The context in which the button is used. Accepts 'rocket-insights' or 'global-score-widget'.
	 * @param array  $data Data to be passed to the button template.
	 *
	 * @return string The generated HTML for the "Add Page" button.
	 */
	public function get_add_page_btn( string $type, array $data ) {
		switch ( $type ) {
			case 'global-score-widget':
				$button = $this->generate( 'partials/rocket-insights/buttons/global-score-widget', $data );
				break;

			case 'rocket-insights':
			default:
				$button = $this->generate( 'partials/rocket-insights/buttons/rocket-insights-panel', $data );
				break;
		}

		return $button;
	}

	/**
	 * Outputs the HTML for the "Add Page" button using the provided type and data.
	 *
	 * @since 3.20
	 *
	 * @param string $type The context in which the button is used. Accepts 'rocket-insights' or 'global-score-widget'.
	 * @param array  $data Data to be passed to the button template.
	 *
	 * @return void
	 */
	public function render_add_page_btn( string $type, array $data ): void {
		echo $this->get_add_page_btn( $type, $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Determines if the given title exceeds the maximum allowed length.
	 *
	 * @since 3.20
	 *
	 * @param string $title The title to check.
	 * @return bool True if the title is truncated, false otherwise.
	 */
	public function is_title_truncated( string $title ): bool {
		$max_length = 37;

		if ( mb_strlen( $title ) <= $max_length ) {
			return false;
		}

		return true;
	}

	/**
	 * Generates the Rocket Insights column content for post listing pages.
	 *
	 * @since 3.20.1
	 *
	 * @param string $url The URL of the post.
	 * @param int    $post_id Post ID of post.
	 *
	 * @return string The rendered HTML for the Rocket Insights column.
	 */
	public function get_rocket_insights_column( string $url, int $post_id ): string {
		// Query for existing row in the database.
		// Try both with and without trailing slash for compatibility.
		$url_no_slash   = untrailingslashit( $url );
		$url_with_slash = trailingslashit( $url );

		$row     = null;
		$results = $this->query->query( [ 'url' => $url_no_slash ] );

		// If not found without slash, try with slash.
		if ( empty( $results ) || ! is_array( $results ) ) {
			$results = $this->query->query( [ 'url' => $url_with_slash ] );
		}

		if ( ! empty( $results ) && is_array( $results ) ) {
			$row = $results[0];
		}

		// Use normalized URL (without trailing slash) for frontend.
		$normalized_url = $url_no_slash;

		// Get credit availability.
		$has_credit = $this->plan->has_credit();

		$can_add_pages = $this->context->is_adding_page_allowed();

		// Prepare template variables.
		$template_data = [
			'wpr_rocket_insights_url'   => $normalized_url,
			'wpr_rocket_row'            => $row,
			'wpr_has_credit'            => $has_credit,
			'wpr_can_add_pages'         => $can_add_pages,
			'wpr_is_free_user'          => $this->context->is_free_user(),
			'wpr_limit_reached_message' => $this->get_page_limit_error_message(),
			'is_draft'                  => get_post_status( $post_id ) === 'draft',
			'wpr_post_id'               => $post_id,
		];

		if ( null !== $row ) {
			$template_data['wpr_is_running']        = $row->is_running();
			$template_data['wpr_has_results']       = 'completed' === $row->status || 'blurred' === $row->status;
			$template_data['wpr_is_blurred']        = isset( $row->is_blurred ) && $row->is_blurred;
			$template_data['wpr_can_access_report'] = $row->can_access_report();

			// Prepare score data for template rendering.
			$template_data['wpr_score_data'] = $this->prepare_score_data( $row );
		}

		return $this->generate(
			'partials/rocket-insights/rocket-insights-column',
			$template_data
		);
	}

	/**
	 * Renders the Rocket Insights column content for post listing pages.
	 *
	 * @since 3.20.1
	 *
	 * @param string $url The URL of the post.
	 * @param int    $post_id Post ID of post.
	 *
	 * @return void
	 */
	public function render_rocket_insights_column( string $url, int $post_id ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template handles escaping.
		echo $this->get_rocket_insights_column( $url, $post_id );
	}

	/**
	 * Get the error message for when page limit is reached.
	 *
	 * @since 3.17
	 *
	 * @return string The formatted error message.
	 */
	public function get_page_limit_error_message(): string {
		if ( $this->context->is_free_user() ) {
			$upgrade_url = admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '&rocket_source=wp_posts_list#rocket_insights' );

			return sprintf(
				/* translators: %1$s: opening <strong> tag, %2$s: closing </strong> tag, %3$s: opening link tag, %4$s: closing link tag */
				__( "You've %1\$sreached your free limit%2\$s. %3\$sUpgrade to continue%4\$s.", 'rocket' ),
				'<strong>',
				'</strong>',
				'<a href="' . esc_url( $upgrade_url ) . '">',
				'</a>'
			);
		}

		return sprintf(
			/* translators: %1$s: opening <strong> tag, %2$s: closing </strong> tag */
			__( "You've %1\$sreached the page limit%2\$s. Please remove at least one page to continue.", 'rocket' ),
			'<strong>',
			'</strong>'
		);
	}
}
