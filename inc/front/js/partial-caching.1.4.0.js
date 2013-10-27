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

    xhr.open( 'POST', rocket_l10n.ajaxUrl, true );
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