<?php
defined( 'ABSPATH' ) || exit;

?>
<div class="wpr-optionHeader ">
	<h3 class="wpr-title2"><?php esc_html_e( 'Settings', 'rocket' ); ?></h3>
	<button data-beacon-id="<?php echo esc_attr( $data['help'] ?? '' ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Addons" class="wpr-infoAction wpr-infoAction--help wpr-icon-help"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></button>
</div>
<div class="wpr-fieldsContainer pma-settings-container">
<fieldset class="wpr-fieldsContainer-fieldset">
	<div class="wpr-field wpr-field--checkbox">
		<div class="wpr-checkbox">
			<input type="checkbox" id="<?php echo esc_html( $data['id'] ); ?>" class=""
					name="wp_rocket_settings['<?php echo esc_html( $data['id'] ); ?>']"
					value="1" <?php checked( $data['value'] ?? 0, 1 ); ?>>
			<label for="performance_monitoring" class="">
				<?php echo esc_html( $data['title'] ); ?>
			</label>
			<div class="wpr-tooltip">
				<div class="wpr-tooltip-content">
					<?php esc_html_e( 'Upgrade your plan to get access to automatic performance tests', 'rocket' ); ?>
				</div>
			</div>
		</div>
		<div class="wpr-field-description">
			<?php esc_html_e( 'Automatically test your tracked pages', 'rocket' ); ?>
		</div>
	</div>
	<div class="wpr-field--select">
		<div class="wpr-select">
			<select id="performance_monitoring_schedule_frequency"
					name="wp_rocket_settings[performance_monitoring_schedule_frequency]">
				<?php foreach ( $data['choices'] as $value => $label ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<option
						value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $data['schedule_frequency'] ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
</fieldset>
</div>
