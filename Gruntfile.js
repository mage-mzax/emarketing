var mage = require("./grunt/mage-extension.js");

module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        config: {
            version: mage.getModuleVersion()
        },

        'clean': {
            build: ['./build/*']
        },

        /* Compile sass files */
        'sass': {
            adminhtml: {
                options: {
                    outputStyle: 'expanded'
                },
                files: [{
                    expand: true,
                    cwd: 'src/skin/adminhtml',
                    src: ['**/*.scss'],
                    dest: 'build/skin/adminhtml/default/default/mzax/',
                    ext: '.css'
                }]
            }
        },

        /* Minify images */
        'imagemin': {
            options: {
                optimizationLevel: 3
            },
            adminhtml: {
                files: [{
                    // adminhtml skin images
                    expand: true,
                    cwd: 'src/skin/adminhtml/images',
                    src: ['**/*.{png,jpg,gif}'],
                    dest: 'build/skin/adminhtml/default/default/mzax/images'
                }]
            }
        },

        /* Minify JS classes */
        'uglify': {
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
            js: {
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

        /* Add 3rd party js libs */
        "copy" : {
            js: {
                files: [
                    {expand: true, src: '**', dest: './build/js/mzax/ckeditor/', cwd: './js_3rdparty/ckeditor'}
                ]
            }
        },

        /* Convert  PSR-4 namespaces to PSR-0 */
        'phpns': {
            options:{
                banner: "/*\n * NOTICE:\n * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.\n */",
                dest: './build/lib'
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
                ignore: ['tests', 'html2text.php', 'convert.php', '.editorconfig', '.travis.yml']
            },
            uapphp:{
                source: './vendor/ua-parser/uap-php',
                base:   'UAParser',
                ignore: ['tests', 'bin', 'uap-core', '.travis.yml', '.gitmodules']
            }
        },

        /* Create extension package.xml file for marketplace */
        'mage-package-xml': {
            extension: {
                options: {
                    template: 'package.xml',
                    version: '<%= config.version %>'
                },
                dest: './build/package.xml',
                src: ['src/module/', 'build/']
            }
        },

        /* Create extension package file for marketplace */
        'compress': {
            extension: {
                options: {
                    archive: './Mzax_Emarketing-<%= config.version %>.tgz'
                },
                files: [
                    {expand: true, dest: '', src: './**', cwd: './src/module'},
                    {expand: true, dest: '', src: './**', cwd: './build'}
                ]
            }
        }
    });


    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-clean');

    grunt.loadTasks('./grunt');
    grunt.registerTask('default', ['phpns']);
    grunt.registerTask('build', ['clean:build', 'sass', 'imagemin', 'uglify', 'copy', 'phpns']);
    grunt.registerTask('pack', ['mage-package-xml', 'compress']);
};
