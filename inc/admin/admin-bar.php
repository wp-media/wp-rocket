<?php

/**
 * TO DO - Description
 *
 * since 1.0
 *
 */

add_action( 'post_submitbox_start', 'rocket_post_submitbox_start' );

function rocket_post_submitbox_start()
{
	global $post;
	if ( current_user_can( 'edit_post', $post->ID ) )
		echo '<div id="purge-action" class="ajaxme"><a class="button-secondary" href="'.wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-' . $post->ID ), 'purge_cache_post-' . $post->ID ).'">'.__( 'Purge cache', WP_ROCKET_TEXTDOMAIN ).'</a></div>';
}


/**
 * TO DO - Description
 *
 * since 1.0
 *
 */

add_action('admin_bar_menu', 'rocket_admin_bar', 500);
function rocket_admin_bar( $wp_admin_bar )
{
	$action = 'purge_cache';
	// Parent
    $wp_admin_bar->add_menu(array(
	    'id'    => 'wp-rocket',
	    'title' => 'WP Rocket',
	    'href'  => '#',
	));
		// Purge All
		$wp_admin_bar->add_menu(array(
			'parent'	=> 'wp-rocket',
			'id' 		=> 'purge-all',
			'title' 	=> sprintf( __( 'Full Purge <span class="count-cache" title="%1$d files">%1$d</span>', WP_ROCKET_TEXTDOMAIN ), rocket_count_cache_contents() ),
			'href' 		=> wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=all' ), $action.'_all' ),
			'meta' 		=> array( 'class'=>'ajaxme' )
		));

		if( is_admin() )
		{
			// Purge a post
			global $pagenow, $post;
			if( $post && $pagenow=='post.php' && isset( $_GET['action'], $_GET['post'] ) )
			{
				$cache_slug = str_replace( home_url( '/' ), '/', get_permalink( $post->ID ) );
				$wp_admin_bar->add_menu(array(
					'parent' => 'wp-rocket',
					'id' => 'purge-post',
					'title' => __( 'Purge this post', WP_ROCKET_TEXTDOMAIN ),
					'href' => wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=post-'.$post->ID ), $action.'_post-'.$post->ID ),
					'meta' => array( 'class'=>'ajaxme' )
				));
			}
		}
		else {
			// Purge this URL (frontend)
			$cache_slug = $_SERVER['REQUEST_URI'];
			$wp_admin_bar->add_menu(array(
				'parent' => 'wp-rocket',
				'id' => 'purge-url',
				'title' => __( 'Purge this URL', WP_ROCKET_TEXTDOMAIN ),
				'href' => wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=url' ), $action.'_url' ),
				'meta' => array( 'class'=>'ajaxme' )
			));
		}
}


/**
 * TO DO - Description
 *
 * since 1.0
 *
 */

add_action( 'wp_before_admin_bar_render', 'rocket_wp_before_admin_bar_render' );
function rocket_wp_before_admin_bar_render()
{ ?>

	<style>
		#wpadminbar .count-cache {
			background-color: #464646;
			border-radius: 10px;
			color: white;
			display: inline-block;
			font-size: 11px;
			font-weight: bold;
			height: 13px;
			line-height: 1.2em;
			margin-left: 2px;
			min-width: 12px;
			padding: 2px 3px;
			text-align: center;
		}
		#wp-admin-bar-wp-rocket .ajax_load {
			display:none;
		}
		#wp-admin-bar-wp-rocket .ajax_load, #wp-admin-bar-wp-rocket .bad_result, #wp-admin-bar-wp-rocket .good_result{
			float:right;
			padding-left:3px;
			position:relative;
		}
	</style>
	
	<script>
		jQuery(document).ready(function($){
			$('#wp-admin-bar-wp-rocket a:first').append('<span class="ajax_load"> <img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" /></span>');
			$('.ajaxme').on('click', 'a', function(e){
				e.preventDefault();
				var $t = $(this);
				$('#wp-admin-bar-wp-rocket .ajax_load').show();
				var $href = $($t).attr('href').replace('admin-post','admin-ajax');
				$.get($href).always(function(result){
					if( result == '-1' )
						$('#wp-admin-bar-wp-rocket a:first').append('<span class="bad_result"> <img src="<?php echo admin_url( '/images/no.png' ); ?>" /></span>').find('.bad_result').hide(6000, function(){$(this).remove();});
					else
						$('#wp-admin-bar-wp-rocket a:first').append('<span class="good_result"> <img src="<?php echo admin_url( '/images/yes.png' ); ?>" /></span>').find('.good_result').hide(4000, function(){$(this).remove();});
					$('#wp-admin-bar-wp-rocket .ajax_load').hide()
				});
			});
		});
	</script>
	
<?php
}