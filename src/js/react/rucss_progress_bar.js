/* global window, document */
import RUCSSStatus from './containers/rucss_status.jsx';

document.addEventListener('DOMContentLoaded', function() {
	ReactDOM.render(<RUCSSStatus wpObject={window.rocket_rucss_ajax_data} />, document.getElementById('rucss-progressbar'));
});
