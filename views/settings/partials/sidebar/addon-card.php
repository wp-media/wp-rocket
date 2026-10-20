<?php
/**
 * Dashboard sidebar add-on card template.
 *
 * One markup for every add-on card, rendered from the data filtered by rocket_dashboard_sidebar_addons.
 *
 * @since 3.24
 *
 * @param array $data {
 *     Card data.
 *
 *     @type string   $id           Unique card identifier.
 *     @type string   $title        Card title.
 *     @type array    $image        {
 *         @type string $src    Image URL, empty for no image.
 *         @type string $alt    Image alternative text, empty when decorative.
 *         @type int    $width  Optional image width.
 *         @type int    $height Optional image height.
 *     }
 *     @type string   $lead         Bold sentence under the image.
 *     @type string   $text         Text following the lead.
 *     @type string[] $list         Benefits shown when the card is expanded. Only <strong> is allowed.
 *     @type string   $button_label Button label.
 *     @type string   $button_url   Button URL.
 *     @type bool     $is_external  Whether the button opens an external site in a new tab.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_card = wp_parse_args(
	$data,
	[
		'id'           => '',
		'title'        => '',
		'image'        => [],
		'lead'         => '',
		'text'         => '',
		'list'         => [],
		'button_label' => '',
		'button_url'   => '',
		'is_external'  => false,
	]
);

$rocket_card_id    = sanitize_html_class( $rocket_card['id'] );
$rocket_card_list  = 'wpr-addonCard-list-' . $rocket_card_id;
$rocket_card_image = wp_parse_args(
	(array) $rocket_card['image'],
	[
		'src'    => '',
		'alt'    => '',
		'width'  => 0,
		'height' => 0,
	]
);
?>
<div class="wpr-addonCard wpr-addonCard--<?php echo esc_attr( $rocket_card_id ); ?>" id="wpr-addonCard-<?php echo esc_attr( $rocket_card_id ); ?>">
	<h4 class="wpr-addonCard-title"><?php echo esc_html( $rocket_card['title'] ); ?></h4>
	<?php if ( ! empty( $rocket_card_image['src'] ) ) : ?>
		<img class="wpr-addonCard-image" src="<?php echo esc_url( $rocket_card_image['src'] ); ?>" alt="<?php echo esc_attr( $rocket_card_image['alt'] ); ?>"<?php echo $rocket_card_image['width'] && $rocket_card_image['height'] ? ' width="' . esc_attr( (string) $rocket_card_image['width'] ) . '" height="' . esc_attr( (string) $rocket_card_image['height'] ) . '"' : ''; ?>>
	<?php endif; ?>
	<p class="wpr-addonCard-lead"><?php echo esc_html( $rocket_card['lead'] ); ?></p>
	<p class="wpr-addonCard-text">
		<?php echo esc_html( $rocket_card['text'] ); ?>
		<?php if ( ! empty( $rocket_card['list'] ) ) : ?>
			<button type="button" class="wpr-addonCard-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $rocket_card_list ); ?>" data-label-more="<?php esc_attr_e( 'Learn more', 'rocket' ); ?>" data-label-less="<?php esc_attr_e( 'Show less', 'rocket' ); ?>"><?php esc_html_e( 'Learn more', 'rocket' ); ?></button>
		<?php endif; ?>
	</p>
	<?php if ( ! empty( $rocket_card['list'] ) ) : ?>
		<ul class="wpr-addonCard-list" id="<?php echo esc_attr( $rocket_card_list ); ?>" hidden>
			<?php foreach ( $rocket_card['list'] as $rocket_card_item ) : ?>
				<li><?php echo wp_kses( $rocket_card_item, [ 'strong' => [] ] ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php if ( ! empty( $rocket_card['button_url'] ) ) : ?>
		<a class="wpr-button wpr-addonCard-button" href="<?php echo esc_url( $rocket_card['button_url'] ); ?>"<?php echo $rocket_card['is_external'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $rocket_card['button_label'] ); ?></a>
	<?php endif; ?>
</div>
