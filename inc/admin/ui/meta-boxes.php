<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Add a link "Purge cache" in the post submit area
 *
 * @since 1.0
 * @todo manage all CPTs
 *
 */
add_action( 'post_submitbox_start', '__rocket_post_submitbox_start' );
function __rocket_post_submitbox_start()
{
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		global $post;
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-' . $post->ID ), 'purge_cache_post-' . $post->ID );
		printf( '<div id="purge-action"><a class="button-secondary" href="%s">%s</a></div>', $url, __( 'Clear cache', 'rocket' ) );
	}
}

/**
 * Add "Cache options" metabox
 *
 * @since 2.5
 *
 */
add_action( 'add_meta_boxes', '__rocket_cache_options_meta_boxes' );
function __rocket_cache_options_meta_boxes() {
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		$cpts = get_post_types( array( 'public' => true ), 'objects' );
		unset( $cpts['attachment'] );
		
		foreach( $cpts as $cpt => $cpt_object ) {
			$label = $cpt_object->labels->singular_name;
			add_meta_box( 'rocket_post_exclude', sprintf( __( 'Cache Options', 'rocket' ), $label ), '__rocket_display_cache_options_meta_boxes', $cpt, 'side', 'core' );
		}
	}
}

/*
 * Displays some checkbox to de/activate some cache options
 *
 * @since 2.5
 */
function __rocket_display_cache_options_meta_boxes() {
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		global $post;
		wp_nonce_field( 'rocket_box_option', '_rocketnonce', false, true );
		?>

		<div class="misc-pub-section">
			<p><?php _e( 'Activate these options on this post:', 'rocket' ) ;?></p>
			<?php
			$fields = array(
				'lazyload'  	   => __( 'Images LazyLoad', 'rocket' ),
				'lazyload_iframes' => __( 'Iframes & Videos LazyLoad', 'rocket' ),
				'minify_html'      => __( 'HTML Minification', 'rocket' ),
				'minify_css'       => __( 'CSS Minification', 'rocket' ),
				'minify_js'        => __( 'JS Minification', 'rocket' ),
				'cdn'              => __( 'CDN', 'rocket' ),
			);

			foreach ( $fields as $field => $label ) {
				$disabled = disabled( ! get_rocket_option( $field ), true, false );
				$title    = $disabled ? ' title="' . sprintf( __( 'Activate first the %s option.', 'rocket' ), esc_attr( $label ) ) . '"' : '';
				$class    = $disabled ? ' class="rkt-disabled"' : '';
				$checked   = ! $disabled ? checked( ! get_post_meta( $post->ID, '_rocket_exclude_' . $field, true ), true, false ) : '';
 				?>

				<input name="rocket_post_exclude_hidden[<?php echo $field; ?>]" type="hidden" value="on">
				<input name="rocket_post_exclude[<?php echo $field; ?>]" id="rocket_post_exclude_<?php echo $field; ?>" type="checkbox"<?php echo $title; ?><?php echo $checked; ?><?php echo $disabled; ?>>
				<label for="rocket_post_exclude_<?php echo $field; ?>"<?php echo $title; ?><?php echo $class; ?>><?php echo $label; ?></label><br>

				<?php
			}
			?>

			<p class="rkt-note"><?php _e( '<strong>Note:</strong> These options aren\'t applied if you added this post in the "Never cache the following pages" option.', 'rocket' ); ?></p>
		</div>

	<?php
	}
}

/*
 * Manage the cache options from the metabox.
 *
 * @since 2.5
 */
add_action( 'save_post', '__rocket_save_metabox_options' );
function __rocket_save_metabox_options() {
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) &&
		isset( $_POST['post_ID'], $_POST['rocket_post_exclude_hidden'], $_POST['_rocketnonce'] ) ) {

		check_admin_referer( 'rocket_box_option', '_rocketnonce' );

		$fields = array( 'lazyload', 'lazyload_iframes', 'minify_html', 'minify_css', 'minify_js', 'cdn' );

		foreach ( $fields as $field ) {
			if ( isset( $_POST['rocket_post_exclude_hidden'][ $field ] ) && $_POST['rocket_post_exclude_hidden'][ $field ] ) {
				if ( isset( $_POST['rocket_post_exclude'][ $field ] ) ) {
					delete_post_meta( $_POST['post_ID'], '_rocket_exclude_' . $field );
				} else {
					if ( get_rocket_option( $field ) ) {
						update_post_meta( $_POST['post_ID'], '_rocket_exclude_' . $field, true );
					}
				}
			}
		}
	}
}