module.exports = function(grunt) {

    var path = require('path');

    var srcPath = './src';
    var buildPath = './build';


    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            adminhtml: {
                options: {
                    outputStyle: 'expanded'
                },
                files: [{
                    expand: true,
                    cwd: 'src/skin/adminhtml',
                    src: ['**/*.scss'],
                    dest: 'build/skin/adminhtml',
                    ext: '.css'
                }]
            }
        },

        imagemin: {
            options: {
                optimizationLevel: 3
            },
            adminhtml: {
                files: [{
                    // adminhtml skin images
                    expand: true,
                    cwd: 'src/skin/adminhtml/images',
                    src: ['**/*.{png,jpg,gif}'],
                    dest: 'build/skin/adminhtml/images'
                }]
            }
        },

        uglify: {
            options: {
                banner: "/*TODO*/ \n\n",
                maxLineLen: 1000,
                compress: {
                    drop_console: true,
                    global_defs: {
                        "DEBUG": false
                    }
                }
            },
            adminhtml: {
                files: [{
                    expand: true,
                    cwd: 'src/js',
                    src: ['**/*.js'],
                    dest: 'build/js/',
                    ext: '.js',
                    extDot: 'last'
                }]
            }
        },

        /* Convert new PHP namespaces (a/b/c) to old ones (a_b_c)*/
        phpns: {
            options:{
                banner: "/*\n * NOTICE:\n * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.\n */",
                dest: path.resolve(buildPath, 'lib')
            },
            Symfony_CssSelector:{
                source: './vendor/symfony/css-selector',
                base:   'Symfony/Component/CssSelector',
                ignore: ['Tests']
            },
            TijsVerkoyen_CssToInlineStyles:{
                source: './vendor/tijsverkoyen/css-to-inline-styles',
                base:  'TijsVerkoyen/CssToInlineStyles',
                ignore: []
            },
            html2text:{
                source: './vendor/soundasleep/html2text',
                base:   'Html2Text',
                ignore: ['tests', 'html2text.php', 'convert.php']
            },
            uapphp:{
                source: './vendor/ua-parser/uap-php',
                base:   'UAParser',
                ignore: ['tests', 'bin', 'uap-core']
            }
        },

        'mage-package-xml': {
            extension: {
                options: {
                    template: 'package.xml'
                },
                dest: './build/package.xml',
                src: ['src/module/', 'build/']
            }

        }
    });


    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.loadTasks('./grunt');


    grunt.registerTask('default', ['phpns']);



};
