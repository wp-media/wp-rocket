const gulp = require("gulp");
const rename = require("gulp-rename");
const sass = require('gulp-sass')(require('sass'));
const css_tasks = require('../gulpconfig').tasks.css;

class gulpCss {
	constructor(options = {}) {
		this.options = options;
	}

	_compileSaas( main_saas_filename, final_filename, minify = false ) {
		return gulp.src('./src/scss/' + main_saas_filename + '.scss')
			.pipe(sass({
				outputStyle: minify ? 'compressed': 'expanded'
			}).on('error', sass.logError))
			.pipe(
				rename( final_filename + ( minify ? '.min' : '' ) + '.css' )
			)
			.pipe( gulp.dest('assets/css') );
	}

	compileAdminSaas() {
		return this._compileSaas( 'main', 'wpr-admin' );
	}

	compileAdminSaasMin() {
		return this._compileSaas( 'main', 'wpr-admin', true );
	}

	compileAdminRtlSaas() {
		return this._compileSaas( 'rtl', 'wpr-admin-rtl' );
	}

	compileAdminRtlSaasMin() {
		return this._compileSaas( 'rtl', 'wpr-admin-rtl', true );
	}

	compileAdminFullSaasUnmin() {
		return gulp.parallel(
			() => this.compileAdminSaas(),
			() => this.compileAdminRtlSaas()
		);
	}

	compileAdminFullSaasMin() {
		return gulp.parallel(
			() => this.compileAdminSaasMin(),
			() => this.compileAdminRtlSaasMin()
		);
	}

	watch() {
		return gulp.watch('./src/scss/**/*.scss', gulp.series( 'build:saas:unmin', 'build:saas:min' ));
	}
}

if ( ! css_tasks ) {
	return;
}

const gulpCssObject = new gulpCss();
css_tasks.forEach( css_task => {
	gulp.task( css_task.task, (cb) => {
		const method = css_task['method'];
		const current_task = gulpCssObject[method]();
		if ( 'function' !== typeof current_task ) {
			return;
		}
		current_task(cb);
	} );
})
