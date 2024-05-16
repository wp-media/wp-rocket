const gulp = require("gulp");
const rename = require("gulp-rename");
const sass = require('gulp-sass')(require('sass'));
const js_tasks = require('../gulpconfig').tasks.js;

class gulpJs {
	constructor(options = {}) {
		this.options = options;
	}

	compile() {


	}
}

if ( ! js_tasks ) {
	return;
}

const gulpJsObject = new gulpJs();
js_tasks.forEach( js_task => {
	gulp.task( js_task.task, (cb) => {
		const method = js_task['method'];
		const current_task = gulpJsObject[method]();
		if ( 'function' !== typeof current_task ) {
			return;
		}
		current_task(cb);
	} );
})
