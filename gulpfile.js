// Require the npm modules we need
var gulp = require("gulp"),
    rename = require("gulp-rename"),
    cleanCSS = require("gulp-clean-css"),
    terser = require("gulp-terser");

function minifyCSS() {
  return gulp.src("./assets/editor.css")
    .pipe(rename("editor.min.css"))
    .pipe(cleanCSS())
    .pipe(gulp.dest("./assets"));
}

function minifyJS() {
  return gulp.src("./assets/editor.js")
    .pipe(rename("editor.min.js"))
    .pipe(terser())
    .pipe(gulp.dest("./assets"));
}

exports.default = gulp.parallel(minifyCSS, minifyJS);