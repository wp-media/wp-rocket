<?php
/**
 * Menu item template.
 *
 * @since 3.23.6
 *
 * @param array $data {
 *     Menu item data.
 *
 *     @type string $id               Menu item identifier.
 *     @type string $title            Menu item title.
 *     @type string $menu_description Menu item summary. Not displayed for sub-items.
 *     @type string $class            Class(es) to apply to the menu item.
 *     @type string $badge            Badge label. Optional.
 *     @type string $url              External URL. When set, the item opens it instead of switching page. Optional.
 *     @type string $target           Link target, used with `url`. Optional.
 *     @type bool   $is_sub           Whether the item is nested in a group. Optional.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_is_sub   = ! empty( $data['is_sub'] );
$rocket_is_link  = ! empty( $data['url'] );
$rocket_href     = $rocket_is_link ? $data['url'] : '#' . $data['id'];
$rocket_classes  = 'wpr-menuItem' . ( $rocket_is_sub ? ' wpr-menuItem--sub' : '' ) . ( ! empty( $data['class'] ) ? ' ' . $data['class'] : '' );
$rocket_rel_attr = $rocket_is_link && '_blank' === $data['target'] ? ' rel="noopener noreferrer"' : '';

?>
<a href="<?php echo esc_url( $rocket_href ); ?>" id="wpr-nav-<?php echo esc_attr( $data['id'] ); ?>" class="<?php echo esc_attr( $rocket_classes ); ?>"<?php echo $rocket_is_link && ! empty( $data['target'] ) ? ' target="' . esc_attr( $data['target'] ) . '"' : ''; ?><?php echo $rocket_rel_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static string. ?>>
	<?php if ( $rocket_is_sub ) : ?>
		<?php echo esc_html( $data['title'] ); ?>
	<?php else : ?>
		<span class="wpr-menuItem-icon" aria-hidden="true"></span>
		<span class="wpr-menuItem-text">
			<span class="wpr-menuItem-title">
				<?php echo esc_html( $data['title'] ); ?>
				<?php if ( ! empty( $data['badge'] ) ) : ?>
					<span class="wpr-badge wpr-badge--new"><?php echo esc_html( $data['badge'] ); ?></span>
				<?php endif; ?>
			</span>
			<span class="wpr-menuItem-description"><?php echo esc_html( $data['menu_description'] ); ?></span>
		</span>
	<?php endif; ?>
</a>
