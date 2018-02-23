<?php
/**
 * Documentation block template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<h3><?php esc_html_e( 'Documentation', 'rocket' ); ?></h3>
<p><?php esc_html_e( 'It is a great starting point to fix some of the most common issues.', 'rocket' ); ?></p>
<?php
$this->render_action_button( 'link', 'documentation', [
	'label'      => __( 'Read the documentation', 'rocket' ),
	'attributes' => [
		'target' => '_blank',
	],
] );
