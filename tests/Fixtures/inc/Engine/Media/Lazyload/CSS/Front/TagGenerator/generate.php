<?php

$tags = <<<TAG
<style id="wpr-lazyload-bg"></style><style id="wpr-lazyload-bg-exclusion">:root{--wpr-bg-15ef8: url(https://domain.ext/path/to/background.ext); }</style>
<noscript>
<style id="wpr-lazyload-bg-nostyle">:root{--wpr-bg-16ef9: url(https://domain.ext/path/to/background2.ext); }</style>
</noscript>
<script type="application/javascript">const rocket_pairs = [{"selector":"#section_2_hash","style":":root{--wpr-bg-16ef9: url(https:\/\/domain.ext\/path\/to\/background2.ext); }"}]; const rocket_excluded_pairs = [{"selector":".internal-css-background-image","style":":root{--wpr-bg-15ef8: url(https:\/\/domain.ext\/path\/to\/background.ext); }"}];</script>
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
