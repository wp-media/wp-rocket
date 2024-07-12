const gulp = require("gulp");
const browserify = require('browserify');
const babel = require('babelify');
const source = require('vinyl-source-stream');
const buffer = require('vinyl-buffer');
const sourcemaps = require('gulp-sourcemaps');
const uglify = require('gulp-uglify');
const watchify = require('watchify');
const rename = require("gulp-rename");
const js_tasks = require('../gulpconfig').tasks.js;

class gulpJs {
	constructor(options = {}) {
		this.options = options;
	}

	_compile( filepath, finalname, minify = false, load_sourcemaps = false ) {
		let bundle = browserify({
			entries: filepath,
			debug: true
		}).transform(babel);

		let stream =  bundle.bundle()
			.pipe(source(finalname + '.js'))
			.pipe(buffer());

		let rename_options = {  };
		if ( minify ) {
			stream = stream.pipe(uglify());
			rename_options.suffix = '.min';
		}

		stream = stream.pipe( rename( rename_options ) );

		if ( minify && load_sourcemaps ) {
			stream = stream.pipe(sourcemaps.init({loadMaps: false}))
				.pipe(sourcemaps.write('./'));
		}

		stream = stream.pipe(gulp.dest('assets/js'));

		return stream;
	}

	buildAppUnmin() {
		return this._compile( './src/js/global/app.js', 'wpr-admin', false, false );
	}

	buildAppMin() {
		return this._compile( './src/js/global/app.js', 'wpr-admin', true, true );
	}

	buildLazyloadCssMin() {
		return this._compile( './src/js/custom/lazyload-css.js', 'lazyload-css', true, true );
	}

	buildLcpBeacon() {
		return gulp.src(['./node_modules/rocket-scripts/dist/lcp-beacon*'])
			.pipe(gulp.dest('./assets/js'));
	}

	buildAll() {
		return gulp.series(
			() => this.buildAppUnmin(),
			() => this.buildAppMin(),
			() => this.buildLazyloadCssMin(),
			() => this.buildLcpBeaconUnMin(),
			() => this.buildLcpBeaconMin()
		);
	}

	watch() {
		gulp.watch('./src/js/global/*.js', gulp.series( 'build:js:app:unmin', 'build:js:app:min' ));
		gulp.watch( './src/js/custom/lazyload-css.js', gulp.series( 'build:js:lazyloadcss:min' ) );
		gulp.watch( './assets/js/lcp-beacon.js', gulp.series( 'build:js:lcp:min' ) );
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
			cb();
			return;
		}
		current_task(cb);
	} );
})
