<?php

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [
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

		'shouldNotChangeFontDisplayAttributeWhenAlreadySetInRule' => [
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
	font-display: auto;
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
	],
];
