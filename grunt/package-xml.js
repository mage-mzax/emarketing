module.exports = function(grunt) {

    var path = require('path');
    var fs = require('fs');
    var libxmljs = require("libxmljs");
    var crypto = require('crypto');
    var dateFormat = require('dateformat');


    /**
     * Create MD5 hash from file
     *
     * @param {String} file
     * @return {String}
     */
    function md5File(file)
    {
        var content = fs.readFileSync(file, {encoding: 'utf8'});
        return crypto.createHash('md5').update(content).digest('hex');
    }

    function walkDir(dir, node, xmlDocument)
    {
        var dirNode;
        fs.readdirSync(dir).forEach(function(filename) {

            if (filename === 'package.xml') {
                return;
            }

            var file = path.resolve(dir, filename);
            if(grunt.file.isDir(file)) {

                dirNode = node.get('./dir[@name="'+filename+'"]');
                if (!dirNode) {
                    dirNode = libxmljs.Element(xmlDocument, 'dir');
                    dirNode.attr('name', filename);
                }

                walkDir(file, dirNode, xmlDocument);
                node.addChild(dirNode);
            }
            else {
                var fileNode = libxmljs.Element(xmlDocument, 'file');
                    fileNode.attr('name', filename);
                    fileNode.attr('hash', md5File(file));

                node.addChild(fileNode);
            }
        });
    }


    grunt.registerMultiTask('mage-package-xml', 'Create package XML for Magento extension', function() {

        var options = this.options({
            template: 'package.xml',
            version: '1.0.0',
            time: new Date()
        });

        var packageFile = grunt.file.read(options.template);
            packageFile = packageFile.replace('{{version}}', options.version);
            packageFile = packageFile.replace('{{time}}', dateFormat(options.time, 'HH:MM:ss'));
            packageFile = packageFile.replace('{{date}}', dateFormat(options.time, 'yyyy-mm-dd'));

        var xmlDocument = libxmljs.parseXml(packageFile);

        var targetNode = libxmljs.Element(xmlDocument, 'target');
            targetNode.attr('name', 'mage');


        this.filesSrc.forEach(function(dir) {
            walkDir(dir, targetNode, xmlDocument, 0);
        });

        xmlDocument.get('//contents').addChild(targetNode);

        fs.writeFileSync(this.files[0].dest, xmlDocument.toString());
    });



};
