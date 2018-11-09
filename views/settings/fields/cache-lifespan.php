<?php
/**
 * Cache lifespan block template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$purge_interval = get_rocket_option( 'purge_cron_interval', 10 );
$purge_unit     = get_rocket_option( 'purge_cron_unit', 'HOUR_IN_SECONDS' );

?>

<div class="wpr-field--cache">
	<div class="wpr-field-description-label">
		<?php echo $data['label']; ?>
	</div>
	<?php if ( ! empty( $data['description'] ) ) : ?>
	<div class="wpr-field-description">
		<?php echo $data['description']; ?>
	</div>
	<?php endif; ?>
	<div class="wpr-field wpr-field--text wpr-field--number">
		<div class="wpr-text wpr-text--number">
			<label for="purge_cron_interval" class="screen-reader-text"><?php _e( 'Clear cache after', 'rocket' ); ?></label>
			<input type="number" min="0" id="purge_cron_interval" name="wp_rocket_settings[purge_cron_interval]" value="<?php echo esc_attr( $purge_interval ); ?>">
		</div>
	</div>
	<div class="wpr-field wpr-field--select">
		<div class="wpr-select">
			<select id="purge_cron_unit" name="wp_rocket_settings[purge_cron_unit]">
			<?php foreach ( $data['choices'] as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $purge_unit ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
			</select>
		</div>
	</div>
</div>
