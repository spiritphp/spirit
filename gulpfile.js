var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');

var args = process.argv;

var copy = function(src, dest) {
    return gulp.src(src)
        .pipe(gulp.dest(dest))
};

var scss = function(src, dest) {
    return gulp.src('./resources/assets/scss/' + src)
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: (args.indexOf('production') ? 'compressed' : 'expanded')
        }).on('error', sass.logError))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('./public/css/' + dest));
};

// ---------------------
// ---------------------
// SCSS
gulp.task('scss:services', function () {
    return scss('services/**/*.scss', 'services');
});

gulp.task('scss:components', function () {
    return scss('components/**/*.scss', 'components');
});

gulp.task('scss', ['scss:components', 'scss:services']);

gulp.task('copy:bootstrap', function () {
    return copy('./node_modules/bootstrap/dist/js/bootstrap.min.js', './public/js/services/');
});
gulp.task('copy:bootstrap-fonts', function () {
    return copy('./node_modules/bootstrap/dist/fonts/*', './public/fonts/services/bootstrap');
});
gulp.task('copy', ['copy:bootstrap','copy:bootstrap-fonts']);

gulp.task('default', ['scss','copy']);

gulp.task('scss:watch', ['scss'], function () {
    gulp.watch('./resources/assets/scss/**/*.scss', ['scss']);
});