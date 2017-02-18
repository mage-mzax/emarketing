
var path = require('path');
var fs = require('fs');
var libxmljs = require("libxmljs");

var _configXml;
var configFile = 'src/module/app/code/community/Mzax/Emarketing/etc/config.xml';
var mage = exports;


/**
 *
 * @return {XML}
 */
mage.getConfigXml = function()
{
    if(!_configXml) {
        _configXml = libxmljs.parseXml(fs.readFileSync(path.resolve(configFile)));
    }
    return _configXml;
};

/**
 * @return {String}
 */
mage.getModuleVersion = function()
{
    return mage.getConfigXml().get('/config/modules/Mzax_Emarketing/version').text().trim();
};
