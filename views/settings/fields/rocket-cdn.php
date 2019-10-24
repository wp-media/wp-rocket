<?php
/**
 * Rocket CDN template.
 *
 * @since 3.5
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$cnames      = get_rocket_option( 'cdn_cnames' );
$cnames_zone = get_rocket_option( 'cdn_zone' );
?>
<div class="wpr-fieldsContainer-fieldset">
	<div class="wpr-field">
		<div class="wpr-field-description-label">
			<?php echo $data['label']; ?>
		</div>
		<?php if ( ! empty( $data['description'] ) ) : ?>
			<div class="wpr-field-description">
				<?php echo $data['description']; ?>
			</div>
		<?php endif; ?>
		<div id="wpr-cnames-list">
		<?php
		if ( $cnames ) :
			foreach ( $cnames as $key => $url ) :
				?>
				<div class="wpr-text">
					<input type="text" name="wp_rocket_settings[cdn_cnames][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $url ); ?>" placeholder="cdn.example.com" />
					<input type="hidden" name="wp_rocket_settings[cdn_zone][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $cnames_zone[ $key ] ); ?>" />
					<?php if ( ! empty( $data['helper'] ) ) : ?>
					<div class="wpr-field-description wpr-field-description-helper">
						<?php echo $data['helper']; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
		<?php endif; ?>
		</div>
	</div>
</div>
