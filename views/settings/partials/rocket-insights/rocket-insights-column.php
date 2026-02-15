<?php
/**
 * Rocket Insights column content for post listing pages.
 *
 * @since 3.20.1
 *
 * @var array $data {
 *     Template data.
 *
 *     @type string      $data['wpr_rocket_insights_url'] The URL of the post.
 *     @type object|null $data['wpr_rocket_row']          Database row object for the URL (null if not tracked).
 *     @type bool        $data['wpr_has_credit']          Whether the user has credit available.
 *     @type bool        $data['wpr_can_add_pages']       Whether the user can add more pages (based on plan limits).
 *     @type bool        $data['wpr_is_free_user']        Whether the user is on the free plan.
 *     @type bool        $data['is_draft']                Whether the post is a draft.
 *     @type int         $data['wpr_post_id']             The ID of the post.
 * }
 */

defined( 'ABSPATH' ) || exit;

// If row doesn't exist, show "Test the page" link.
if ( null === $data['wpr_rocket_row'] ) :
	// For not-tracked rows always render the button.
	// The click handler will decide whether to show the limit message or proceed.
	?>
	<div class="wpr-ri-column wpr-ri-not-tracked" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>" data-has-credit="<?php echo esc_attr( $data['wpr_has_credit'] ? '1' : '0' ); ?>" data-can-add-pages="<?php echo esc_attr( $data['wpr_can_add_pages'] ? '1' : '0' ); ?>" data-post-id="<?php echo esc_attr( $data['wpr_post_id'] ); ?>">
		<?php if ( $data['is_draft'] ) : ?>
			<div class="wpr-btn-with-tool-tip">
		<?php endif; ?>
		<button 
			type="button"
			class="wpr-ri-test-page <?php echo $data['is_draft'] ? 'wpr-ri-no-credit' : ''; ?>"
			data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>"
			data-post-id="<?php echo esc_attr( $data['wpr_post_id'] ); ?>"
			<?php echo $data['is_draft'] ? 'disabled' : ''; ?>
		>
			<?php esc_html_e( 'Test the page', 'rocket' ); ?>
		</button>
		<?php if ( $data['is_draft'] ) : ?>
				<div class="wpr-tooltip">
					<div class="wpr-tooltip-content">
						<?php esc_html_e( 'This page is a draft and cannot be tested.', 'rocket' ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php // Store the limit message HTML hidden in the column for per-row usage by JS. ?>
		<div class="wpr-ri-limit-html" style="display: none;">
			<?php echo wp_kses_post( $data['wpr_limit_reached_message'] ); ?>
		</div>

		<div class="wpr-ri-message" style="display: none;"></div>
	</div>
	<?php
	return;
endif;

?>

<div class="wpr-ri-column" data-rocket-insights-id="<?php echo esc_attr( $data['wpr_rocket_row']->id ); ?>" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>" data-has-credit="<?php echo esc_attr( $data['wpr_has_credit'] ? '1' : '0' ); ?>" data-can-add-pages="<?php echo esc_attr( $data['wpr_can_add_pages'] && ! $data['is_draft'] ? '1' : '0' ); ?>" data-post-id="<?php echo esc_attr( $data['wpr_post_id'] ); ?>">
	<?php
	// Helper: always render the re-test button (JS will handle credit checks on click).
	$render_retest_button = function () use ( $data ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		?>
		<button type="button" class="wpr-ri-retest-link wpr-icon-bold-refresh" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>" data-post-id="<?php echo esc_attr( $data['wpr_post_id'] ); ?>" data-source="re-test post type listing">
			<?php esc_html_e( 'Re-test', 'rocket' ); ?>
		</button>
		<?php
	};

	// We keep the credit message HTML available (hidden) for JS to show it on click
	// for the specific row only. Do not render it on page load.
	$render_credit_message = function () use ( $data ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		?>
		<div class="wpr-ri-limit-html" style="display: none;">
			<?php echo wp_kses_post( $data['wpr_limit_reached_message'] ); ?>
		</div>
		<?php
	};
	?>
	<?php if ( $data['wpr_is_running'] ) : ?>
		<!-- Loading state -->
		<div class="wpr-ri-loading wpr-btn-with-tool-tip">
			<img class="wpr-loading-img" src="<?php echo esc_url( rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL', '' ) . 'orange-loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading...', 'rocket' ); ?>"/>
			<div class="wpr-tooltip">
				<div class="wpr-tooltip-content">
					<?php echo esc_html__( 'Analyzing your page (~1 min).', 'rocket' ); ?>
				</div>
			</div>
		</div>
	<?php elseif ( $data['wpr_has_results'] ) : ?>
		<!-- Results state -->
		<?php if ( $data['wpr_is_blurred'] ) : ?>
			<!-- Blurred score - show score with tooltip and actions (Re-test clickable, See Report disabled) -->
			<div class="wpr-ri-blurred">
				<div class="wpr-btn-with-tool-tip">
					<?php $this->render_performance_score( $data['wpr_score_data'] ); ?>
					<div class="wpr-tooltip">
						<div class="wpr-tooltip-content">
							<?php
							// translators: %s = human-readable time difference (e.g., "5 minutes").
							printf( esc_html__( 'Tested %s ago', 'rocket' ), esc_html( human_time_diff( $data['wpr_rocket_row']->modified, time() ) ) );
							?>
						</div>
					</div>
				</div>
				
				<div class="wpr-ri-actions-wrapper">
					<?php
					$render_retest_button();
					?>
					<span class="wpr-ri-see-report-link wpr-icon-report wpr-ri-disabled">
						<?php esc_html_e( 'See Report', 'rocket' ); ?>
					</span>
					<?php
					$render_credit_message();
					?>
				</div>
			</div>
		<?php else : ?>
			<!-- Normal score with actions -->
			<div class="wpr-ri-score-wrapper wpr-btn-with-tool-tip">
				<?php $this->render_performance_score( $data['wpr_score_data'] ); ?>
				<div class="wpr-tooltip">
					<div class="wpr-tooltip-content">
						<?php
						// translators: %s = human-readable time difference (e.g., "5 minutes").
						printf( esc_html__( 'Tested %s ago', 'rocket' ), esc_html( human_time_diff( $data['wpr_rocket_row']->modified, time() ) ) );
						?>
					</div>
				</div>
			</div>
			
			<div class="wpr-ri-actions-wrapper">
				<?php
				$render_retest_button();

				// See report link - only show if report_url exists.
				$wpr_report_url = $data['wpr_rocket_row']->report_url ?? ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

				if ( ! empty( $wpr_report_url ) && $data['wpr_can_access_report'] ) :
					?>
					<a href="<?php echo esc_url( $wpr_report_url ); ?>" class="wpr-ri-see-report-link wpr-icon-report" target="_blank" rel="noopener">
						<?php esc_html_e( 'See Report', 'rocket' ); ?>
					</a>
				<?php else : ?>
					<span class="wpr-ri-see-report-link wpr-icon-report wpr-ri-disabled">
						<?php esc_html_e( 'See Report', 'rocket' ); ?>
					</span>
					<?php
				endif;

				$render_credit_message();
				?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<!-- Failed or unknown state -->
		<div class="wpr-ri-score-wrapper">
			<div class="wpr-btn-with-tool-tip">
				<div class="wpr-percentage-indicator">
					<div class="wpr-percentage-circle status-red">
						<span class="wpr-failed-score wpr-icon-exclamation"></span>
					</div>
				</div>
				<div class="wpr-tooltip">
					<div class="wpr-tooltip-content">
						<?php esc_html_e( 'Something went wrong with this URL', 'rocket' ); ?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="wpr-ri-actions-wrapper">
			<?php
			$render_retest_button();
			?>
			<span class="wpr-ri-see-report-link wpr-icon-report wpr-ri-disabled">
				<?php esc_html_e( 'See Report', 'rocket' ); ?>
			</span>
			<?php $render_credit_message(); ?>
		</div>
	<?php endif; ?>
	
	<div class="wpr-ri-message" style="display: none;"></div>
</div>