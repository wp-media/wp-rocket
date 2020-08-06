var gulp = require('gulp');
var sourcemaps = require('gulp-sourcemaps');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var browserify = require('browserify');
var watchify = require('watchify');
var babel = require('babelify');
var uglify = require('gulp-uglify');
var sass = require('gulp-sass');
var rename = require("gulp-rename");


/* Task to compile sass admin */
gulp.task('sass', function () {
  return gulp.src('./src/scss/main.scss')
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(rename('wpr-admin.css'))
    .pipe(gulp.dest('assets/css'));
});

/* Task to compile sass admin RTL */
gulp.task('sass_rtl', function () {
    return gulp.src('./src/scss/rtl.scss')
      .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
      .pipe(rename('wpr-admin-rtl.css'))
      .pipe(gulp.dest('assets/css'));
  });

/* Task to compile sass modal */
gulp.task('sass_modal', function () {
  return gulp.src('./src/scss/modal.scss')
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(rename('wpr-modal.css'))
    .pipe(gulp.dest('assets/css'));
});

 /* Task to watch sass changes */
gulp.task('sass:watch', function () {
  gulp.watch('./src/scss/main.scss', ['sass']);
  gulp.watch('./src/scss/modal.scss', ['sass_modal']);
});



/* Task to compile JS */
function compile(watch) {
    var bundler = watchify(browserify('./src/js/global/app.js', {debug: true}).transform(babel));

    // Admin JS
    function rebundle() {
        bundler.bundle()
                .on('error', function (err) {
                    console.error(err);
                    this.emit('end');
                })
                .pipe(source('wpr-admin.js'))
                .pipe(buffer())
                .pipe(uglify())
                .pipe(sourcemaps.init({loadMaps: false}))
                .pipe(sourcemaps.write('./'))
                .pipe(gulp.dest('assets/js'));
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

gulp.task('default', ['watch', 'sass', 'sass:watch']);


/**
 * Compiles a standalone script file.
 *
 * Command line: gulp js:compile_single --script=script-name.js
 */
gulp.task('js:compile_single', () => {
	const {argv} = require("yargs");
	const transpile = require('gulp-babel');
	const source = './assets/js/' + argv.script;

	return gulp.src( source )
		// Transpile newer JS for cross-browser support.
		.pipe( transpile({
			presets: ["es2015"]
		}))
		// Minify the script.
		.pipe( uglify() )
		// Rename the .js to .min.js.
		.pipe( rename( { suffix: '.min' } ) )
		// Write out the script to the configured <filename>.min.js destination.
		.pipe( gulp.dest( './assets/js/' ) );
});
