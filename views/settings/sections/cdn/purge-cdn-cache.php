<?php
/**
 * Purge CDN cache section template.
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
 *     @type string $purge_url   URL to purge the CDN cache.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-optionHeader <?php echo esc_attr( $data['class'] ); ?>">
	<h3 class="wpr-title2"><?php echo $data['title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?></h3>
	<?php if ( ! empty( $data['help'] ) ) : ?>
	<a href="<?php echo esc_url( $data['help']['url'] ); ?>" data-beacon-id="<?php echo esc_attr( $data['help']['id'] ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Settings" class="wpr-infoAction wpr-infoAction--help wpr-icon-help" target="_blank"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></a>
	<?php endif; ?>
</div>

<div class="wpr-cdn-purge <?php echo esc_attr( $data['class'] ?? '' ); ?>">
	<div class="wpr-cdn-purge__content">
		<div>
			<?php if ( ! empty( $data['title'] ) ) : ?>
			<p class="wpr-cdn-purge__title">
				<?php echo $data['title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
			</p>
			<?php endif; ?>
			<?php if ( ! empty( $data['description'] ) ) : ?>
			<p class="wpr-cdn-purge__description">
				<?php echo $data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
			</p>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $data['help'] ) ) : ?>
		<a href="<?php echo esc_url( $data['help']['url'] ); ?>" class="wpr-cdn-purge__learn-more" target="_blank" rel="noopener noreferrer">
			<span class="wpr-cdn-purge__info-icon"></span>
			<?php esc_html_e( 'Learn more about Purge', 'rocket' ); ?>
		</a>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $data['purge_url'] ) ) : ?>
	<a href="<?php echo esc_url( $data['purge_url'] ); ?>" class="wpr-cdn-purge__button">
		<span class="wpr-cdn-purge__button-icon"></span>
		<span>
			<?php
			// translators: %s is the CDN driver, wrapped in a span for JS targeting.
			printf( esc_html__( 'CLEAR ALL %s CACHE FILES', 'rocket' ), '<span class="rocketcdn-driver-js">ROCKETCDN</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</span>
	</a>
	<?php endif; ?>
</div>
