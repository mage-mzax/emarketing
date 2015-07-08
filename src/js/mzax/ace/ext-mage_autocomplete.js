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

ace.define("ace/ext/mage_autocomplete",
        ["require","exports","module", "ace/ext/language_tools"], 
        function(require, exports, module) {
    
"use strict";



var util = ace.require("ace/autocomplete/util");
var snippetManager = ace.require("ace/snippets").snippetManager;
var commands = require("ace/commands/default_commands").commands;
var langTools = ace.require("ace/ext/language_tools");
var Autocomplete = require("ace/autocomplete").Autocomplete;
var Editor = require("ace/editor").Editor;
var config = require("ace/config");
var IDENTIFIER_REGEX = /[a-zA-Z_0-9\$\.\u00A2-\uFFFF-]/;


/**
 * Set mage snippets
 * 
 * Add method to editor prototype.
 * Should be used the add the snippets that are avaialable for the
 * current editor.
 * 
 * @param Array snippets
 */
Editor.prototype.setMageSnippets = function(snippets)
{
    if(!snippets) {
        return;
    }
    this._mageSnippets = snippets;
    snippets.forEach(function(snippet) {
        snippetManager.register([{
            name: '$' + snippet.shortcut, 
            score: 100, 
            snippet: snippet.snippet,
            content: snippet.snippet,
            type:'snippet',
            trigger: '\\$(?:' + snippet.shortcut + ')',
            guard: '.*',
            scope: 'mage',
            endTrigger: '',
            endGuard: '.*'
        }]);
        
        snippet.type = 'snippet';
        snippet.score = 1000;
        snippet.meta = 'Mage';
        snippet.identifierRegex = IDENTIFIER_REGEX;    
        
        var docHtml = [];
        if(snippet.title) {
            docHtml.push("<strong>" , snippet.title , "</strong><hr></hr>")
        }
        if(snippet.shortcut) {
            docHtml.push('Shortcut: <code style="font-weight:bold; color:#CB6D12">$' , snippet.shortcut , "</code>")
        }
        if(snippet.description) {
            docHtml.push("<p>" , snippet.description , "</p>")
        }
        if(snippet.snippet) {
            docHtml.push("Code: <p><code>" , snippet.snippet , "</code></p>")
        }
        snippet.docHTML = docHtml.join("");
    });
}


/**
 * Helper function to auto show autocomplete when user
 * types "mage."
 * 
 * @param Object e
 * @return void
 */
var doLiveAutocomplete = function(e) {
    
    var editor = e.editor;
    var hasCompleter = editor.completer && editor.completer.activated;
    
    if (e.command.name === "insertstring" || e.command.name === "mage" || e.command.name.substr(0,2) == 'go') {
        var pos = editor.getCursorPosition();
        var line = editor.session.getLine(pos.row);
        var prefix = util.retrievePrecedingIdentifier(line, pos.column, IDENTIFIER_REGEX);
        
        if(prefix == 'mage.' && !hasCompleter) {
            if (!editor.completer) {
                editor.completer = new Autocomplete();
            }
            editor.completer.autoSelect = false;
            editor.completer.autoInsert = false;
            editor.completer.showPopup(editor);
        }
    }
}



langTools.addCompleter({
    getCompletions: function(editor, session, pos, prefix, callback) {
        if(editor._mageSnippets) {
            callback(null, editor._mageSnippets);
        }
    }
});



config.defineOptions(Editor.prototype, "editor", {
    enableMageLiveAutocompletion: {
        set: function(val) {
            if (val) {
                if (!this.completers)
                    this.completers = Array.isArray(val)? val: completers;
                this.commands.on('afterExec', doLiveAutocomplete);
            } else {
                this.commands.removeListener('afterExec', doLiveAutocomplete);
            }
        },
        value: false
    }
});


commands.push({
    name: 'mage',
    bindKey: {win: 'Ctrl-M',  mac: 'Command-M'},
    exec: function(editor) {
        editor.insert("mage.");
    },
    readOnly: false 
});



/**
 * Extend insert snippet to allow calling javascript functions
 * 
 */
function extendInsertSnippet(orignalFunc)
{
    return function(editor, snippetText) {
        var isCall = snippetText.match(/\{js:\s*([a-zA-Z0-9]+)\}/);
        if(isCall) {
            var method = editor[isCall[1]] || window[isCall[1]];
            if( method ) {
                method.call(editor);
                snippetText = '';
            }
        }
        return orignalFunc.call(this, editor, snippetText);
    }
}


(['insertSnippet', 'insertSnippetForSelection']).forEach(function(method) {
    snippetManager[method] = extendInsertSnippet(snippetManager[method]);
});


});