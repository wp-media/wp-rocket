let cpcssHeartbeatCall;
const cpcssHeartbeat = () => {
	const xhttp  = new XMLHttpRequest();
	xhttp.onload = () => {
		if ( 200 !== xhttp.status ) {
			return;
		}

		const cpcs_heartbeat_response = JSON.parse( xhttp.response );
		if ( false === cpcs_heartbeat_response.success ) {
			stopCPCSSHeartbeat();
			return;
		}

		if ( cpcs_heartbeat_response.success &&
			'cpcss_complete' === cpcs_heartbeat_response.data.status ) {
			stopCPCSSHeartbeat();
			return;
		}

		cpcssHeartbeatCall = setTimeout( () => {
			cpcssHeartbeat();
		}, 3000 );
	};

	xhttp.open( 'POST', ajaxurl, true );
	xhttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
	xhttp.send( "action=rocket_cpcss_heartbeat&_nonce=" + rocket_cpcss_heartbeat.nonce );
}


const stopCPCSSHeartbeat = () => {
	clearTimeout( cpcssHeartbeatCall );
}

cpcssHeartbeat();
