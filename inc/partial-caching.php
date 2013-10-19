<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * Add WP Rocket attribut in before_widget
 *
 * since 1.4.0
 *
 */
 
add_filter( 'dynamic_sidebar_params', 'rocket_dynamic_sidebar_params' );
function rocket_dynamic_sidebar_params( $params )
{

    global $wp_registered_widgets;
    $widget_id           = $params[0]['widget_id'];
    $widget_obj          = $wp_registered_widgets[$widget_id];
    $widget_opt          = get_option($widget_obj['callback'][0]->option_name);
    $widget_num          = $widget_obj['params'][0]['number'];
    $widget_data		 = $widget_opt[$widget_num];
    $before_widget       = $params[0]['before_widget'];
    $after_widget        = $params[0]['after_widget'];
    $widget_opt_active   = isset( $widget_data['rocket-partial-caching-active'] ) ? $widget_data['rocket-partial-caching-active'] : false;
    $widget_opt_interval = isset( $widget_data['rocket-partial-caching-interval'] ) ? (int)$widget_data['rocket-partial-caching-interval'] : false;
    $widget_opt_unit     = isset( $widget_data['rocket-partial-caching-unit'] ) ? $widget_data['rocket-partial-caching-unit'] : false;
    
	
    // Check if the widget should be in partial cache
	if ( $widget_opt_active && $widget_opt_interval > 0 )
	{
		$interval = (int)( $widget_opt_interval * constant( $widget_opt_unit ) );

		// Si les widgets de la sidebar ne possède pas d'argument "before_widget",
		// On ajoute automatiquement l'id du widget et l'attribut data-partial-caching
		if( empty( $before_widget ) )
		{
			$before_widget = '<div id="' . $widget_id . '" data-partial-caching="true">';
			$after_widget  = '</div>';
		}
		else
		{
			//
			if( preg_match( '/id=[\'"](.*)[\'"]/' , $before_widget ) )
				$before_widget = preg_replace( '/id=[\'"](.*)[\'"]/', '/id="$1" data-partial-caching="true"/', $before_widget );
			else
				$before_widget = str_replace( '>', ' id="' . $widget_id . '" data-partial-caching="true">', $before_widget );
		}

	}
	
	$params[0]['before_widget'] = $before_widget;
	$params[0]['after_widget']  = $after_widget;
    return $params;
}

