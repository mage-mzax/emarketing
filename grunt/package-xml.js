module.exports = function(grunt) {

    var path = require('path');
    var fs = require('fs');
    var libxmljs = require("libxmljs");
    var crypto = require('crypto');


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
        fs.readdirSync(dir).forEach(function(filename) {

           // console.log(filename);

            var file = path.resolve(dir, filename);
            if(grunt.file.isDir(file)) {
                var dirNode = libxmljs.Element(xmlDocument, 'dir');
                dirNode.attr('name', filename);

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
            version: '1.0.0'
        });

        var packageFile = grunt.file.read(options.template);
            packageFile = packageFile.replace('{{version}}', options.version);
            packageFile = packageFile.replace('{{time}}', 'time111');
            packageFile = packageFile.replace('{{date}}', 'date222');

        var xmlDocument = libxmljs.parseXml(packageFile);

        var targetNode = libxmljs.Element(xmlDocument, 'target');
            targetNode.attr('name', 'mage');


        this.filesSrc.forEach(function(dir) {
            walkDir(dir, targetNode, xmlDocument);
        });

        xmlDocument.get('//contents').addChild(targetNode);

        fs.writeFileSync(this.files[0].dest, xmlDocument.toString());
    });



};
