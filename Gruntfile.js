module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        sass: {
            dist: {
                options: {
                    banner: '/*! <%= pkg.name %> <%= pkg.version %> filename.min.css <%= grunt.template.today("yyyy-mm-dd h:MM:ss TT") %> */\n',
                    style: 'compressed'
                },
                files: [{
                    expand: true,
                    cwd: 'admin/assets/sass',
                    src: [
                        '*.scss'
                    ],
                    dest: 'admin/assets/css',
                    ext: '.min.css'
                }]
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
              keywords: ['gettext', '__', 'esc_html_e', 'esc_html', 'esc_attr_e', 'esc_attr', '_e', '_x'], //functions to look for
              msgmerge: true,
            },
            files:{
              src:  [ 'admin/*.php', 'public/*.php', 'updater/*.php' ], //Parse all php files
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