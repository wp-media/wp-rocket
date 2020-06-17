<?php
/**
 * Getting Started block template.
 *
 * @since 3.4
 */

defined( 'ABSPATH' ) || exit;

$rocket_tutorials = [
	'7seqacq2ol' => __( 'What WP Rocket Does For You By Default', 'rocket' ),
	'fj42vucf99' => __( 'Finding the Best Settings for Your Site', 'rocket' ),
	'z1qxl7s2zn' => __( 'How to Check if WP Rocket is Caching Your Site', 'rocket' ),
	'j042jylrre' => __( 'How to Measure the Speed of Your Site', 'rocket' ),
];
?>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php esc_html_e( 'Getting Started', 'rocket' ); ?></h3>
</div>
<div class="wpr-fieldsContainer-fieldset">
	<div class="wpr-field">
		<ul class="wpr-field-list">
			<?php foreach ( $rocket_tutorials as $rocket_tutorial_id => $rocket_tutorial_title ) : ?>
			<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
			<li class="wpr-icon-tutorial-alt"><script src="https://fast.wistia.com/embed/medias/<?php echo esc_attr( $rocket_tutorial_id ); ?>.jsonp" async></script><span class="wpr-tutorial-link wistia_embed wistia_async_<?php echo esc_attr( $rocket_tutorial_id ); ?> popover=true popoverContent=link" style="display:inline;position:relative"><?php echo esc_html( $rocket_tutorial_title ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
