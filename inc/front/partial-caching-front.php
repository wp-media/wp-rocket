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
    $widget_id = $params[0]['widget_id'];
    $widget_obj = $wp_registered_widgets[$widget_id];
    $widget_opt = get_option($widget_obj['callback'][0]->option_name);
    $widget_num = $widget_obj['params'][0]['number'];
    
    
    // On vérifie si le widget doit être mis en cache partiel
	if ( 	isset( $widget_opt[$widget_num]['rocket-partial-caching-active'], $widget_opt[$widget_num]['rocket-partial-caching-interval'], $widget_opt[$widget_num]['rocket-partial-caching-unit'] )
			&& (int)$widget_opt[$widget_num]['rocket-partial-caching-interval']>0 && (int)$widget_opt[$widget_num]['rocket-partial-caching-interval']==1 )
	{
		$interval = (string)(time()+(int)( $widget_opt[$widget_num]['rocket-partial-caching-interval'] * constant( $widget_opt[$widget_num]['rocket-partial-caching-unit'] ) ));
		$data_partial_caching = 'data-partial-caching-debug="'.$interval.','.$interval.'" ';
		// Si les widgets de la sidebar ne possède pas d'argument "before_widget",
		// On ajoute automatiquement l'id du widget et l'attribut data-partial-caching
		if( empty($params[0]['before_widget']) ) 
		{
			$params[0]['before_widget'] = '<div id="' . $widget_id . '" '.$data_partial_caching.'>';
			$params[0]['after_widget']  = '</div>';
		}
		else 
		{		
			// Is there an ID ? Ok use it, or create id with partial caching data infos
			if( strpos( $params[0]['before_widget'], 'id="' )>0 )
				$params[0]['before_widget'] = str_replace( 'id="', $data_partial_caching . 'id="', $params[0]['before_widget'] );	
			else
				$params[0]['before_widget'] = str_replace( '>', ' id="' . $widget_id . '" '.$data_partial_caching.'>', $params[0]['before_widget'] );		
		}
			
	}
    	    
    return $params;
}



/**
 * JavaScript code to read LocalStorage and send ajax informations
 *
 * since 1.4.0
 *
 */

// You can change the hook, "wp_footer" or "wp_head" are the bests just add "define( 'ROCKET_PARTIAL_CACHE_HOOK', 'wp_footer' )" in wp-config.php
$ROCKET_PARTIAL_CACHE_HOOK = 'wp_head'; // wp_head default hook
if( defined( 'ROCKET_PARTIAL_CACHE_HOOK' ) )
	$ROCKET_PARTIAL_CACHE_HOOK = ROCKET_PARTIAL_CACHE_HOOK;

// You can enqueue the full human readable script if you need to debug it, just add "define( 'ROCKET_SCRIPT_DEBUG', true )" in wp-config.php
if( !defined( 'ROCKET_SCRIPT_DEBUG' ) )
	add_action( $ROCKET_PARTIAL_CACHE_HOOK, 'rocket_partial_caching_script_min', PHP_INT_MAX );
else
	add_action( $ROCKET_PARTIAL_CACHE_HOOK, 'rocket_partial_caching_script', 0 );
unset( $ROCKET_PARTIAL_CACHE_HOOK );
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
