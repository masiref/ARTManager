'use strict';

module.exports = function (grunt) {

    // Load all grunt tasks
    require('load-grunt-tasks')(grunt);
    var app = 'app/Resources';

    // Configuration
    grunt.initConfig({

        /**
         * grunt-contrib-compass
         * @see https://github.com/gruntjs/grunt-contrib-compass
         *
         * Compile Sass to CSS using Compass.
         */
        compass: {
            sass: {
                options: {
                    sassDir: app + '/scss',
                    cssDir: '.tmp/css',
                    importPath: app + '/libs',
                    outputStyle: 'expanded',
                    noLineComments: true
                }
            }
        },

        /**
         * grunt-contrib-sass
         * @see https://github.com/gruntjs/grunt-contrib-sass
         *
         * Compile Sass to CSS using Sass.
         */
        sass: {
            watch: {
                files: [{
                    expand: true,
                    cwd: app + '/scss',
                    src: ['*.scss'],
                    dest: '.tmp/css',
                    ext: '.css'
                }],
                debugInfo: true,
                lineNumbers: true,
                noCache: true
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: app + '/scss',
                    src: ['*.scss'],
                    dest: '.tmp/css',
                    ext: '.css'
                }],
                noCache: true
            }
        },

        coffee: {
            compile: {
                files: {
                    '.tmp/app.js': [
                        'src/App/MainBundle/Resources/coffee/app.coffee',
                        'src/App/MainBundle/Resources/coffee/ProjectsManager.coffee'
                    ]
                }
            }
        },

        /**
         * grunt-contrib-cssmin
         * @see https://github.com/gruntjs/grunt-contrib-cssmin
         *
         * Run predefined tasks whenever watched file patterns are added, changed or deleted.
         */
        cssmin: {
            combine: {
                options:{
                    report: 'gzip',
                    keepSpecialComments: 0
                },
                files: {
                    'web/built/min.css': [
                        '.tmp/css/**/*.css',
                        app + '/libs/Fontello/fontello-codes.css',
                        app + '/libs/datatables/css/jquery.datatables.css',
                        app + '/libs/datatables/css/datatables.bootstrap.css',
                        app + '/libs/bootstrap3-editable/css/bootstrap-editable.css',
                        app + '/libs/bootstrap-treeview/src/css/bootstrap-treeview.css',
                        app + '/css/**/*.css'
                    ]
                }
            }
        },

        /**
         * grunt-contrib-uglify
         * @see https://github.com/gruntjs/grunt-contrib-uglify
         *
         * Run predefined tasks whenever watched file patterns are added, changed or deleted.
         */
        uglify: {
            options: {
                mangle: false,
                sourceMap: true,
                sourceMapName: 'web/built/app.map'
            },
            dist: {
                files: {
                    'web/built/app.min.js':[
                    app + '/libs/jquery/jquery.js',
                    app + '/libs/bootstrap/assets/javascripts/bootstrap.js',
                    app + '/libs/sweetalert/lib/sweet-alert.js',
                    app + '/libs/datatables/js/jquery.datatables.js',
                    app + '/libs/datatables/js/plugins/fnAddTr.js',
                    app + '/libs/datatables/js/datatables.bootstrap.js',
                    app + '/libs/bootstrap3-editable/js/bootstrap-editable.js',
                    app + '/libs/bootstrap-treeview/src/js/bootstrap-treeview.js',
                    '.tmp/js/**/*.js',
                    app + '/js/**/*.js'
                    ]
                }
            }
        },

        /**
         * grunt-contrib-copy
         * @see https://github.com/gruntjs/grunt-contrib-copy
         *
         * Run predefined tasks whenever watched file patterns are added, changed or deleted.
         */
        copy: {
            dist: {
                files: [{
                    expand: true,
                    cwd: app + '/libs/Fontello/fonts',
                    dest: 'web/fonts',
                     src: ['**']
                },{
                    expand: true,
                    cwd: app + '/libs/bootstrap/assets/fonts/bootstrap',
                    dest: 'web/fonts/bootstrap',
                     src: ['**']
                }]
            }
        },

	watch: {
		css: {
                    files: [
                        app + '/scss/**/*.scss',
                        app + '/libs/datatables/css/*.css'
                    ],
                    tasks: ['css']
		},
		javascript: {
                    files: [app + '/js/**/*.js'],
                    tasks: ['javascript']
		}
	}

        

    });

    /****************************************************************
     * Grunt Task Definitions
     ****************************************************************/

    grunt.registerTask('default', ['css', 'javascript', 'cp']);    
    grunt.registerTask('javascript', ['coffee', 'uglify']);
    grunt.registerTask('css', ['compass','cssmin']);
    grunt.registerTask('css', ['sass','cssmin']);
    grunt.registerTask('cp', ['copy']);
};
