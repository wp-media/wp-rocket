<?php
/**
 * Generic table list partial.
 *
 * Renders a flexible div-based table with any number of columns per row.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Table list data.
 *
 *     @type string $id             Optional. HTML id attribute for the table container.
 *     @type string $class          Optional. Additional CSS classes for the container.
 *     @type string $rows_hook      Action hook name to fire for rendering rows (each row rendered via table-list-row partial).
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_container_classes = 'wpr-table-list';
if ( ! empty( $data['class'] ) ) {
	$rocket_container_classes .= ' ' . $data['class'];
}
?>
<div class="<?php echo esc_attr( $rocket_container_classes ); ?>"<?php echo ! empty( $data['id'] ) ? ' id="' . esc_attr( $data['id'] ) . '"' : ''; ?>>
	<?php
	if ( ! empty( $data['rows_hook'] ) ) {
		/**
		 * Fires to render the table list rows.
		 *
		 * @since 3.22
		 */
		do_action( $data['rows_hook'] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Hook name is passed via data.
	}
	?>
</div>
