<?php
/**
 * Quick Actions partial template.
 *
 * @since 3.17
 */

defined( 'ABSPATH' ) || exit;

$rocket_manual_preload = (bool) get_rocket_option( 'manual_preload', false );
?>
<div class="wpr-optionHeader wpr-quick-actions-header">
	<h3 class="wpr-title2"><?php esc_html_e( 'Quick Actions', 'rocket' ); ?></h3>
</div>

<div class="wpr-fieldsContainer wpr-quick-actions-container">
	<fieldset class="wpr-fieldsContainer-fieldset">
		<?php if ( current_user_can( 'rocket_purge_cache' ) ) : ?>
		<div class="wpr-field">
			<h4 class="wpr-title3"><?php esc_html_e( 'Cache Files', 'rocket' ); ?></h4>
			<p><?php echo $rocket_manual_preload ? esc_html__( 'Clear and preload all the cache files.', 'rocket' ) : esc_html__( 'Clear all the cache files.', 'rocket' ); ?></p>
			<?php
			$this->render_action_button(
				'link',
				'purge_cache',
				[
					'label'      => $rocket_manual_preload ? __( 'Clear and preload', 'rocket' ) : __( 'Clear', 'rocket' ),
					'parameters' => [
						'type' => 'all',
					],
					'attributes' => [
						'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-trash wpr-button--no-min-width',
					],
				]
			);
			?>
		</div>
		<?php endif; ?>
		<?php if ( 'local' !== wp_get_environment_type() && get_rocket_option( 'async_css' ) && apply_filters( 'do_rocket_critical_css_generation', true ) && current_user_can( 'rocket_regenerate_critical_css' ) ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound ?>
		<div class="wpr-field">
			<h4 class="wpr-title3"><?php esc_html_e( 'Regenerate Critical CSS', 'rocket' ); ?></h4>
			<?php
			$this->render_action_button(
				'link',
				'rocket_generate_critical_css',
				[
					'label'      => __( 'Regenerate Critical CSS', 'rocket' ),
					'attributes' => [
						'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-refresh',
					],
				]
			);
			?>
		</div>
		<?php endif; ?>

		<?php
		/**
		 * Fires in the dashboard actions column
		 *
		 * @since 3.16
		 */
		do_action( 'rocket_dashboard_actions' );
		?>
	</fieldset>
</div>
