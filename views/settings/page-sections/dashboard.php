<?php
/**
 * Dashboard section template.
 *
 * @since 3.0
 *
 * @param array {
 *     Section arguments.
 *
 *     @type string $id    Page section identifier.
 *     @type string $title Page section title.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_manual_preload   = (bool) get_rocket_option( 'manual_preload', false );
$rocket_boxes            = get_user_meta( get_current_user_id(), 'rocket_boxes', true );
$rocket_cdn_token        = get_option( 'rocketcdn_user_token', '' );
$rocket_box_is_dismissed = in_array( 'rocket_activation_notice', (array) $rocket_boxes, true );
$rocketcdn_paid_plan     = ! empty( $rocket_cdn_token ) && $data['is_rocketcdn_paid_user'];

/**
 * Filters showing rocketcdn banner message instead of welcome banner.
 *
 * @since 3.22
 *
 * @param bool $show_rocketcdn_banner Show rocketcdn banner, by default it's shown for not paid rocketcdn.
 */
$rocket_show_rocketcdn_banner = wpm_apply_filters_typed( 'boolean', 'rocket_show_rocketcdn_banner', ! $rocketcdn_paid_plan );

if ( ! $rocket_show_rocketcdn_banner ) {
	$rocket_hero_title       = __( 'Congratulations!', 'rocket' );
	$rocket_title            = esc_html__( 'WP Rocket is now activated and already working for you.', 'rocket' )
		. '<br>'
		. esc_html__( 'Your website should be loading faster now!', 'rocket' );
	$rocket_hero_description = esc_html__( 'To guarantee fast websites, WP Rocket automatically applies 80% of web performance best practices.', 'rocket' )
		. '<br>'
		. esc_html__( 'We also enable options that provide immediate benefits to your website.', 'rocket' );
} else {
	$rocket_hero_title       = __( 'NEW!', 'rocket' );
	$rocket_title            = esc_html__( 'You can now enable RocketCDN for free on up to 3 pages of your choice!', 'rocket' );
	$rocket_hero_description = esc_html__( 'RocketCDN serves your content from locations closer to your visitors, helping your top pages load faster around the world. Go to the Content Delivery tab, choose up to 3 pages, and add them to RocketCDN to improve their performance globally.', 'rocket' );
}
?>
<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<?php
	if ( ! $rocket_box_is_dismissed ) :
		?>
	<div class="wpr-notice">
		<div class="wpr-notice-container">
			<div class="wpr-notice-supTitle"><?php echo esc_html( $rocket_hero_title ); ?></div>
			<h2 class="wpr-notice-title">
				<?php echo wp_kses( $rocket_title, [ 'br' => [] ] ); ?>
			</h2>
			<div class="wpr-notice-description">
				<?php echo wp_kses( $rocket_hero_description, [ 'br' => [] ] ); ?>
			</div>
			<?php if ( ! empty( $rocket_cdn_token ) && ! empty( $data['rocket_insights_enabled'] ) ) : ?>
				<div class="wpr-notice-continue">
					<?php
					printf(
						// translators: %1$s = opening <strong> tag, %2$s = closing </strong> tag.
						esc_html__( 'Check the %1$sRocket Insights%2$s tab to track your top pages, quickly spot issues, and get in-depth insights to further optimize your website speed.', 'rocket' ),
						'<strong>',
						'</strong>'
					);
					?>
				</div>
			<?php endif; ?>
			<a id="wpr-congratulations-notice" class="wpr-notice-close wpr-icon-close rocket-dismiss" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box=rocket_activation_notice' ), 'rocket_ignore_rocket_activation_notice' ) ); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'rocket' ); ?></span></a>
		</div>
	</div>
		<?php
		// Mark the banner as seen on first render so it never shows again, even if it wasn't dismissed.
		rocket_dismiss_box( 'rocket_activation_notice' );
	endif;
	?>
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-home"><?php echo esc_html( $data['title'] ); ?></h2>
	</div>

	<?php
		/**
		 * Fires before displaying the dashboard tab content
		 *
		 * @since 3.7.4
		 */
		do_action( 'rocket_before_dashboard_content' );
	?>
	<div class="wpr-Page-row">
		<div class="wpr-Page-col">
			<?php
			/**
			 * Fires in the main column of the dashboard tab content.
			 *
			 * @since 3.23.6
			 */
			do_action( 'rocket_dashboard_content' );
			?>
		</div>

		<div class="wpr-Page-col wpr-Page-col--fixed">
			<?php
			/**
			 * Fires in the dashboard sidebar
			 */
			do_action( 'rocket_dashboard_sidebar' );

			$this->render_part( 'quick-actions' );

			$this->render_part( 'documentation' );
			?>
		</div>
	</div>
</div>
