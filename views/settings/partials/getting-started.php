<?php
/**
 * Getting Started block template.
 *
 * @since 3.4
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$tutorials = [
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
			<?php foreach ( $tutorials as $id => $title ) : ?>
			<li class="wpr-icon-tutorial-alt"><script src="https://fast.wistia.com/embed/medias/<?php echo esc_attr( $id ); ?>.jsonp" async></script><script src="https://fast.wistia.com/assets/external/E-v1.js" async></script><span class="wpr-tutorial-link wistia_embed wistia_async_<?php echo esc_attr( $id ); ?> popover=true popoverContent=link" style="display:inline;position:relative"><?php echo esc_html( $title ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
