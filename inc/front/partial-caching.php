<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Add WP Rocket attribut in before_widget
 *
 * since 1.4.0
 *
 */
 
// add_filter( 'dynamic_sidebar_params', 'rocket_dynamic_sidebar_params' );
function rocket_dynamic_sidebar_params( $params )
{
    
    global $wp_registered_widgets;
    $widget_id = $params[0]['widget_id'];
    $widget_obj = $wp_registered_widgets[$widget_id];
    $widget_opt = get_option($widget_obj['callback'][0]->option_name);
    $widget_num = $widget_obj['params'][0]['number'];
    
    
    // On vérifie si le widget doit être mis en cache partiel
	if ( 	isset( $widget_opt[$widget_num]['rocket-partial-caching-active'], $widget_opt[$widget_num]['rocket-partial-caching-interval'], $widget_opt[$widget_num]['rocket-partial-caching-unit'] )
			&& (int)$widget_opt[$widget_num]['rocket-partial-caching-interval']>0 && (int)$widget_opt[$widget_num]['rocket-partial-caching-interval']==1 )
	{
		$interval = (int)( $widget_opt[$widget_num]['rocket-partial-caching-interval'] * constant( $widget_opt[$widget_num]['rocket-partial-caching-unit'] ) );
		$data_partial_caching = ' data-partial-caching-debug="'.$interval.','.time() + $interval.'"';
		// Si les widgets de la sidebar ne possède pas d'argument "before_widget",
		// On ajoute automatiquement l'id du widget et l'attribut data-partial-caching
		if( empty($params[0]['before_widget']) ) 
		{
			$params[0]['before_widget'] = '<div id="' . $widget_id . '" '.$data_partial_caching.'>';
			$params[0]['after_widget']  = '</div>';
		}
		else 
		{		
			// 
			if( preg_match( '/id=[\'"](.*)[\'"]/' , $params[0]['before_widget']) )
				$params[0]['before_widget'] = preg_replace( '/id=[\'"](.*)[\'"]/', '/id="$1"'.$data_partial_caching.'/', $params[0]['before_widget'] );	
			else
				$params[0]['before_widget'] = str_replace( '>', ' id="' . $widget_id . '"'.$data_partial_caching.'>', $params[0]['before_widget'] );		
		}
			
	}
    	    
    return $params;
}



/**
 * TO DO - Description
 *
 * since 1.4.0
 *
 */
 
add_action( 'wp_ajax_nopriv_rocket_get_refreshed_fragments', 'rocket_get_refreshed_fragments' );
add_action( 'wp_ajax_rocket_get_refreshed_fragments', 'rocket_get_refreshed_fragments' );
function rocket_get_refreshed_fragments() 
{
	if( !isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'WPRXMLHttpRequest' )
		return;

	global $wp_registered_sidebars, $wp_registered_widgets;
		
	$fragments = -1;
	
	// Get all sidebars
	$sidebars_widgets = wp_get_sidebars_widgets();
	
	// If no sidebar, return to get a coffee
	if ( empty( $sidebars_widgets ) )
			return false;
	
	foreach ( $sidebars_widgets as $sidebar_id => $widgets ) {
		
		unset( $sidebars_widgets['wp_inactive_widgets'] );
		
		$sidebar = $wp_registered_sidebars[$sidebar_id];
		
		foreach( $widgets as $widget_id ) {
			
			// Get widget basename (ex: recent-comments)
			$widget_basename = $wp_registered_widgets[$widget_id]['callback'][0]->id_base;
			
			// Get widgets save options
			$widget_options  = get_option( 'widget_' . $widget_basename ); 
			$widget_options  = $widget_options[$wp_registered_widgets[$widget_id]['params'][0]['number']];
			
			// 
			if( !isset( $widget_options['rocket-partial-caching-interval'] ) ||
				!isset( $widget_options['rocket-partial-caching-unit'] ) 	 ||
				!isset( $widget_options['rocket-partial-caching-active'] ) 	 ||
				(int)$widget_options['rocket-partial-caching-active']!=1 )
				continue;
			
			$interval = (int)( $widget_options['rocket-partial-caching-interval'] * constant( $widget_options['rocket-partial-caching-unit'] ) );
			if( isset( $widget_options['rocket-partial-caching-active'] ) && $widget_options['rocket-partial-caching-active']==1 &&
				( !isset( $_POST[$widget_id] ) || ( time() - $_POST[$widget_id] > 0 ) ) ) {

				$params = array_merge(
					array( array_merge( $sidebar, array('widget_id' => $widget_id, 'widget_name' => $wp_registered_widgets[$widget_id]['name']) ) ),
					(array) $wp_registered_widgets[$widget_id]['params']
				);
				
				//
				$callback = $wp_registered_widgets[$widget_id]['callback'];
				if ( is_callable($callback) ) 
				{
					if( !is_array( $fragments ) )
						$fragments = array();
					ob_start();
					call_user_func_array($callback, $params);
					$fragments[$widget_id]['content'] = ob_get_clean();
					$fragments[$widget_id]['interval'] = (int)(time() + $interval);
				}

			}
				
		}
	
	}
	unset( $_POST['action'] );
	if( !empty( $_POST ) ){
		$fragments = array_intersect_key( $fragments, $_POST );
		foreach( $_POST as $k=>$v )
			$_POST[$k] = array( 'content'=>'', 'interval'=>0 );
		$fragments = wp_parse_args( $fragments, $_POST );
	}
	// wp_die(var_dump($fragments));
	wp_send_json( $fragments );
}


/**
 * TO DO - Description
 *
 * since 1.4.0
 *
 */
 
