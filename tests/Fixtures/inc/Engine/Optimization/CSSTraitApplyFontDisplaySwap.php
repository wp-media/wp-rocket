<?php

return [
		'shouldTargetOnlyFontFaceRuleSets' => [
			'css'      => <<<CSS
@font-face {
  font-family: 'MyWebFont'; /* Define the custom font name */
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff'); /* Define where the font can be downloaded */
        /* Define how the browser behaves during download */
}
CSS
			,
			'expected' => <<<CSS
@font-face {font-display:swap;
  font-family: 'MyWebFont'; /* Define the custom font name */
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff'); /* Define where the font can be downloaded */
        /* Define how the browser behaves during download */
}
CSS
		],

		'shouldIgnoreNonFontFaceRuleSets' => [
			'css'      => <<<CSS
body {
  font-family: 'MyWebFont'; /* Let's pick a custom font! */
  font-size: 14px;
  line-height: 1.5;
  color: #24292e;
  background-color: #fff;
}
CSS
			,
			'expected' => <<<CSS
body {
  font-family: 'MyWebFont'; /* Let's pick a custom font! */
  font-size: 14px;
  line-height: 1.5;
  color: #24292e;
  background-color: #fff;
}
CSS
		],

		'shouldChangeFontDisplayAttributeWhenAlreadySetInRule' => [
			'css'      => <<<CSS
@font-face {
	font-family: 'ETmodules';
	src: url("core/admin/fonts/modules.eot");
	src: url("core/admin/fonts/modules.eot#iefix")
	format("woff"), url("core/admin/fonts/modules.svg#ETModules");
	font-weight: normal;
	font-display: auto;
	font-style: normal;
}
CSS
			,
			'expected' => <<<CSS
@font-face {
	font-family: 'ETmodules';
	src: url("core/admin/fonts/modules.eot");
	src: url("core/admin/fonts/modules.eot#iefix")
	format("woff"), url("core/admin/fonts/modules.svg#ETModules");
	font-weight: normal;
	font-display: swap;
	font-style: normal;
}
CSS
		],

		'shouldTargetMultipleFontFaceRules' => [
			'css'      => <<<CSS
@font-face {
  font-family: 'MyWebFont'; /* Define the custom font name */
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff'); /* Define where the font can be downloaded */
        /* Define how the browser behaves during download */
}
@font-face {
	font-family: 'ETmodules';
	src: url("core/admin/fonts/modules.eot");
	src: url("core/admin/fonts/modules.eot#iefix")
	format("woff"), url("core/admin/fonts/modules.svg#ETModules");
	font-weight: normal;
	font-style: normal;
}
CSS
			,
			'expected' => <<<CSS
@font-face {font-display:swap;
  font-family: 'MyWebFont'; /* Define the custom font name */
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff'); /* Define where the font can be downloaded */
        /* Define how the browser behaves during download */
}
@font-face {font-display:swap;
	font-family: 'ETmodules';
	src: url("core/admin/fonts/modules.eot");
	src: url("core/admin/fonts/modules.eot#iefix")
	format("woff"), url("core/admin/fonts/modules.svg#ETModules");
	font-weight: normal;
	font-style: normal;
}
CSS
		],

		'shouldReplaceMinimizedWithoutAddingSpaces' => [
			'css'      => <<< CSS
@font-face{font-family:'MyWebFont';src:url('myfont.woff2')format('woff2'),url('myfont.woff')format('woff')}
CSS
			,
			'expected' => <<<CSS
@font-face{font-display:swap;font-family:'MyWebFont';src:url('myfont.woff2')format('woff2'),url('myfont.woff')format('woff')}
CSS
		],

		'shouldIgnoreMalFormedCSS' => [
			'css' => <<<CSS
@font-face:font-display:swap;font-family:'MyWebFont';src:url('myfont.woff2')format('woff2'),url('myfont.woff')format('woff')}@font-face{
font-family:'MyWebFont';src:url('myfont.woff2')format('woff2'),url('myfont.woff')format('woff')}@font-face {font-display:swapfont-family'ETmodules';src:
url("core/admin/fonts/modules.eot");src: url("core/admin/fonts/modules.eot#iefix")format("woff"), url("core/admin/fonts/modules.svg#ETModules")font-weight: normal;font-style: normal;
CSS
			,
			'expected' => <<<CSS
@font-face:font-display:swap;font-family:'MyWebFont';src:url('myfont.woff2')format('woff2'),url('myfont.woff')format('woff')}@font-face{font-display:swap;
font-family:'MyWebFont';src:url('myfont.woff2')format('woff2'),url('myfont.woff')format('woff')}@font-face {font-display:swapfont-family'ETmodules';src:
url("core/admin/fonts/modules.eot");src: url("core/admin/fonts/modules.eot#iefix")format("woff"), url("core/admin/fonts/modules.svg#ETModules")font-weight: normal;font-style: normal;
CSS
		],
		'shouldReplaceOnlyDisplayPropertyValue' => [
			'css'      => <<< CSS
@font-face{font-display:auto;font-family:'Barlow';src:url('https://www.autophaus-glienicke.de/wp-content/uploads/avia_fonts/type_fonts/barlow/barlow-regular.ttf')format('ttf')}
@font-face{font-family:'fa';src:url('/wp-content/plugins/recipe-card-blocks-by-wpzoom-pro/dist/assets/webfonts/fa-solid-900.woff2')format('woff2');font-display:block;}
CSS
			,
			'expected' => <<<CSS
@font-face{font-display:swap;font-family:'Barlow';src:url('https://www.autophaus-glienicke.de/wp-content/uploads/avia_fonts/type_fonts/barlow/barlow-regular.ttf')format('ttf')}
@font-face{font-family:'fa';src:url('/wp-content/plugins/recipe-card-blocks-by-wpzoom-pro/dist/assets/webfonts/fa-solid-900.woff2')format('woff2');font-display:swap;}
CSS
		],
];
