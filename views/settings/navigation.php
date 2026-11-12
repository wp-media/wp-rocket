<?php
/**
 * Menu template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of menu entries.
 *
 *     @type string  $type             Entry type: `item` for a standalone link, `group` for a collapsible parent.
 *     @type string  $id               Menu entry identifier.
 *     @type string  $title            Menu entry title.
 *     @type string  $menu_description Menu entry summary.
 *     @type string  $class            Class(es) to apply to the menu item.
 *     @type array[] $children         Items nested under a group.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( ! rocket_valid_key() ) {
	return;
}

foreach ( $data as $rocket_entry ) {
	if ( 'group' !== $rocket_entry['type'] ) {
		echo $this->generate( 'navigation-item', $rocket_entry ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
		continue;
	}
	?>
	<div class="wpr-menuGroup">
		<button type="button" id="wpr-nav-group-<?php echo esc_attr( $rocket_entry['id'] ); ?>" class="wpr-menuGroup-trigger" aria-expanded="false" aria-controls="wpr-nav-group-<?php echo esc_attr( $rocket_entry['id'] ); ?>-items">
			<span class="wpr-menuItem-icon" aria-hidden="true"></span>
			<span class="wpr-menuItem-text">
				<span class="wpr-menuItem-title"><?php echo esc_html( $rocket_entry['title'] ); ?></span>
				<span class="wpr-menuItem-description"><?php echo esc_html( $rocket_entry['menu_description'] ); ?></span>
			</span>
			<span class="wpr-menuGroup-chevron" aria-hidden="true"></span>
		</button>
		<div class="wpr-menuGroup-items" id="wpr-nav-group-<?php echo esc_attr( $rocket_entry['id'] ); ?>-items">
			<div class="wpr-menuGroup-list">
				<?php
				foreach ( $rocket_entry['children'] as $rocket_child ) {
					echo $this->generate( 'navigation-item', $rocket_child + [ 'is_sub' => true ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
				}
				?>
			</div>
		</div>
	</div>
	<?php
}