add_action( 'wp_enqueue_scripts', 'rocket_add_ajax_for_partial_cache' );
function rocket_add_ajax_for_partial_cache()
{
	wp_enqueue_script( 'rocket_ajax_partial_cache', WP_ROCKET_INC_JS_URL . 'partial-cache.js', array(), WP_ROCKET_VERSION, true /*in_footer*/ );
	wp_localize_script( 'rocket_ajax_partial_cache', 'rocket_l10n', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
}

/**
 * JavaScript code to read LocalStorage and send ajax informations
 *
 * since 1.4.0
 *
 */
/*
// You can enqueue the full human readable script if you need to debug it, just add "define( 'ROCKET_SCRIPT_DEBUG', true )" in wp-config.php
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
		if( postData!=null && postData!='' && postData!='{}' && postData!=undefined ){
			for(var key in postData){
				var value = postData[key];
				if(!postData.hasOwnProperty(value) ){
					params += key + '=' + encodeURIComponent(value['interval']) + '&';
				}
			}
	    }
		params += 'action=rocket_get_refreshed_fragments';

		xhr.send(params);

        return xhr;
	}

	function WPR_isEmpty(obj) {
		if( typeof obj==Object )
			return Object.keys(obj).length === 0;
		else
			return true;
	}

	function _WPR_cb( data )
	{
		var wp_rocket_fragments = null;
		if ( data != -1 ) {
			var done=false;
			data = JSON.parse( data );
			for( var key in data ) {
				if( data[key]['content'] != undefined && data[key]['content'] != '' ) {
					var elem = document.getElementById( key );
					if( elem != undefined && elem != null ) {
						document.getElementById(key).innerHTML = data[key]['content'];
						done = true;
					}
				}
			}

			if ( sup_html5st ) {
				var WPR_SS = sessionStorage.getItem( 'WPR_SS' );
				if( WPR_SS!=null && WPR_SS!='' && WPR_SS!='{}' && WPR_SS!=undefined ){
					wp_rocket_fragments = JSON.parse( WPR_SS );
					for( var key in data ) {
						if( data[key] && data[key]['content'] != undefined && data[key]['content'] != '' && wp_rocket_fragments!=undefined) {
							wp_rocket_fragments[key]['content'] = data[key]['content'];
							wp_rocket_fragments[key]['interval'] = data[key]['interval'];
						}else if( data[key]['content'] == '' && wp_rocket_fragments!=undefined ){
							delete wp_rocket_fragments[key];
						}
					}
				}
				try {
					console.log(data);
					console.log(wp_rocket_fragments);
					if( WPR_SS==null || WPR_SS=='' || WPR_SS=='{}' || WPR_SS==undefined ){
						wp_rocket_fragments = data;
					}
					if( done )
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
		var WPR_SS = sessionStorage.getItem( 'WPR_SS' );
		if( WPR_SS!=null && WPR_SS!='' && WPR_SS!='{}' && WPR_SS!=undefined ){
		var wp_rocket_fragments = JSON.parse( WPR_SS );

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
 // same as above, minified
function rocket_partial_caching_script_min()
{ ?>
<script>var rocketAdminAJaxUrl="<?php echo admin_url( 'admin-ajax.php?action=rocket_get_refreshed_fragments' ); ?>";var sup_html5st='sessionStorage' in window && window['sessionStorage']!==undefined;
function _WPR_Ajax(){if("undefined"!==typeof XMLHttpRequest)var a=new XMLHttpRequest;else for(var b=["MSXML2.XmlHttp.5.0","MSXML2.XmlHttp.4.0","MSXML2.XmlHttp.3.0","MSXML2.XmlHttp.2.0","Microsoft.XmlHttp"],c=0,d=b.length;c<d;c++)try{a=new ActiveXObject(b[c]);break}catch(f){}a.onreadystatechange=function(){4!=a.readyState||200!=a.status&&0!=a.status||_WPR_cb.call(a,a.responseText)};a.open("POST",rocketAdminAJaxUrl,!0);a.setRequestHeader("Content-Type","application/x-www-form-urlencoded");a.setRequestHeader("X-Requested-With",
"WPRXMLHttpRequest");var b="",c=JSON.parse(sessionStorage.getItem("WPR_SS")),e;for(e in c)d=c[e],c.hasOwnProperty(d)||(b+=e+"="+encodeURIComponent(d.interval)+"&");a.send(b+"action=rocket_get_refreshed_fragments");return a}function rocketRemover(a,b){return a.splice(b,1)}
function _WPR_cb(a){if(-1!=a){a=JSON.parse(a);for(var b in a)if(""!=a[b].content){var c=document.getElementById(b);void 0!=c&&null!=c&&(document.getElementById(b).innerHTML=a[b].content)}if(sup_html5st){c=JSON.parse(sessionStorage.getItem("WPR_SS"));for(b in a)""!=a[b].content&&void 0!=c?(c[b].content=a[b].content,c[b].interval=a[b].interval):""==a[b].content&&void 0!=c&&delete c[b];try{null==c&&(c=a),sessionStorage.setItem("WPR_SS",JSON.stringify(c))}catch(d){if(d==QUOTA_EXCEEDED_ERR)throw"Quota exceeded!";
}}}}if(sup_html5st){var wp_rocket_fragments=JSON.parse(sessionStorage.getItem("WPR_SS"));if(wp_rocket_fragments)for(var key in wp_rocket_fragments){var elem=document.getElementById(key);void 0!=elem&&null!=elem&&(elem.innerHTML=wp_rocket_fragments[key].content)}}_WPR_Ajax();
</script>
<?php
}
*/