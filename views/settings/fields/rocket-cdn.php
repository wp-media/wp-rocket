<?php
/**
 * RocketCDN template.
 *
 * @since 3.5
 */

defined( 'ABSPATH' ) || exit;

$rocket_cnames      = get_rocket_option( 'cdn_cnames' );
$rocket_cnames_zone = get_rocket_option( 'cdn_zone' );
?>
<div class="wpr-fieldsContainer-fieldset">
	<div class="wpr-field">
		<div class="wpr-field-description-label">
			<?php echo $data['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
		</div>
		<?php if ( ! empty( $data['description'] ) ) : ?>
			<div class="wpr-field-description">
				<?php echo $data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
			</div>
		<?php endif; ?>
		<div id="wpr-cnames-list">
		<?php
		if ( $rocket_cnames ) :
			foreach ( $rocket_cnames as $key => $url ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				?>
				<div class="wpr-text">
					<label for="cdn_cnames_<?php echo esc_attr( $key ); ?>" class="screen-reader-text"><?php esc_html_e( 'CDN CNAME', 'rocket' ); ?></label>
					<input type="text" id="cdn_cnames_<?php echo esc_attr( $key ); ?>" name="wp_rocket_settings[cdn_cnames][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $url ); ?>" placeholder="cdn.example.com" />
					<input type="hidden" name="wp_rocket_settings[cdn_zone][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $rocket_cnames_zone[ $key ] ); ?>" />
					<?php if ( ! empty( $data['helper'] ) ) : ?>
					<div class="wpr-field-description wpr-field-description-helper">
						<?php echo $data['helper']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
					</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="wpr-text">
				<label for="cdn_cnames" class="screen-reader-text"><?php esc_html_e( 'CDN CNAME', 'rocket' ); ?></label>
				<input type="text" id="cdn_cnames" name="wp_rocket_settings[cdn_cnames][]" value="" placeholder="xxxxxx.rocketcdn.me" />
				<input type="hidden" name="wp_rocket_settings[cdn_zone][]" value="all" />
				<?php if ( ! empty( $data['helper'] ) ) : ?>
				<div class="wpr-field-description wpr-field-description-helper">
					<?php echo $data['helper']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
				</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		</div>
	</div>
</div>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php esc_html_e( 'Purge RocketCDN cache', 'rocket' ); ?></h3>
</div>
<div class="wpr-fieldsContainer">
	<div class="wpr-fieldsContainer-description">
		<?php
		printf(
			// translators: %s is a "Learn more" link.
			esc_html__( 'Purges RocketCDN cached resources for your website. %s', 'rocket' ),
			'<a href="' . esc_url( $data['beacon']['url'] ) . '" data-beacon-article="' . esc_attr( $data['beacon']['id'] ) . '" rel="noopener noreferrer" target="_blank">' . esc_html__( 'Learn more', 'rocket' ) . '</a>'
		);
		?>
	</div><br>
	<?php
	$this->render_action_button(
		'link',
		'rocket_purge_rocketcdn',
		[
			'label'      => __( 'Clear all RocketCDN cache files', 'rocket' ),
			'attributes' => [
				'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-trash',
			],
		]
	);
	?>
</div>
