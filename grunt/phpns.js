module.exports = function(grunt) {
    
const ENCODING = 'utf8';
    
    
var path = require('path');
var fs = require('fs');
var mkdirp = require('mkdirp');


/**
 * Copy files from src to dest
 * 
 * @param src
 * @param dest
 * @return void
 */
function copyFile(src, dest)
{
    mkdirp.sync(path.dirname(dest));
    fs.createReadStream(src).pipe(fs.createWriteStream(dest));
}


function mkdir(file)
{
    if(grunt.file.isDir(file)) {
        return;
    }
    if(path.extname(file)) {
        file = path.dirname(file);
    }
    mkdirp.sync(file);
}






var defaultIgnore = ['Tests', '.DS_Store', '.gitignore'];
    

    
grunt.registerMultiTask('phpns', 'Convert new PHP namespace to old', function() {
        

    var log = grunt.log;
    var options = this.options({});
    var source = this.data.source;
    var dest   = this.data.dest || options.dest;
    var namespace = this.data.namespace;
    var base = this.data.base || './';
    var lib = this.data.lib || '';
    
    var ignore = defaultIgnore.concat(this.data.ignore || []);
    
    
    if (!grunt.file.isDir(source)) {
        log.warn('Source dir "' + source + '" is not directory.');
        return false;
    }
    
    
    // All detected class will be captured here
    var classes = {};
    
    scanDir(source, '');
    
    
    /**
     * Scan directory for all files and check
     * for php files that contain classes
     * 
     * @param src
     * @param cwd
     * @return void
     */
    function scanDir(src, cwd)
    {
        var files = fs.readdirSync(src);
        
        files.forEach(function(filename) {
            
            var srcFile  = path.resolve(src, filename);
            var destFile = path.resolve(dest, base, cwd||'./', filename);
            
            // ingore if match ignore
            if(ignore.indexOf(filename) !== -1) {
                return;
            }
            
            // go recursive
            if(grunt.file.isDir(srcFile)) {
                scanDir(srcFile, path.join(cwd, filename));
            }
            else {
                // php special treatment
                if(path.extname(filename) === '.php') {
                    if(!convertClass(srcFile)) {
                        copyFile(srcFile, destFile);
                    }
                }
                // normal files
                else {
                    copyFile(srcFile, destFile);
                }
            }
        });
    }
    
    
    
    
    
    
    function detectLocalClassNames(dir, prefix, namespace)
    {
        if(!grunt.file.isDir(dir)) {
            return;
        }
        // find class files in current folder
        fs.readdirSync(dir).forEach(function(filename) {
            var file = path.resolve(dir, filename);
            if(path.extname(filename) === '.php') {
                var localName = path.basename(filename, '.php');
                className = prefix + "_" + localName;
                classes[localName] = className;
                
                if(namespace) {
                    classes[namespace+'\\'+localName] = className;
                }
            }
            if(grunt.file.isDir(file)) {
                detectLocalClassNames(file, prefix+'_'+filename, (namespace ? namespace+'\\' : '')+filename);
            }
        });
        
    }
    
    
    /**
     * Extract PHP namespace from content
     * 
     * @param string content PHP file content
     */
    function extractNamespace(content)
    {
        var namespace = content.match(/^namespace\s+([A-Z0-9\\]+);$/im);
        if(namespace) {
            return namespace[1].replace(/\\/g, '_');
        }
        return '';
    }
    
    
    
    
    
    /**
     * Convert PHP Class files
     */
    function convertClass(src)
    {
        var content = fs.readFileSync(src, {encoding: ENCODING});
        
        var destFile = path.resolve(dest, path.basename(src));
        
        
        // retrieve namespace
        var namespace = extractNamespace(content);
        if( namespace ) {
            // update destFile ussing namespace path
            destFile = path.resolve(dest, namespace.replace(/_/g, '/'), path.basename(src));
        }
        
        mkdir(destFile);
        
        detectLocalClassNames(path.dirname(src), namespace, '');
        
        
        // search for use declaration
        var useDeclerations = content.match(/^use (.*)$/igm);
        if(useDeclerations) {
            useDeclerations.forEach(function(code) {
                
                var match = code.match(/^use\s+(.*)\s*;/i);
                if(match) {
                    var className = match[1].replace(/\\/g, '_');
                    var localName = className.replace(/.*_(.*)/, '$1');
                    //log.warn("***"+className);
                    detectLocalClassNames(path.resolve(source, lib, className.replace(/_/g, '/').replace(base, '.')), className, localName);
                    classes[localName] = className;
                }
            });
        }
        
        // serach for class declerations
        var classDeclerations = content.match(/^(abstract )?(class|interface)\s+([a-z0-9]+)/igm);
        if(classDeclerations) {
            classDeclerations.forEach(function(code) {
                var match = code.match(/^(abstract )?(class|interface)\s+([a-z0-9]+)/i);
                if(match) {
                    var localName = match[1];
                    var className = namespace + "_" + localName;
                    classes[localName] = className;
                }
            });
        }
        else {
            return false;
        }
        
        // scan for all classes and replace them with the new ones
        for(var cls in classes) {
            var regex = new RegExp('[^a-z0-9\\\\_]'+cls.replace('\\', '\\\\')+'[^a-z0-9\\\\_]', 'gi');
            content = content.replace(regex, function(match) {
                return match.replace(cls, classes[cls]);
            });
            content = content.replace(regex, function(match) {
                 return match.replace(cls, classes[cls]);
             });
            
        }
        
        
        // comment out namespace line
        content = content.replace(/^namespace (.*)$/gmi, '#namespace $1');
        
        // comment out use lines
        content = content.replace(/^use (.*)$/igm, '#use $1');
        
        // remove root namespace e.g. \Exception
        content = content.replace(/(\W)\\(Exception|[A-Z0-9]{6,})/ig, '$1$2');
        
        
        // get_called_class might return something unexpected
        content = content.replace(/get_called_class\(\)/ig, "str_replace('_','\\\\', get_called_class())");
        
        
        if(options.banner) {
            content = content.replace(/^<\?php\n/i, "<?php\n" + options.banner + "\n");
        }
        
        fs.writeFileSync(destFile, content, {encoding: ENCODING});
        
        return true;
    }
    
    
    
    
    
});


};