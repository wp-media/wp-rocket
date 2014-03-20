<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Enqueue pointers script and style if needed
 *
 * @since 1.3.0
 *
 */

add_action( 'admin_enqueue_scripts', 'rocket_admin_pointers_header' );
function rocket_admin_pointers_header()
{
   if ( rocket_admin_pointers_check() ) 
   {
      add_action( 'admin_print_footer_scripts', 'rocket_admin_pointers_footer' );
      wp_enqueue_script( 'wp-pointer' );
      wp_enqueue_style( 'wp-pointer' );
   }
}



/**
 * The pointers checker function
 *
 * @since 1.3.0
 *
 */

function rocket_admin_pointers_check()
{
	$admin_pointers = rocket_admin_pointers();
	foreach ( $admin_pointers as $pointer => $array )
	{
		if ( $array['active'] )
			return true;
	}
}




/**
 * The pointers core for WP Rocket
 *
 * @since 1.3.0
 *
 */

function rocket_admin_pointers_footer()
{
	global $pagenow, $current_screen;
	$admin_pointers = rocket_admin_pointers();
	?>
	
	<script type="text/javascript">
	/* <![CDATA[ */
	( function($) {
	<?php
	foreach ( $admin_pointers as $pointer => $array ) {
		$ai = isset( $array['anchor_id'][$pagenow] ) ? $array['anchor_id'][$pagenow] : $array['anchor_id']['all'];
		$ai = isset( $array['anchor_id'][$current_screen->base] ) ? $array['anchor_id'][$current_screen->base] : $ai;
		if( !empty( $ai ) ) {
			?>
			$( '<?php echo $ai; ?>' ).pointer( {
				content: '<?php echo addslashes( $array['content'] ); ?>',
				position: {
					edge: '<?php echo $array['edge']; ?>',
					align: '<?php echo $array['align']; ?>'
				},
				<?php if( !empty( $array['action'] ) ): ?>
					close: function() {
						$.post( ajaxurl, {
							pointer: '<?php echo $pointer; ?>',
							action: '<?php echo $array['action']; ?>',
						} );
					},
				<?php endif; ?>
			} ).pointer( 'open' );
			<?php
		}
	}
	?>
	} )(jQuery);
	/* ]]> */
	</script>
	
   <?php
}



/**
 * Get all pointers for WP Rocket
 *
 * @since 1.3.0
 *
 */

function rocket_admin_pointers()
{
   $dismissed   = explode( ',', (string)get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
   $pointer_key = WP_ROCKET_SLUG.'_'.str_replace( '.', '_', WP_ROCKET_VERSION ).'_';
   $new_pointer = array();
   $actions     = apply_filters( 'rocket_pointer_actions', array() );
   
   foreach( $actions as $action => $options ) 
   {

	   if( apply_filters( 'rocket_pointer_'.$action, false ) && !in_array( $pointer_key.$action, $dismissed ) ) 
	   {
		   
		   $new_pointer[$pointer_key.$action] = array(
		         'anchor_id' => $options['anchor_id'],
		         'edge'      => $options['edge'],
		         'align'     => $options['align'],
		         'active'    => true,
		         'action'    => $options['action'],
		   );
		   $new_pointer[$pointer_key.$action]['content'] = '<h3>'.WP_ROCKET_PLUGIN_NAME.'</h3><p>' . $options['content'] . '</p>';
		   
		}
   }
	
   return $new_pointer;
}




/**
 * Add APIKEY pointer
 *
 * @since 1.3.0
 *
 */

add_filter( 'rocket_pointer_actions', 'rocket_pointer_apikey' );
function rocket_pointer_apikey( $pointers )
{
	
	$pointers['apikey'] = array(
		'anchor_id'	=> array( 
			'options-general.php' 	 => 'ul.wp-submenu li a[href$="page='.WP_ROCKET_PLUGIN_SLUG.'"]', 
			'settings_page_'.WP_ROCKET_PLUGIN_SLUG => ' ', 
			'all'					 =>'#menu-settings' 
		),
		'edge' 		=> 'left',
		'align'		=> 'right',
		'action'	=> '',
		'content'	=> sprintf( __( 'To finalize the installation and enjoy the performance provided by our plugin, thank you to fill <a href="%s">your API key</a>.', 'rocket' ), admin_url( 'options-general.php?page='.WP_ROCKET_PLUGIN_SLUG ) )
	);
	
	return $pointers;
}