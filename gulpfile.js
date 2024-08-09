const requireDir  = require('require-dir');
// Require all tasks.
requireDir('./src/js/gulp/tasks', { recurse: true });

/*
* List of gulp tasks:
*
* CSS Tasks:
*
* gulp build:saas:unmin => Builds Full admin CSS, the unminified version (wpr-admin.css)
* gulp build:saas:min   => Builds Full admin CSS, the minified version (wpr-admin.min.css)
* gulp build:sass:all   => Builds all admin CSS files (wpr-admin.css, wpr-admin.min.css, wpr-admin-rtl.css, wpr-admin-rtl.min.css)
* gulp sass:watch       => Watches all admin CSS files mentioned above and builds them again with any change.
*
* JS Tasks:
*
* gulp build:js:app:unmin       => Builds admin app js file, the unminified version (wpr-admin.js)
* gulp build:js:app:min         => Builds admin app js file, the minified version (wpr-admin.min.js)
* gulp build:js:lazyloadcss:min => Builds lazyload CSS js file, the minified version (lazyload-css.min.js)
* gulp build:js:all             => Builds all js files mentioned above (wpr-admin.js, wpr-admin.min.js, lazyload-css.min.js, wpr-beacon.min.js)
* gulp build:js:beacon          => Builds lcp beacon script, the minified version (wpr-beacon.min.js, source file, and map file)
* gulp js:watch                 => Watches all js files changes and build them again with any change.
*
 */
