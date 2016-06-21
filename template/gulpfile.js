var built = true;

var paths = {
	source: 'src/',
	dev: 'dev/',
	built: 'built/',

	js: 'js/',
	css: 'css/',
	images: 'images/',
	vendor: 'vendor/',
};

var path = paths.dev;

/**** Dependencias ****/
const gulp = require('gulp'),
	jade = require('gulp-jade'),
	concat = require('gulp-concat'),
	uglify = require('gulp-uglify'),
	imagemin = require('gulp-imagemin'),
	pngquant = require('imagemin-pngquant'),
	less = require('gulp-less'),
	minifyCss = require('gulp-minify-css'),
	autoprefixer = require('gulp-autoprefixer'),

	webserver = require('gulp-webserver'),
	gulpif = require('gulp-if');



/**** Configuracio de Tareas ****/
gulp.task('jade', function(){
	gulp.src([paths.source+'**/*.jade', '!'+paths.source+'**/_*.jade'])
	.pipe(jade({
		pretty: true
	}))
	.pipe(gulp.dest(path));
});

gulp.task('less', function(){
	var $path = path+paths.css;

	gulp.src(paths.source+paths.css+'main.less')
	.pipe(less({compress: built}))
	.pipe(gulpif(built, minifyCss()))
	.pipe(autoprefixer('last 10 versions', 'ie 9'))
	.pipe(gulp.dest($path));
});

gulp.task('js', function(){
	var $path = path+paths.js;

	gulp.src(paths.source+paths.js+'*.js')
	.pipe(concat('main.js'))
	.pipe(gulpif(built, uglify()))
	.pipe(gulp.dest($path));
});

gulp.task('images', function(){
	var $path = path+paths.images;

	gulp.src(paths.source+paths.images+'**/*')
	.pipe(imagemin({
		progressive: true,
		svgoPlugins: [{removeViewBox: false}],
		use: [pngquant()]
	}))
	.pipe(gulp.dest($path));
});

gulp.task('vendor', function(){
	gulp.src([
		'./bower_components/normalize-css/normalize.css',
		'./bower_components/flexboxgrid/dist/flexboxgrid.min.css',
		
		'./bower_components/jquery/dist/jquery.min.js',
	])
	.pipe(gulp.dest(path+paths.vendor));
});

gulp.task('fonts', function(){
	var $path = path+paths.css+'fonts/';

	gulp.src(paths.source+paths.css+'fonts/**/*')
	.pipe(gulp.dest($path));
});

gulp.task('compile', ['jade','less','js','images','vendor']);

gulp.task('watch', function(){
	gulp.watch(paths.source+'**/*.jade', ['jade']);
	gulp.watch(paths.source+paths.css+'**/*.less', ['less']);
	gulp.watch(paths.source+paths.js+'**/*.js', ['js']);
	//gulp.watch(paths.source+paths.images+'**/*', ['images']);
});

gulp.task('webserver', function () {
    gulp.src('dev')
    .pipe(webserver({
        livereload: true,
		open: true,
		port: 8000
    }));
});


gulp.task('default', ['webserver']);
gulp.task('dev', ['compile','watch','webserver']);
gulp.task('built', ['compile','webserver']);