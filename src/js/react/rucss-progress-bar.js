/* global window, document */
import RUCSSStatus from './containers/rucss-status.jsx';

document.addEventListener('DOMContentLoaded', function() {
	ReactDOM.render(<RUCSSStatus wpRUCSSObject={window.rocket_rucss_ajax_data} />, document.getElementById('rucss-progressbar'));
});
