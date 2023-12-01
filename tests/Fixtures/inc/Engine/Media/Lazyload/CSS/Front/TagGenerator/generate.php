<?php

$tags = <<<TAG
<div id="wpr-lazyload-bg"></div><style id="wpr-lazyload-bg-exclusion">:root{--wpr-bg-15ef8: url(https://domain.ext/path/to/background.ext); }</style>
<noscript>
<style id="wpr-lazyload-bg-nostyle">:root{--wpr-bg-16ef9: url(https://domain.ext/path/to/background2.ext); }:root{--wpr-bg-17ef6: url(https://domain.ext/path/to/background3.ext); }:root{--wpr-bg-18ef6: url(https://domain.ext/path/to/background3.ext); }</style>
</noscript>
<script type="application/javascript">const rocket_pairs = [{"selector":"#section_2_hash","style":":root{--wpr-bg-16ef9: url(https:\/\/domain.ext\/path\/to\/background2.ext); }"},{"selector":"title~=\"wp-rocket\"","style":":root{--wpr-bg-17ef6: url(https:\/\/domain.ext\/path\/to\/background3.ext); }"},{"selector":"title~=\"wp-rocket-image\"","style":":root{--wpr-bg-18ef6: url(https:\/\/domain.ext\/path\/to\/background3.ext); }"}]; const rocket_excluded_pairs = [{"selector":".internal-css-background-image","style":":root{--wpr-bg-15ef8: url(https:\/\/domain.ext\/path\/to\/background.ext); }"}];</script>
TAG;


return [
    'shouldGenerate' => [
        'config' => [
              'mapping' => [
				  [
					  'selector' => '.internal-css-background-image',
					  'style' => ':root{--wpr-bg-15ef8: url(https://domain.ext/path/to/background.ext); }'
				  ],
				  [
					  'selector' => '#section_2_hash',
					  'style' => ":root{--wpr-bg-16ef9: url(https://domain.ext/path/to/background2.ext); }"
				  ],
				  [
					  'selector' => 'title~="wp-rocket"',
					  'style' => ':root{--wpr-bg-17ef6: url(https://domain.ext/path/to/background3.ext); }'
				  ],
				  [
					  'selector' => 'title~="wp-rocket-image"',
					  'style' => ':root{--wpr-bg-18ef6: url(https://domain.ext/path/to/background3.ext); }'
				  ]
			  ],
              'loaded' => [
				  [
					  'selector' => '.internal-css-background-image',
					  'style' => ':root{--wpr-bg-15ef8: url(https://domain.ext/path/to/background.ext); }'
				  ],
			  ],
        ],
        'expected' => $tags
    ],

];
