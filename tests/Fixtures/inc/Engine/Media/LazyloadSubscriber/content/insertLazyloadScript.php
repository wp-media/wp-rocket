<?php

$polyfill   = '<script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?flags=gated&features=default%2CIntersectionObserver%2CIntersectionObserverEntry"></script>';
$script     = '<script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/15.1.1/lazyload.js"></script>';
$min_script = '<script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/15.1.1/lazyload.min.js"></script>';

return [
	'min_script'          => $min_script,
	'script'              => $script,
	'min_script_polyfill' => "{$polyfill}{$min_script}",
	'script_polyfill'     => "{$polyfill}{$script}",
];
