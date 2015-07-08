


exports.setup = function(grunt, mzax, DEBUG_NS)
{
    
    mzax.registerBuild(grunt, DEBUG_NS, '../MagentoDir', {
        debug: true
    });
    
    
    mzax.registerBuild(grunt, 'demo', '../SecoundMagentoDir', {
        debug: false
    });
    
}