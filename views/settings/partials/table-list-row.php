<?php
/**
 * Generic table list row partial.
 *
 * Renders a single row with any number of columns.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Row data.
 *
 *     @type string $class   Optional. Additional CSS class for the row.
 *     @type array  $columns Array of column data.
 *         @type string $content HTML content for the column.
 *         @type string $class   Optional. Additional CSS class for the column cell.
 *         @type string $type    Optional. Cell type: 'cell' (default), 'actions'. Maps to BEM element.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_row_classes = 'wpr-table-list__row';
if ( ! empty( $data['class'] ) ) {
	$rocket_row_classes .= ' ' . $data['class'];
}
?>
<div class="<?php echo esc_attr( $rocket_row_classes ); ?>">
	<?php
	if ( ! empty( $data['columns'] ) ) :
		foreach ( $data['columns'] as $rocket_column ) :
			$rocket_cell_type  = ! empty( $rocket_column['type'] ) ? $rocket_column['type'] : 'cell';
			$rocket_cell_class = 'wpr-table-list__' . $rocket_cell_type;
			if ( ! empty( $rocket_column['class'] ) ) {
				$rocket_cell_class .= ' ' . $rocket_column['class'];
			}
			?>
			<div class="<?php echo esc_attr( $rocket_cell_class ); ?>">
				<?php echo $rocket_column['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is pre-escaped by the caller. ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
