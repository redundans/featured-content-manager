module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        sass: {
            dist: {
                options: {
                    banner: '/*! <%= pkg.name %> <%= pkg.version %> filename.min.css <%= grunt.template.today("yyyy-mm-dd h:MM:ss TT") %> */\n',
                    style: 'compressed'
                },
                files: {
                    'admin/assets/css/customizer.min.css': 'admin/assets/sass/customizer.scss',
                }
            }
        },
        uglify: {
            dist: {
                options: {
                    banner: '/*! <%= pkg.name %> <%= pkg.version %> filename.min.js <%= grunt.template.today("yyyy-mm-dd h:MM:ss TT") %> */\n',
                },
                files: {
                    'admin/assets/js/customizer.min.js' : [
                        'admin/assets/js/customizer.js'
                    ]
                }
            }
        },
        watch: {
            all: {
                files: 'admin/assets/sass/*.scss',
                tasks: ['sass'],
            },
        },
        pot: {
              options:{
              text_domain: 'featured-content-manager', //Your text domain. Produces my-text-domain.pot
              dest: 'languages/', //directory to place the pot file
              keywords: ['gettext', '__'], //functions to look for
            },
            files:{
              src:  [ '**/*.php' ], //Parse all php files
              expand: true,
               }
          },
    });

    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-pot');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', [
        'sass:dist',
        'uglify:dist',
        'pot',
    ]);

};