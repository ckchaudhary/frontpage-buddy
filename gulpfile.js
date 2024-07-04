// Require the npm modules we need
var gulp = require("gulp"),
    rename = require("gulp-rename"),
    cleanCSS = require("gulp-clean-css"),
    terser = require("gulp-terser");

function minifyCSS() {
  return gulp.src("./assets/css/editor.css")
    .pipe(rename("editor.min.css"))
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