<?php
/**
 * Built-in CDN section template.
 *
 * Displays the page list for built-in CDN with add page functionality.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Section data.
 *
 *     @type string $id          Section identifier.
 *     @type string $title       Section title.
 *     @type string $description Section description.
 *     @type string $class       Section classes.
 *     @type string $help        Data to pass to beacon.
 *     @type string $page        Page section identifier.
 *     @type array  $status_indicator_data Data for the CDN status indicator partial.
 *     @type int    $pages_count Number of pages added to RocketCDN.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-optionHeader wpr-optionHeader--cdn-driver <?php echo esc_attr( $data['class'] ); ?>">
	<div class="wpr-optionHeader__title-group">
		<h3 class="wpr-title2 wpr-title2--orange"><?php echo esc_html( $data['title'] ); ?></h3>
		<span class="wpr-badge wpr-badge--grey"><?php esc_html_e( 'Free', 'rocket' ); ?></span>
	</div>
	<?php if ( ! empty( $data['help'] ) ) : ?>
	<a href="<?php echo esc_url( $data['help']['url'] ); ?>" data-beacon-id="<?php echo esc_attr( $data['help']['id'] ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Settings" class="wpr-infoAction wpr-infoAction--help wpr-icon-help" target="_blank"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></a>
	<?php endif; ?>
</div>

<?php
/**
 * Fires before the CDN status indicator.
 *
 * @since 3.22
 */
do_action( 'rocket_cdn_free_before_status_indicator' );
?>

<div class="wpr-cdn-built-in <?php echo esc_attr( $data['class'] ); ?>">
	<?php

	$this->render_parts_with_data( 'cdn/cdn-status-indicator', $data['status_indicator_data'] );

	?>
	<div class="wpr-cdn-built-in__separator"></div>
	<?php
	/**
	 * Fires to render the built-in CDN page list table.
	 *
	 * @since 3.22
	 */
	do_action( 'rocket_cdn_free_page_list' );
	?>

	<div class="wpr-cdn-add-page">
		<div class="wpr-cdn-add-page__input-wrap">
			<input type="text" id="wpr_cdn_add_page_input" placeholder="<?php esc_attr_e( 'Enter a page URL to add to RocketCDN', 'rocket' ); ?>" />
			<?php if ( 0 === $data['pages_count'] ) : ?>
			<button type="button" class="wpr-cdn-add-page__homepage">
				<span class="wpr-cdn-add-page__icon"></span>
				<?php esc_html_e( 'ADD HOMEPAGE', 'rocket' ); ?>
			</button>
			<?php endif; ?>
		</div>
		<button type="button" class="wpr-cdn-add-page__button">
			<span class="wpr-icon-plus"></span>
			<?php esc_html_e( 'ADD PAGE', 'rocket' ); ?>
		</button>
	</div>

	<?php
	/**
	 * Fires after the built-in CDN page list.
	 *
	 * Used to display the upsell banner.
	 *
	 * @since 3.22
	 */
	do_action( 'rocket_after_built_in_cdn_list' );
	?>
</div>
