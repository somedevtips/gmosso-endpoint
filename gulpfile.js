/*jslint node: true white: true*/

"use strict";

const gulp = require("gulp");
const cssnano = require("gulp-cssnano");
const sourcemaps = require("gulp-sourcemaps");
const rename = require("gulp-rename");
const terser = require("gulp-terser");

gulp.task("minifycss", function () {
    return gulp.src("public/css/front.css")
        .pipe(sourcemaps.init())
        .pipe(cssnano())
        .pipe(rename("front.min.css"))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest("public/css"));
});

gulp.task("minifyjs", function () {
    return gulp.src("public/js/users.js")
        .pipe(sourcemaps.init())
        .pipe(terser({ecma: 5}))
        .pipe(rename("users.min.js"))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest("public/js"));
});

gulp.task("default", gulp.parallel("minifycss", "minifyjs"));
