<?php
/**
 * Rocket Insights column content for post listing pages.
 *
 * @since 3.20.1
 *
 * @var array $data {
 *     Template data.
 *
 *     @type string      $data['wpr_rocket_insights_url']        The URL of the post.
 *     @type object|null $data['wpr_rocket_row']        Database row object for the URL (null if not tracked).
 *     @type bool        $data['wpr_has_credit'] Whether the user has credit available.
 * }
 */

defined( 'ABSPATH' ) || exit;

// If row doesn't exist, show "Test the page" link.
if ( null === $data['wpr_rocket_row'] ) :
	$wpr_can_test = $data['wpr_has_credit'] && $data['wpr_can_add_pages'] && ! $data['is_draft']; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	?>
	<div class="wpr-ri-column wpr-ri-not-tracked" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>">
		<?php if ( $wpr_can_test ) : ?>
			<button type="button" class="wpr-ri-test-page" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>">
				<?php esc_html_e( 'Test the page', 'rocket' ); ?>
			</button>
		<?php else : ?>
			<button type="button" class="wpr-ri-test-page wpr-ri-no-credit" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>">
				<?php esc_html_e( 'Test the page', 'rocket' ); ?>
			</button>
			<?php if ( ! $data['wpr_can_add_pages'] && $data['wpr_is_free'] ) : ?>
				<div class="wpr-ri-credit-message">
					<?php
					printf(
						/* translators: %s: bolded text "reached your free limit" */
						esc_html__( "You've %s. Upgrade to continue.", 'rocket' ),
						'<strong>' . esc_html__( 'reached your free limit', 'rocket' ) . '</strong>'
					);
					?>
				</div>
			<?php elseif ( ! $data['wpr_can_add_pages'] && ! $data['wpr_is_free'] ) : ?>
				<div class="wpr-ri-credit-message">
					<?php
					printf(
						/* translators: %s: bolded text "reached the page limit" */
						esc_html__( "You've %s. Please remove at least one page to continue.", 'rocket' ),
						'<strong>' . esc_html__( 'reached the page limit', 'rocket' ) . '</strong>'
					);
					?>
				</div>
			<?php elseif ( ! $data['wpr_has_credit'] ) : ?>
				<div class="wpr-ri-credit-message">
					<?php
					printf(
						/* translators: %s: bolded text "reached your free limit" */
						esc_html__( "You've %s. Upgrade to continue.", 'rocket' ),
						'<strong>' . esc_html__( 'reached your free limit', 'rocket' ) . '</strong>'
					);
					?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<div class="wpr-ri-message" style="display: none;"></div>
	</div>
	<?php
	return;
endif;

?>

<div class="wpr-ri-column" data-rocket-insights-id="<?php echo esc_attr( $data['wpr_rocket_row']->id ); ?>" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>">
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
					<?php
					$this->render_performance_score( $data['wpr_score_data'] );
					?>
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
					<?php if ( $data['wpr_has_credit'] ) : ?>
						<button type="button" class="wpr-ri-retest-link wpr-icon-bold-refresh" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>">
							<?php esc_html_e( 'Re-test', 'rocket' ); ?>
						</button>
					<?php else : ?>
						<span class="wpr-ri-retest-link wpr-icon-bold-refresh wpr-ri-disabled">
							<?php esc_html_e( 'Re-test', 'rocket' ); ?>
						</span>
					<?php endif; ?>
					
					<span class="wpr-ri-see-report-link wpr-icon-report wpr-ri-disabled">
						<?php esc_html_e( 'See Report', 'rocket' ); ?>
					</span>
					
					<?php if ( ! $data['wpr_has_credit'] ) : ?>
						<span class="wpr-ri-no-credit-text">
							<?php
							printf(
								/* translators: %s: bolded text "reached your free limit" */
								esc_html__( "You've %s. Upgrade to continue.", 'rocket' ),
								'<strong>' . esc_html__( 'reached your free limit', 'rocket' ) . '</strong>'
							);
							?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<!-- Normal score with actions -->
			<div class="wpr-ri-score-wrapper wpr-btn-with-tool-tip">
				<?php
				$this->render_performance_score( $data['wpr_score_data'] );
				?>
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
				<?php if ( $data['wpr_has_credit'] ) : ?>
					<button type="button" class="wpr-ri-retest-link wpr-icon-bold-refresh" data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>">
						<?php esc_html_e( 'Re-test', 'rocket' ); ?>
					</button>
				<?php else : ?>
					<span class="wpr-ri-retest-link wpr-icon-bold-refresh wpr-ri-disabled">
						<?php esc_html_e( 'Re-test', 'rocket' ); ?>
					</span>
				<?php endif; ?>
				
				<?php
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
				<?php endif; ?>
				
				<?php if ( ! $data['wpr_has_credit'] ) : ?>
					<span class="wpr-ri-no-credit-text">
						<?php
						printf(
							/* translators: %s: bolded text "reached your free limit" */
							esc_html__( "You've %s. Upgrade to continue.", 'rocket' ),
							'<strong>' . esc_html__( 'reached your free limit', 'rocket' ) . '</strong>'
						);
						?>
					</span>
				<?php endif; ?>
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
			<?php if ( ! empty( $data['wpr_has_credit'] ) ) : ?>
				<button
					type="button"
					class="wpr-ri-retest-link wpr-icon-bold-refresh"
					data-url="<?php echo esc_attr( $data['wpr_rocket_insights_url'] ); ?>"
				>
					<?php esc_html_e( 'Re-test', 'rocket' ); ?>
				</button>
			<?php else : ?>
				<span class="wpr-ri-retest-link wpr-icon-bold-refresh wpr-ri-disabled">
					<?php esc_html_e( 'Re-test', 'rocket' ); ?>
				</span>
			<?php endif; ?>

			<span class="wpr-ri-see-report-link wpr-icon-report wpr-ri-disabled">
				<?php esc_html_e( 'See Report', 'rocket' ); ?>
			</span>

			<?php if ( empty( $data['wpr_has_credit'] ) ) : ?>
				<span class="wpr-ri-no-credit-text">
					<strong><?php esc_html_e( "You've reached your free limit.", 'rocket' ); ?></strong>
					<?php esc_html_e( 'Upgrade to continue.', 'rocket' ); ?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	
	<div class="wpr-ri-message" style="display: none;"></div>
</div>
