/* global window, document */
import Admin from './containers/Admin.jsx';

document.addEventListener('DOMContentLoaded', function() {
  ReactDOM.render(<Admin wpObject={window.rocket_rucss_ajax_data} />, document.getElementById('rucss-progressbar'));
});
