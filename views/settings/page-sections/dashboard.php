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

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<div class="wpr-sectionHeader">
	<h2 class="wpr-title1 wpr-icon-home"><?php echo $data['title']; ?></h2>
</div>

<h3><?php esc_html_e( 'My account', 'rocket' ); ?></h3>
<?php
$this->render_action_button( 'button', 'refresh_account', [
	'label' => __( 'Refresh info', 'rocket' ),
] );
?>
<?php esc_html_e( 'License' ); ?>
<?php esc_html_e( 'Expiration date' ); ?>
<?php
$this->render_action_button( 'link', 'view_account', [
	'label'      => __( 'View my account', 'rocket' ),
	'icon'       => '',
	'attributes' => [
		'target' => '_blank',
		'class'  => '',
	],
] );
?>
<h3><?php esc_html_e( 'Quick Actions', 'rocket' ); ?></h3>
<h4><?php esc_html_e( 'Remove all cached files', 'rocket' ); ?></h4>
<?php
$this->render_action_button( 'link', 'purge_cache', [
	'label'      => __( 'Clear cache', 'rocket' ),
	'icon'       => '',
	'parameters' => [
		'type' => 'all',
	],
] );
?>
<h4><?php esc_html_e( 'Start cache preloading', 'rocket' ); ?></h4>
<?php
$this->render_action_button( 'link', 'preload', [
	'label' => __( 'Preload cache', 'rocket' ),
	'icon'  => '',
] );
?>
<h4><?php esc_html_e( 'Purge OPCache content', 'rocket' ); ?></h4>
<?php
$this->render_action_button( 'link', 'rocket_purge_opcache', [
	'label' => __( 'Purge OPCache', 'rocket' ),
	'icon'  => '',
] );
?>
<?php
$this->render_settings_sections( $data['id'] );
?>
<h3><?php esc_html_e( 'Frequently Asked Questions', 'rocket' ); ?></h3>
<h4><?php esc_html_e( 'Still can not find a solution?', 'rocket' ); ?></h4>
<p><?php esc_html_e( 'Submit a ticket and get help from our friendly and knowledgeable Rocketeers.', 'rocket' ); ?></h4>
<?php
$this->render_action_button( 'button', 'ask_support', [
	'label' => __( 'Ask support', 'rocket' ),
	'icon'  => '',
] );
$this->render_documentation_block(); ?>
