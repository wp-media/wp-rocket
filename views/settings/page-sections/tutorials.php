<?php
/**
 * Tutorials section template.
 *
 * @since 3.4
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$tutorials = [
	[
		'title'     => __( 'Getting Started', 'rocket' ),
		'tutorials' => [
			'7seqacq2ol' => __( 'Getting Started with WP Rocket', 'rocket' ),
			'fj42vucf99' => __( 'Finding the Best Settings for Your Site', 'rocket' ),
			'z1qxl7s2zn' => __( 'How to Check if WP Rocket is Caching Your Site', 'rocket' ),
			'j042jylrre' => __( 'How to Measure the Speed of Your Site', 'rocket' ),
		],
	],
	[
		'title'     => __( 'File Optimization', 'rocket' ),
		'tutorials' => [
			'frwm2xrksl' => __( 'Troubleshooting Display Issues with File Optimization', 'rocket' ),
			'95z0cb0yxb' => __( 'How to Find the Right JavaScript to Exclude', 'rocket' ),
			'9m1zg8p5wc' => __( 'How External Content Slows Your Site', 'rocket' ),
		],
	],
	[
		'title'     => __( 'Preload', 'rocket' ),
		'tutorials' => [
			'803tlui8oi' => __( 'How Preloading Works', 'rocket' ),
		],
	],
	[
		'title'     => __( 'Add-ons', 'rocket' ),
		'tutorials' => [
			'09kolaz9o0' => __( 'Set Up the Cloudflare Add-on', 'rocket' ),
		],
	],
];
?>
<div id="tutorials" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-tutorial-hover"><?php esc_html_e( 'Tutorials', 'rocket' ); ?></h2>
	</div>
	<div class="wpr-Page-row">
		<div class="wpr-Page-col">
	<?php foreach ( $tutorials as $section ) : ?>
	<div class="wpr-optionHeader">
		<h3 class="wpr-title2"><?php echo esc_html( $section['title'] ); ?></h3>
	</div>
	<div class="wpr-field wpr-tutorials-section">
	<?php foreach ( $section['tutorials'] as $id => $title ) : ?>
	<div class="wpr-tutorial-item">
	<script src="https://fast.wistia.com/embed/medias/<?php echo esc_attr( $id ); ?>.jsonp" async></script><script src="https://fast.wistia.com/assets/external/E-v1.js" async></script><div class="wistia_responsive_padding" style="padding:56.25% 0 0 0;position:relative;"><div class="wistia_responsive_wrapper" style="height:100%;left:0;position:absolute;top:0;width:100%;"><span class="wistia_embed wistia_async_<?php echo esc_attr( $id ); ?> popover=true popoverAnimateThumbnail=true videoFoam=true" style="display:inline-block;height:100%;position:relative;width:100%">&nbsp;</span></div></div>
	<h4 class="wpr-fieldsContainer-description"><?php echo esc_html( $title ); ?></h4>
	</div>
	<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
		</div>	
	</div>
</div>
