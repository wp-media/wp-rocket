<?php
/**
 * Menu template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of page sections arrays.
 *
 *     @type string $id               Menu item identifier.
 *     @type string $title            Menu item title.
 *     @type string $menu_description Menu item summary.
 *     @type string $class            Class(es) to apply to the menu item.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<?php
if ( rocket_valid_key() ) {
	foreach ( $data as $section ) {
	?>
	<a href="#<?php echo esc_attr( $section['id'] ); ?>" id="wpr-nav-<?php echo esc_attr( $section['id'] ); ?>" class="wpr-menuItem <?php echo $section['class']; ?>">
		<div class="wpr-menuItem-title"><?php echo $section['title']; ?></div>
		<div class="wpr-menuItem-description"><?php echo $section['menu_description']; ?></div>
	</a>
	<?php
	}
}
