define('ace/mode/mage', function(require, exports, module) {

    
    var oop = require("pilot/oop");
    var HtmlMode = require("ace/mode/html").Mode;
    var Tokenizer = require("ace/tokenizer").Tokenizer;
    var MageHighlightRules = require("ace/mode/mage_highlight_rules").MageHighlightRules;
    
    
    var Mode = function() {
        this.$tokenizer = new Tokenizer(new MageHighlightRules().getRules());
    };
    oop.inherits(Mode, HtmlMode);
    
    (function() {
        // Extra logic goes here. (see below)
    }).call(Mode.prototype);
    
    exports.Mode = Mode;
});



define('ace/mode/mage_highlight_rules', function(require, exports,module) {

    var oop = require("pilot/oop");
    var HtmlHighlightRules = require("ace/mode/html_highlight_rules").HtmlHighlightRules;
    var MageHighlightRules = function() {
        this.$rules = new HtmlHighlightRules.getRules();
    }
    
    oop.inherits(MageHighlightRules, HtmlHighlightRules);
    
    exports.MageHighlightRules = HtmlHighlightRules;
}); 


