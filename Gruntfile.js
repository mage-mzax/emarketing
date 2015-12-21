module.exports = function(grunt) {
    
    
    var path = require('path');
    var mzax = require("./grunt/mzax.js");
    
    
    var srcPath = './src';
    var DEBUG_NS = 'test';
    
    
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        config: {
            version: mzax.getModuleVersion()
        },
        /* Convert new PHP namespaces (a/b/c) to old ones (a_b_c)*/
        phpns: {
            options:{
                banner: "/*\n * NOTICE:\n * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.\n */",
                dest: path.resolve(srcPath, 'lib_composer')
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
        
        watch: {
            options: {livereload: true, cwd:srcPath},
            
            php: {
                files: ['module/**', 'lib/**'],
                tasks: ['newer:string-replace:'+DEBUG_NS],
                options: {
                    livereload: false
                }
            },
            sass: {
                files: [path.join('skin/adminhtml/**/*.scss')],
                tasks: ['sass:'+DEBUG_NS],
                options: {
                    livereload: true,
                    spawn: true,
                    interrupt: true
                }
            },
            design_adminhtml: {
                files: [path.join('design/adminhtml/**/*.*')],
                tasks: ['newer:string-replace:'+DEBUG_NS]
            },
            design_frontend: {
                files: [path.join('design/frontend/**/*.*')],
                tasks: ['newer:string-replace:'+DEBUG_NS]
            },
            js: {
                files: [path.join('js/**/*')],
                tasks: ['newer:string-replace:'+DEBUG_NS]
            },
            image: {
                files: [path.resolve('skin/adminhtml/images/**/*')],
                tasks: ['newer:imagemin:'+DEBUG_NS]
            },
            configFiles: {
                files: [ 'Gruntfile.js'],
                options: {
                    reload: true
                }
            }
        }
    });
    
    
    
    require("./grunt/local.js").setup(grunt, mzax, DEBUG_NS);
    
    
    mzax.registerBuild(grunt, 'package', './package', {
        debug: false,
        extraFiles: ['package.xml'],
        compress:'./Mzax_Emarketing-<%= config.version %>.tgz'
    });
    
    
    grunt.registerTask('default', ['phpns', DEBUG_NS]);
    grunt.registerTask('pack', ['phpns', 'package']);

    grunt.loadNpmTasks('grunt-newer');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-string-replace');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-ftp-push');
    
    grunt.loadTasks('grunt');
    
};