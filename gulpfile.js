// Require the npm modules we need
var gulp = require("gulp"),
    rename = require("gulp-rename"),
	concat = require('gulp-concat'),
    cleanCSS = require("gulp-clean-css"),
    terser = require("gulp-terser");

function minifyCSS() {
  return gulp.src([
		"./assets/css/_src/ggicons.css",
		"./assets/css/_src/utility.css",
		"./assets/css/_src/editor.css",
		"./assets/css/_src/integrations.css"
	])
	.pipe(concat('editor.min.css'))
    .pipe(cleanCSS())
    .pipe(gulp.dest("./assets/css"));
}

function minifyJS() {
  return gulp.src("./assets/js/editor.js")
    .pipe(rename("editor.min.js"))
    .pipe(terser())
    .pipe(gulp.dest("./assets/js"));
}

exports.default = gulp.parallel(minifyCSS, minifyJS);