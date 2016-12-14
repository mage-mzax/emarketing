/*TODO*/ 

window.mzax=window.mzax||{},function(a,b){b.ajaxHandler={onCreate:function(a){a&&Element.show("loading-mask")},onComplete:function(){0==Ajax.activeRequestCount&&Element.hide("loading-mask")}},Ajax.Responders.register(b.ajaxHandler)}(window,mzax);