// if( !defined( 'ROCKET_SCRIPT_DEBUG' ) )
// 	add_action( 'wp_footer', 'rocket_partial_caching_script_min', PHP_INT_MAX );
// else
	add_action( 'wp_footer', 'rocket_partial_caching_script', 0 );
function rocket_partial_caching_script()
{ 
	?>
	<script>
	var rocketAdminAJaxUrl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
	var sup_html5st = 'sessionStorage' in window && window['sessionStorage'] !== undefined;

	function _WPR_Ajax()
	{
        if(typeof XMLHttpRequest !== 'undefined')
        {
                var xhr = new XMLHttpRequest();  
        }      
	    else
	    {  
	        var versions = ["MSXML2.XmlHttp.5.0",  
	                        "MSXML2.XmlHttp.4.0",  
	                        "MSXML2.XmlHttp.3.0",  
	                        "MSXML2.XmlHttp.2.0",  
	                        "Microsoft.XmlHttp"]
	 
	         for(var i = 0, len = versions.length; i < len; i++)
	         {  
	            try
	            {  
	                var xhr = new ActiveXObject(versions[i]);  
	                break;  
	            }  
	            catch(e){}  
	         }
	    }  
       
        xhr.onreadystatechange = function()
        {
            if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
            {
                _WPR_cb.call( xhr, xhr.responseText );
            }
        }

        xhr.open( 'POST', rocketAdminAJaxUrl, true );
		xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
        xhr.setRequestHeader( 'X-Requested-With', 'WPRXMLHttpRequest' );
		var params = '';
		var postData = JSON.parse( sessionStorage.getItem( 'WPR_SS' ) );
		for(var key in postData){
			var value = postData[key];
			if(!postData.hasOwnProperty(value) ){
				params += key + '=' + encodeURIComponent(value['interval']) + '&';
			}
		}
		params += 'action=rocket_get_refreshed_fragments';

		xhr.send(params);

        return xhr;
	}
 
	function rocketRemover( arr, index ) {
	    return arr.splice( index, 1 );
	}

	function _WPR_cb( data )
	{
		if ( data != -1 ) {
			data = JSON.parse( data );
			for( var key in data ) {
				if( data[key]['content'] != '' ) {
					var elem = document.getElementById( key );
					if( elem != undefined && elem != null ) {
						document.getElementById(key).innerHTML = data[key]['content'];
					}
				}
			}

			if ( sup_html5st ) {
				var wp_rocket_fragments = JSON.parse( sessionStorage.getItem( 'WPR_SS' ) );
				for( var key in data ) {
					if( data[key]['content'] != '' && wp_rocket_fragments!=undefined) {
						wp_rocket_fragments[key]['content'] = data[key]['content'];
						wp_rocket_fragments[key]['interval'] = data[key]['interval'];
					}else if( data[key]['content'] == '' && wp_rocket_fragments!=undefined ){
						delete wp_rocket_fragments[key];
					}
				}
				try {
					if( wp_rocket_fragments==null )
						wp_rocket_fragments = data;
					sessionStorage.setItem( 'WPR_SS', JSON.stringify( wp_rocket_fragments ) );
				}
				catch( e ) {
					if( e == QUOTA_EXCEEDED_ERR ) {
					      throw "Quota exceeded!";
						}
				}
			}
		}
	}
	
	if ( sup_html5st )
	{
		var wp_rocket_fragments = JSON.parse( sessionStorage.getItem( 'WPR_SS' ) );

		if ( wp_rocket_fragments ) {
			
			for(var key in wp_rocket_fragments) {
				var elem = document.getElementById(key);
				if( elem != undefined && elem != null ) {
					elem.innerHTML = wp_rocket_fragments[key]['content'];							
				}
			}
			
		} 
	}

	_WPR_Ajax();
		
	</script>
<?php
}
/*
function rocket_partial_caching_script_min()
{ ?>
<script>var rocketAdminAJaxUrl="<?php echo admin_url( 'admin-ajax.php?action=rocket_get_refreshed_fragments' ); ?>";
function e(){var a;if("undefined"!==typeof XMLHttpRequest)a=new XMLHttpRequest;else for(var c=["MSXML2.XmlHttp.5.0","MSXML2.XmlHttp.4.0","MSXML2.XmlHttp.3.0","MSXML2.XmlHttp.2.0","Microsoft.XmlHttp"],b=0,d=c.length;b<d;b++)try{a=new ActiveXObject(c[b]);break}catch(k){}a.onreadystatechange=function(){4!=a.readyState||200!=a.status&&0!=a.status||f.call(a,a.responseText)};a.open("GET",rocketAdminAJaxUrl,!0);a.setRequestHeader("Content-Type","application/x-www-form-urlencoded");a.setRequestHeader("X-Requested-With",
"WPRXMLHttpRequest");a.send(null)}var g="sessionStorage"in window&&void 0!==window.sessionStorage;function f(a){if(a){a=JSON.parse(a);var c=!1,b;for(b in a){var d=document.getElementById(b);void 0!=d&&null!=d&&d.getAttribute("data-partial-caching-id")==a[b].id?document.getElementById(b).innerHTML=a[b].content:c=!0}if(g)try{sessionStorage.setItem("WPR_SS",JSON.stringify(a))}catch(k){if(k==QUOTA_EXCEEDED_ERR)throw"Quota exceeded!";}c&&e()}}
if(g)try{var h=JSON.parse(sessionStorage.getItem("WPR_SS"));if(h){var l=!1,m;for(m in h){var n=document.getElementById(m);void 0!=n&&null!=n&&n.getAttribute("data-partial-caching-id")==h[m].id?n.innerHTML=h[m].content:l=!0}}else throw"No fragment!";l&&e()}catch(p){e()}else e();
</script>
<?php
}
*/