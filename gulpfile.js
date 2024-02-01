var gulp = require('gulp');
var sourcemaps = require('gulp-sourcemaps');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var browserify = require('browserify');
var watchify = require('watchify');
var babel = require('babelify');
var uglify = require('gulp-uglify');
var sass = require('gulp-sass')(require('sass'));
var rename = require("gulp-rename");
var iife = require('gulp-iife');

// Compile sass admin
const compile_sass_admin = (name, minify = false) => {
	return gulp.src('./src/scss/main.scss')
	  .pipe(sass({outputStyle: minify ? 'compressed': 'expanded'}).on('error', sass.logError))
	  .pipe(rename(name))
	  .pipe(gulp.dest('assets/css'));
}

// Compile sass_rtl
const compile_sass_rtl = (name, minify = false) => {
	return gulp.src('./src/scss/rtl.scss')
      .pipe(sass({outputStyle: minify ? 'compressed': 'expanded'}).on('error', sass.logError))
      .pipe(rename(name))
      .pipe(gulp.dest('assets/css'));
}

const build_sass_admin_unmin = () => {
	return compile_sass_admin('wpr-admin.css');
}
const build_sass_rtl_unmin = () => {
	return compile_sass_rtl('wpr-admin-rtl.css');
}

gulp.task('sass_all_unmin', gulp.parallel(build_sass_admin_unmin, build_sass_rtl_unmin));

const sassWatchUnmin = () => {
	// Init compilation before gulp starts watching...
	compile_sass_admin('wpr-admin.css');
	compile_sass_rtl('wpr-admin-rtl.css');

	// Start watching for changes...
	gulp.watch('./src/scss/**/*.scss', gulp.series('sass_all_unmin'));
}

// Compile without minification.
const compileWithoutMinify = () => {
	var bundler = watchify(browserify('./src/js/global/app.js', {debug: true}).transform(babel));

    // Admin JS
    var rebundle = () => {
		var isSuccess = true;
        bundler.bundle()
                .on('error', function (err) {
                    console.error(err);
					isSuccess = false;
                    this.emit('end');
                })
                .pipe(source('wpr-admin.js'))
                .pipe(buffer())
                .pipe(sourcemaps.init({loadMaps: false}))
				.pipe(sourcemaps.write('./'))
                .pipe(gulp.dest('assets/js'))
				.on('end', function() {
					if( isSuccess )console.log('Bundled without minification!');
				})
    }

	bundler.on('update', function () {
		console.log('-> bundling...');
		rebundle();
	});

    rebundle();
}

// Run `bundle:unminify` to bundle unminified assets.
gulp.task('bundle:unminify', gulp.parallel(compileWithoutMinify, sassWatchUnmin));


/* Task to compile sass admin */
const sass_admin = () => {
	return compile_sass_admin('wpr-admin.min.css', true);
}

const sass_rtl = () => {
	return compile_sass_rtl('wpr-admin-rtl.min.css', true);
}

gulp.task('sass_all', gulp.parallel(sass_admin, sass_rtl));

function sassWatch() {
	// Init compilation before gulp starts watching...
	compile_sass_admin('wpr-admin.min.css', true);
	compile_sass_rtl('wpr-admin-rtl.min.css', true);

	gulp.watch('./src/scss/**/*.scss', gulp.series('sass_all'));
}

 /* Task to watch sass changes */
 gulp.task('sass:watch', sassWatch);


/* Task to compile JS */
function compile(watch) {
    var bundler = watchify(browserify('./src/js/global/app.js', {debug: true}).transform(babel));

    // Admin JS
    function rebundle() {
		var isSuccess = true;
        bundler.bundle()
                .on('error', function (err) {
                    console.error(err);
					isSuccess = false;
                    this.emit('end');
                })
                .pipe(source('wpr-admin.js'))
                .pipe(buffer())
                .pipe(uglify())
                .pipe(sourcemaps.init({loadMaps: false}))
                .pipe(sourcemaps.write('./'))
				.pipe( rename( { suffix: '.min' } ) )
                .pipe(gulp.dest('assets/js'))
				.on('end', function() {
					if( isSuccess )console.log('Yay success!');
				})
    }

    if (watch) {
        bundler.on('update', function () {
            console.log('-> bundling...');
            rebundle();
        });
    }

    rebundle();
}

