/**
 * Mzax Emarketing (www.mzax.de)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this Extension in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @version     {{version}}
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


// init main namespace
window.mzax = window.mzax || {};


(function(window, mzax) {

    mzax.ajaxHandler = {
        onCreate: function(request) {
            if(request){
                Element.show('loading-mask');
            }
        },

        onComplete: function(transport) {
            if(Ajax.activeRequestCount == 0) {
                Element.hide('loading-mask');
            }
        }
    };


    Ajax.Responders.register(mzax.ajaxHandler);


})(window, mzax);


