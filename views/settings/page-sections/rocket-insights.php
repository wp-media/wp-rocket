<?php
/**
 * Rocket insights section template.
 *
 * @since 3.20
 *
 * @param array {
 *     Section arguments.
 *
 *     @type string $id    Page section identifier.
 *     @type string $title Page section title.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-rocket-insights-hover"><?php echo esc_html( $data['title'] ); ?></h2>
	</div>
	<div class="wpr-sectionHeader-description wpr-without-padding">
			<?php esc_html_e( 'Rocket Insights is powered by GTmetrix to run tests and generate your performance results and recommendations.', 'rocket' ); ?>
		</div>

	<?php do_action( 'rocket_insights_tab_content' ); ?>
	<?php $this->render_settings_sections( $data['id'] ); ?>
</div>

