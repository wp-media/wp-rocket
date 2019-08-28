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
			'7seqacq2ol' => __( 'What WP Rocket Does For You By Default', 'rocket' ),
			'fj42vucf99' => __( 'Finding the best settings for your site', 'rocket' ),
			'z1qxl7s2zn' => __( 'How to check if WP Rocket is caching your site', 'rocket' ),
			'j042jylrre' => __( 'How to measure the speed of your site', 'rocket' ),
		],
	],
	[
		'title'     => __( 'File Optimization', 'rocket' ),
		'tutorials' => [
			
		],
	],
	[
		'title'     => __( 'Preload', 'rocket' ),
		'tutorials' => [

		],
	],
	[
		'title'     => __( 'Add-ons', 'rocket' ),
		'tutorials' => [

		],
	],
];
?>
<div id="tutorials" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-tutorial-hover"><?php esc_html_e( 'Tutorials', 'rocket' ); ?></h2>
	</div>
</div>
