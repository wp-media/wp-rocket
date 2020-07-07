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
			'expected' => <<<EXPECTED
@font-face{font-display:swap;
  font-family: 'MyWebFont'; /* Define the custom font name */
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff'); /* Define where the font can be downloaded */
        /* Define how the browser behaves during download */
}
EXPECTED
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
			'expected' => <<<EXPECTED
body {
  font-family: 'MyWebFont'; /* Let's pick a custom font! */
  font-size: 14px;
  line-height: 1.5;
  color: #24292e;
  background-color: #fff;
}
EXPECTED
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
			'expected' => <<<EXPECTED
@font-face{
	font-family: 'ETmodules';
	src: url("core/admin/fonts/modules.eot");
	src: url("core/admin/fonts/modules.eot#iefix")
	format("woff"), url("core/admin/fonts/modules.svg#ETModules");
	font-weight: normal;
	font-display: auto;
	font-style: normal;
}
EXPECTED
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
			'expected' => <<<EXPECTED
@font-face{font-display:swap;
  font-family: 'MyWebFont'; /* Define the custom font name */
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff'); /* Define where the font can be downloaded */
        /* Define how the browser behaves during download */
}
@font-face{font-display:swap;
	font-family: 'ETmodules';
	src: url("core/admin/fonts/modules.eot");
	src: url("core/admin/fonts/modules.eot#iefix")
	format("woff"), url("core/admin/fonts/modules.svg#ETModules");
	font-weight: normal;
	font-style: normal;
}
EXPECTED
		],

		'shouldReplaceMinimizedWithoutAddingSpaces' => [
	               <<< CSS
@font-face{font-family:'MyWebFont';src:url('myfont.woff2')format('woff2'),url('myfont.woff')format('woff')}
CSS
		,
		'expected' => <<<EXPECTED
@font-face{font-display:swap;font-family:'MyWebFont';src:url('myfont.woff2')format('woff2'),url('myfont.woff')format('woff')}
EXPECTED
		],
	],
];
