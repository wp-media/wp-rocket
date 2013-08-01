<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Link to the configuration page of the plugin
 *
 * Since 1.0
 *
 */

add_filter( 'plugin_action_links_'.plugin_basename( WP_ROCKET_FILE ), 'rocket_settings_action_links' );
function rocket_settings_action_links( $actions )
{
    array_unshift( $actions, '<a href="' . admin_url( 'options-general.php?page=wprocket' ) . '">' . __( 'Settings' ) . '</a>' );
    return $actions;
}



/**
 * Add some informations about authors in plugins list area
 *
 * Since 1.0
 *
 */

add_filter( 'plugin_row_meta', 'rocket_plugin_row_meta', 10, 2 );
function rocket_plugin_row_meta( $plugin_meta, $plugin_file )
{
	if( plugin_basename( WP_ROCKET_FILE ) == $plugin_file ):
		$last = end( $plugin_meta );
		$plugin_meta = array_slice( $plugin_meta, 0, -2 );
		$a = array();
		$authors = array(// array(	'name'=>'WP-Rocket', 'url'=>'http://wp-rocket.me' ),
						array( 	'name'=>'Jonathan Buttigieg', 'url'=>'http://www.geekpress.fr' ),
						array( 	'name'=>'Julio Potier', 'url'=>'http://www.boiteaweb.fr' ),
						array( 	'name'=>'Jean-Baptiste Marchand-Arvier', 'url'=>'http://jbma.me/blog/' ),
					);
		foreach( $authors as $author )
			$a[] = '<a href="' . $author['url'] . '" title="' . esc_attr__( 'Visit author homepage' ) . '">' . $author['name'] . '</a>';
		$a = sprintf( __( 'By %s' ), wp_sprintf( '%l', $a ) );
		$plugin_meta[] = $a;
		$plugin_meta[] = $last;
	endif;
	return $plugin_meta;
}



/**
 * Add a link "Purge this cache" in the post edit area
 *
 * since 1.0
 *
 */

add_filter( 'page_row_actions', 'rocket_row_actions', 10, 2 );
add_filter( 'post_row_actions', 'rocket_row_actions', 10, 2 );
function rocket_row_actions( $actions, $post )
{
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-'.$post->ID ), 'purge_cache_post-'.$post->ID );
    $actions['rocket_purge'] = '<a href="'.$url.'">Purger le cache</a>';
    return $actions;
}



/**
 * Add a link "Purge cache" in the post submit area
 *
 * since 1.0
 *
 */

add_action( 'post_submitbox_start', 'rocket_post_submitbox_start' );
function rocket_post_submitbox_start()
{
	global $post;
	if ( current_user_can( 'edit_post', $post->ID ) )
		echo '<div id="purge-action"><a class="button-secondary" href="'.wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-' . $post->ID ), 'purge_cache_post-' . $post->ID ).'">'.__( 'Purge cache', 'wp-rocket' ).'</a></div>';
}

/**
 * Add the scripts for the JS deffered files fields module box
 *
 * since 1.1.0
 *
 */

add_action( 'admin_print_footer_scripts', 'rocket_script_settings_page', PHP_INT_MAX );
function rocket_script_settings_page()
{
	global $current_screen;
	if( $current_screen->id != 'settings_page_wprocket' )
		return;
?>
	<script>
	jQuery( document ).ready( function($){
		function rocket_rename()
		{
			$('#rktdrop .rktdrag').each( function(i){
				var $item_t_input = $(this).find( 'input[type=text]' );
				var $item_c_input = $(this).find( 'input[type=checkbox]' );
				$($item_t_input).attr( 'name', '<?php echo WP_ROCKET_SLUG; ?>[deferred_js_files]['+i+']' );
				$($item_c_input).attr( 'name', '<?php echo WP_ROCKET_SLUG; ?>[deferred_js_wait]['+i+']' );
			});
		}
		$('#rktdrop').sortable({
			update : function(){ rocket_rename(); },
			axis: "y",
			items: ".rktdrag",
			containment: "parent",
			cursor: "move",
			handle: ".rktmove",
			forcePlaceholderSize: true,
			dropOnEmpty: false,
			placeholder: 'sortable-placeholder',
			tolerance: 'pointer',
			revert: true,
		});
		$('#rktclone').on('click', function(e){
			e.preventDefault();
			if( $('#rktdrop .rktdrag:last input[type=text]').val()=='' )
				return;
			var $item = $('.rktmodel:last').clone().appendTo('#rktdrop').removeClass('rktmodel').show();
			rocket_rename();
		} );
		$('.rktdelete').css('cursor','pointer').on('click', function(e){
			e.preventDefault();
			$(this).parent().css('background-color','red' ).slideUp( 'slow' , function(){$(this).remove(); } );
		} );
	} );
	</script>
<?php
}

/**
 * Add some CSS to display the dismiss cross
 *
 * since 1.1.10
 *
 */

add_action( 'admin_print_styles', 'rocket_admin_print_styles' );
function rocket_admin_print_styles()
{ ?>
<style>.rocket_cross{float: right !important; cursor: pointer;}</style>
<?php
 }

/**
 * Manage the dismissed boxes
 *
 * since 1.1.10
 *
 */

add_action( 'admin_post_rocket_ignore', 'rocket_dismiss_boxes' );
function rocket_dismiss_boxes()
{
	if( isset( $_GET['box'], $_GET['_wpnonce'] ) ) {

		if( !wp_verify_nonce( $_GET['_wpnonce'], $_GET['action'] . '_' . $_GET['box'] ) )
			wp_nonce_ays( '' );

		global $current_user;
		$actual = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		update_user_meta( $current_user->ID, 'rocket_boxes', array_filter( array_merge( (array)$actual, array( $_GET['box'] ) ) ) );

		wp_redirect( wp_get_referer() );
		die();

	}
}

