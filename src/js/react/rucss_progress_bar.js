/* global window, document */
if (! window._babelPolyfill) {
  require('@babel/polyfill');
}

import React from 'react';
import ReactDOM from 'react-dom';
import Admin from './containers/Admin.jsx';

document.addEventListener('DOMContentLoaded', function() {
  ReactDOM.render(<Admin wpObject={window.rocket_ajax_data} />, document.getElementById('remove_unused_css_progress_bar'));
});
