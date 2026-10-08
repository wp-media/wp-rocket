<?php
/**
 * Account section template.
 *
 * @since 3.23.6
 *
 * @param array $data {
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
		<h2 class="wpr-title1 wpr-icon-user"><?php echo esc_html( $data['title'] ); ?></h2>
	</div>
</div>
