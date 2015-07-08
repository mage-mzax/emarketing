
var extend = require('util')._extend;
var path = require('path');
var fs = require('fs');
var libxmljs = require("libxmljs");
var crypto = require('crypto');

var _configXml;

var mzax = exports;

mzax.getConfigXml = function()
{
    if(!_configXml) {
        _configXml = libxmljs.parseXml(fs.readFileSync(path.resolve('src/module/etc/config.xml')));
    }
    return _configXml;
};


mzax.getModuleVersion = function()
{
    return mzax.getConfigXml().get('/config/modules/Mzax_Emarketing/version').text().trim();
}





mzax.registerBuild = function(grunt, namespace, targetPath, options) {
    
    var debug = options.debug;
    
    var _config = {};
    
    var srcPath = path.resolve('./src');

    
    var extensionVersion = mzax.getModuleVersion();
    
    
    
    var phpLicense = grunt.file.read(options.codeLicense || 'license-php.txt');
    
    phpLicense = grunt.template.process(phpLicense, {data: {
        pkg: grunt.file.readJSON('package.json'),
        version: extensionVersion
    }});

    
    
    function addTask(task, config, label) {
        
        if(task.indexOf(':') !== -1) {
            task = task.split(':');
            label = task[1];
            task  = task[0];
        }
        
        label = label||'';
        
        if(!_config[task]) {
            _config[task] = {};
        }
        _config[task][namespace+label] = grunt.util._.merge(_config[task][namespace+label]||{}, config);
    }
    
    
    
    
    
    
    addTask('string-replace', {
        
        options: {
            replacements: [{
                pattern: '@version     {{version}}',
                replacement: '@version     '+extensionVersion
            },{
                pattern: '{{version}}',
                replacement: extensionVersion
            },{
                pattern: '@version {{version}}',
                replacement: '@version ' + extensionVersion
            },{
                pattern: '@license {{license}}',
                replacement: '@license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)'
            },{
                pattern: '{{date}}',
                replacement: "<%= grunt.template.today('yyyy-mm-dd') %>"
            },{
                pattern: '{{time}}',
                replacement: "<%= grunt.template.today('h:MM:ss') %>"
            }]
        },
        files: [{
            // Module related directories
            expand: true, 
            cwd: path.resolve(srcPath, 'module'), 
            dest: path.resolve(targetPath, 'app/code/community/Mzax/Emarketing'),
            src: '**/*'
        },{
            // All converted 3rd party libs
            expand: true, 
            cwd: path.resolve(srcPath, 'lib_composer'), 
            dest: path.resolve(targetPath, 'lib'),
            src: '**/*'
        },{
            // PHP lib files
            expand: true, 
            cwd: path.resolve(srcPath, 'lib'), 
            dest: path.resolve(targetPath, 'lib'),
            src: '**/*'
        },{
            // adminhtml template files
            expand: true, 
            cwd: path.resolve(srcPath, 'design/adminhtml'), 
            dest: path.resolve(targetPath, 'app/design/adminhtml/default/default'),
            src: '**/*'
        },{
            // frontend template files
            expand: true, 
            cwd: path.resolve(srcPath, 'design/frontend'), 
            dest: path.resolve(targetPath, 'app/design/frontend/base/default'),
            src: '**/*'
        },{
            // javascript files
            expand: true,
            cwd: path.resolve(srcPath, 'js'),
            src: ['**/*.js'],
            dest: path.resolve(targetPath, 'js'),
        },{
            // module etc XML
            expand: true, 
            cwd: './', 
            dest: path.resolve(targetPath, 'app/etc/modules'),
            src: ['Mzax_Emarketing.xml']
        },{
            // module etc XML
            expand: true, 
            cwd: './', 
            dest: targetPath,
            src: options.extraFiles||[]
        }]
    });
    
    
    
    addTask('clean', {
        options:{
            force:true
        },
        
        files: [{
            // remove old files 
            expand: true, 
            cwd: targetPath,
            src: ['app/code/community/Mzax/Emarketing', 
                  'lib/Mzax',
                  'lib/Html2Text',
                  'lib/Symfony',
                  'lib/TijsVerkoyen',
                  'lib/UAParser',
                  'js/mzax', 
                  'app/etc/modules/Mzax_Emarketing.xml',
                  'app/design/adminhtml/default/default/layout/mzax', 
                  'app/design/frontend/base/default/layout/mzax',
                  'app/design/adminhtml/default/default/template/mzax', 
                  'app/design/frontend/base/default/template/mzax',
                  'skin/adminhtml/default/default/mzax']
        }]
    });
    
    
    addTask('copy', {
        files: [{
            // CKEditor
            expand: true,
            cwd: path.resolve(srcPath, 'js_3rdparty/ckeditor'),
            dest: path.resolve(targetPath, 'js/mzax/ckeditor'),
            src: ['**/*'],
            
        }/*,{
            expand: true,
            cwd: path.resolve(srcPath, 'skin/adminhtml/default/default/mzax'),
            src: ['** /*'],
            dest: path.resolve(targetPath, 'skin/adminhtml/default/default/mzax'),
        }*/]
    });
    
    
    
    
    
    
    
    addTask('imagemin', {
        options: {
            optimizationLevel: 3
        },
        
        files: [{
            // adminhtml skin images
            expand: true,
            cwd: path.resolve(srcPath, 'skin/adminhtml/images'),
            src: ['**/*.{png,jpg,gif}'],
            dest: path.resolve(targetPath, 'skin/adminhtml/default/default/mzax/images')
        }]
    });
    
    
    
    
    
    addTask('sass', {
        options: {
            outputStyle: debug ? 'expanded' : 'compressed'
        },
        files: [{
            expand: true,
            cwd: path.resolve(srcPath, 'skin/adminhtml'),
            src: ['**/*.scss'],
            dest: path.resolve(targetPath, 'skin/adminhtml/default/default/mzax'),
            ext: '.css'
        }]
    });
    
    

    
    
    if(!debug) {
        
        addTask('clean:production', {
            options:{
                force:true
            },
            files: [{
                // remove any files that should not go in production
                expand: true, 
                cwd: targetPath,
                src: ['app/code/community/Mzax/Emarketing/controllers/TestController.php']
            }]
        });
        
        
        addTask('uglify', {
            options: {
                banner: phpLicense + "\n\n",
                maxLineLen: 1000,
                compress: {
                    drop_console: true,
                    global_defs: {
                        "DEBUG": false
                    },
                }
            },
            files: [{
                expand: true,
                cwd: path.resolve(srcPath, 'js'),
                src: ['**/*.js'],
                dest: path.resolve(targetPath, 'js'),
                ext: '.js',
                extDot: 'last'
            }]
        });
    }
    
    
    
    if(options.push) {
        addTask('ftp_push', {
            options: options.push,
            files: [{
                expand: true,
                cwd: targetPath,
                src: ['app/code/community/Mzax/Emarketing/**/*', 
                      'lib/Mzax/**/*', 
                      'js/mzax/**/*', 
                      'app/design/adminhtml/default/default/mzax/**/*', 
                      'app/design/frontend/base/default/mzax/**/*',
                      'skin/adminhtml/default/default/mzax/**/*']
            }]
        });
    }
    
    
    if(options.compress) {
        addTask('compress', {
            options: {
                archive: options.compress,
            },
            files: [{
                expand: true, 
                cwd: targetPath, 
                dest: '',
                src: './**', 
            }]
        });
    }
    
    
    grunt.task.registerTask(namespace+'-clean', 'Clean '+namespace, function() {
        grunt.task.run([
            'clean:'+namespace
        ]);
    });
    
    grunt.task.registerTask(namespace+'-package', 'Build '+namespace, function() {
        
        var packageFile = path.resolve(targetPath, 'package.xml');
        
        var xml = libxmljs.parseXml(fs.readFileSync(packageFile));
        
        fs.unlinkSync(packageFile);
        
        var target = libxmljs.Element(xml, 'target');
            target.attr('name', 'mage');
            
        function md5File(file)
        {
            var content = fs.readFileSync(file, {encoding: 'utf8'});
            return crypto.createHash('md5').update(content).digest('hex');
        }
        
        function walkDir(dir, node) 
        {
            fs.readdirSync(dir).forEach(function(filename) {
                var file = path.resolve(dir, filename);
                if(grunt.file.isDir(file)) {
                    var dirNode = libxmljs.Element(xml, 'dir');
                        dirNode.attr('name', filename);
                    
                    walkDir(file, dirNode);
                    node.addChild(dirNode);
                }
                else {
                    var fileNode = libxmljs.Element(xml, 'file');
                        fileNode.attr('name', filename);
                        fileNode.attr('hash', md5File(file));
                    node.addChild(fileNode);
                }
            });
        }
        
        walkDir(targetPath, target);
        
        xml.get('//contents').addChild(target);
        //console.log(xml.toString());
        
        fs.writeFileSync(packageFile, xml.toString());
    });
    
    
    
    
    grunt.task.registerTask(namespace+'-push', 'Deploy '+namespace, function() {
        
        grunt.task.run(['ftp_push:'+namespace]);
        
    });
    
    grunt.task.registerTask(namespace, 'Build '+namespace, function() 
    {
        grunt.task.run([
            'clean:'+namespace,
            'imagemin:'+namespace,
            'sass:'+namespace,
            'copy:'+namespace,
            'string-replace:'+namespace
        ]);
        
        
        if(!debug) {
            grunt.task.run([
                'uglify:'+namespace,
                'clean:'+namespace+'production'
            ]);
        }
        if(options.compress) {
            grunt.task.run([namespace+'-package', 'compress:'+namespace]);
        }
    });
    
    
    grunt.config.merge(_config);

};