function watch() {
    return compile(true);
};

gulp.task('build', function () {
    return compile();
});
gulp.task('watch', function () {
    return watch();
});

gulp.task('default', gulp.parallel('watch', 'sass:watch', 'bundle:unminify'));

/** Tasks for deployment */
const build_sass_admin = () => {
	return compile_sass_admin('wpr-admin.min.css', true);

}
const build_sass_rtl = () => {
	return compile_sass_rtl('wpr-admin-rtl.min.css', true);
}

gulp.task('run:build:sass', gulp.parallel(build_sass_admin, build_sass_rtl));

// Bundle script without watching.
const bundleJsWithoutWatch = () => {
    var bundle = browserify({
        entries: './src/js/global/app.js',
        debug: true
      }).transform(babel);

    return bundle.bundle()
                .pipe(source('wpr-admin.js'))
                .pipe(buffer())
                .pipe(uglify())
                .pipe(sourcemaps.init({loadMaps: false}))
                .pipe(sourcemaps.write('./'))
				.pipe( rename( { suffix: '.min' } ) )
                .pipe(gulp.dest('assets/js'))
}

// Bundle script without watching.
const bundleLazyloadJsWithoutWatch = () => {
	var bundle = browserify({
		entries: './src/js/global/lazyload-css.js',
		debug: true
	}).transform(babel);

	return bundle.bundle()
		.pipe(source('lazyload-css.js'))
		.pipe(buffer())
		.pipe(uglify())
		.pipe( rename( { suffix: '.min' } ) )
		.pipe(sourcemaps.init({loadMaps: false}))
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest('assets/js'))
}

exports.bundleLazyloadJsWithoutWatch = bundleLazyloadJsWithoutWatch;

// Run build without watching: watching keeps git actions stuck on 'build'
gulp.task('run:build', gulp.parallel(bundleJsWithoutWatch, bundleLazyloadJsWithoutWatch, 'run:build:sass'));

// Compiles DelayJS script.
gulp.task('run:build-delayjs', () => {
    const bundle = browserify({
        entries: './assets/js/lazyload-scripts.min.js',
        debug: true
    }).transform(babel);

    const uglifyOptions = {
        mangle: {
           properties: {
             regex: /^_/
          }
        }
    };

    return bundle.bundle()
        .pipe(source('lazyload-scripts.min.js'))
        .pipe(buffer())
        .pipe(uglify(uglifyOptions))
        .pipe(sourcemaps.init({loadMaps: false}))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('assets/js'))
});

/**
 * Compiles a standalone script file.
 *
 * Command line: gulp js:compile_single --script=script-name.js [optional --mangle=true, --iife=true]
 */
gulp.task('js:compile_single', () => {
	const {argv} = require("yargs");
	const transpile = require('gulp-babel');
	const source = './assets/js/' + argv.script;
	const mangle = 'mangle' in argv && argv.mangle
		? {
			toplevel: true,

		}
		: false;
	const iife_status = 'iife' in argv && argv.iife;

	let stream = gulp.src( source )
		// Transpile newer JS for cross-browser support.
		.pipe( transpile({
			presets: [
				[
					'env',
					{
						'targets': {
							'browsers': [ 'last 2 versions' ]
						}
					}
				]
			]
		}))
		// Minify the script.
		.pipe( uglify({
			compress: {
				sequences: true,
				dead_code: true,
				conditionals: true,
				booleans: true,
				unused: true,
				if_return: true,
				join_vars: true,
				drop_console: true
			},
			mangle: mangle
		} ) );
	//apply iife
	if ( iife_status ) {
		stream = stream.pipe( iife({useStrict: false, prependSemicolon: false}) );
	}
	stream = stream
		// Rename the .js to .min.js.
		.pipe( rename( { suffix: '.min' } ) )
		// Write out the script to the configured <filename>.min.js destination.
		.pipe( gulp.dest( './assets/js/' ) );

	return stream;
});
