let build = require('gulp-query')
  , scss = require('gulp-query-scss')
  , js = require('gulp-query-js-buble')
;

build((query) => {
  query
    .plugins(scss, js)
    .scss('resources/assets/scss/components/debug.scss', 'public/css/components/debug.css')
    .js('resources/assets/js/components/debug.js', 'public/js/components/debug.js')
});