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
    
    // init namespace
    mzax.ui = mzax.ui || {};
    
    var document = window.document,
        jQueryPlugins = [],
        _uid = 1000;
    
    
    
    ace.require("ace/ext/mage_autocomplete");
    
    
    /*****************************************************************
     * 
     * PRIVATE FUNCTIONS
     * 
     *****************************************************************/
    
    
    /**
     * Wait for document state complete
     * 
     * @param document
     * @param func
     * @return Number Interval id
     */
    function onReady(document, func, scope) {
        var id = setInterval(function() {
            if (document.readyState === "complete") {
                clearInterval(id);
                func.call(scope||document);
            }
        }, 10);
        return id;
    }
    
    
    function isTrue(str) {
        return !!(str||'').toLowerCase().match(/(1|yes|true|y|enabled?)/);
    }
    
    
    function limit(value, min, max)
    {
        return Math.min(Math.max(value, min), max);
    }
    
    
    
    /**
     * Remove text indentation by taking the indentation level
     * from the first line
     * 
     * @param string str
     * @return string
     */
    function removeIndentation(str) {
        // convert tabs to 4 spaces
        str = (str||'').replace(/^(\s+)/gm, function(match) {
            return match.replace(/\t/g, '    ');
        });
        // replace all empty lines at the beginning
        str = str.replace(/^(\s*)\r?\n(\s+)/, '$2');
        
        // find indentation level & remove it from all lines
        var indent = str.match(/^ +/);
        if(indent) {
            return str.replace(new RegExp('^ {0,'+indent[0].length+'}', 'gm'), '');
        }
        return str;
    }
    
    
    
    /**
     * Convert to upper case words
     * e.g. HELLO wOrLd => Hello World
     * 
     * @param string str
     * @return string
     */
    function ucwords(str) {
        return (str + '')
            .toLowerCase()
            .replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function($1) {
                return $1.toUpperCase();
            });
    }
    
    
    /**
     * Normalize label
     * Can take any fancy string as label and converts it to
     * a human friendly readable string.
     * 
     * e.g. $_helloWorld_textInput => Hello World Text Input
     * 
     * @param string str
     * @return string
     */
    function normalizeLabel(str) {
        return ucwords(str.replace(/(.)([A-Z])/g, "$1 $2").replace(/[\W_-]+/g, ' '));
    }
    
    
    /**
     * Retrieve outer HTML for a given node
     * 
     * @param Node node
     * @return string
     */
    function outerHTML(node){
        // if IE, Chrome take the internal method otherwise build one
        return node.outerHTML || (function(n){
            var div = document.createElement('div'), h;
            div.appendChild( n.cloneNode(true) );
            return div.innerHTML;
        })(node);
    }
    
    function decodeHtml(html) {
        var txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }
    
    
    
    function registerJQueryPlugins(jQuery) {
        var i = jQueryPlugins.length;
        while(--i > -1) {
            jQueryPlugins[i].call(jQuery, jQuery);
        }
    }
    
    
    function walkObject(object, func, scope)
    {
        for(var p in object) {
            if(object.hasOwnProperty(p)) {
                if(func.call(scope || object, object[p], p) === false) {
                    return p;
                }
            }
        }
        return true;
    }
    
    
    
    function find(array, func, scope)
    {
        scope = scope || array;
        for (var i = 0, length = array.length; i < length; i++) {
            if(func.call(scope, array[i])) {
                return array[i];
            }
        }
        return undefined;
    }
    
    
    /**
     * Find all editable elements
     * 
     * @param element
     * @returns Array
     */
    function findEditableElements(element)
    {
        var result = [];
        var elements = element.querySelectorAll("[mage\\:editable]");
        
        for (var i = 0, length = elements.length; i < length; i++) {
            switch((elements[i].getAttribute('mage:editable') || '0').toLowerCase()) {
                case 'yes':
                case '1':
                case 'y':
                    result.push(elements[i]);
            }
        }
        return result;
    }
    
    

    
    mzax.jQueryPlugin = function(func) {
        jQueryPlugins.push(func);
        return func;
    };
    
    
    
    
    /*****************************************************************
     * 
     * CLASSES
     * 
     *****************************************************************/
    
    /**
     * Placeholder
     * MageExpr
     * 
     * Parses magento expressions and provides access to there properties
     * 
     * e.g.
     * p = new mzax.ui.Placeholder('{{block type="core/template" template="example.phml" customer="$customer"}}');
     * p.directive === 'block'
     * p.params['type'] === 'core/template'
     * 
     */
    mzax.ui.Placeholder = Class.create({
        
        
        /**
         * regex for searching magento expressions
         */
        REGEX_PLACEHOLDER : /\{\{([^\[\}])+\}\}/g,
        
        
        /**
         * regex for validating a mage expression
         * 
         * e.g. {{var customer.firstname}}
         */
        REGEX_VALIDATE : /^\{\{(\/)?([a-z0-9]+)\s*(?:\s([^\}]+?))?\}\}$/i,
        
        
        /**
         * match all parameters for a mage expression
         * 
         * e.g. {{block type="core/template" template="example.phml" customer="$customer"}}
         */
        REGEX_PARAMS : /(?:(?:([a-z_-]+)=)?(?:([^\s]+\([^)]*([^)]+\)))|([^\s"']+)|"([^"]*)"|'([^']*)'))/g,
        
        // (?:[^\s]+\([^)]*([^)]+\)))
        /**
         * read a single parameter
         * 
         */
        REGEX_PARAM : /^(?:(?:([a-z_-]+)=)?(?:([^\s"']+)|"([^"]*)"|'([^']*)'))$/,
        
        
        /**
         * Constructor
         */
        initialize: function(code) 
        {
            if(code) {
                this.parse(code);
            }
        },
        
        
        /**
         * Encode placeholder
         * 
         * @return string
         */
        encode : function()
        {
            if(this.expr) {
                return encodeURI('encode:'+this.render());
            }
            return null;
        },
        
        


        /**
         * Parse magento expression
         * e.g.
         * {{directive foo="bar" hello="word"}}
         * 
         * @return boolean
         */
        parse : function(expr)
        {
            if(expr.indexOf('encode:') === 0) {
                expr = decodeURI(expr.substr(7));
            }
            var match = expr.match(this.REGEX_VALIDATE);
            if(match) {
                // reset
                this.expr      = expr;
                this.valid     = true;
                this.closing   = !!match[1];
                this.directive = (match[2]).toLowerCase();
                this.input     = null;
                this.params    = {};
                this.isBlock   = false;
                this.isLang    = false;
                
                // parse parameters for known directives
                switch(this.directive) {
                    case 'depend':
                    case 'if':
                    case 'else':
                        this.input = match[3] || '';
                        this.isLang = true;
                        break;
                
                    case 'block':
                    case 'layout':
                    case 'include':
                    case 'widget':
                        this.isBlock = true;
                    // continue
                    case 'htmlescape':
                    case 'store':
                    case 'skin':
                    case 'media':  
                    case 'customvar':
                    case 'config':
                        this.params = this.parseParams(match[3]);
                        break;
                    
                    // otherwise assume one input
                    default:
                        this.input = match[3];
                }
                return true;
            }
            else {
                this.valid = false;
                return false;
            }
        },
        
        
        
        /**
         * Parse parameter string
         * e.g.
         * foo="bar" hello="word"
         * 
         * @return void
         */
        parseParams : function(paramStr)
        {
            var pm,i,
                result = {},
                params = paramStr.match(this.REGEX_PARAMS);
            
            if(params) {
                for(i = 0; i < params.length; i++) {
                    pm = params[i].match(this.REGEX_PARAM);
                    if(!pm[1]) {
                        result[pm[2] || pm[3] || pm[4] || pm[5]] = true;
                    }
                    else {
                        result[pm[1]] = pm[2] || pm[3] || pm[4] || pm[5];
                    }
                }
            }
            return result;
        },
        
        
        
        /**
         * Render placeholder to magento expression
         * 
         * @return String
         */
        render : function()
        {
            // closing tags just close
            if(this.closing) {
                return this.expr = '{{/'+this.directive+'}}';
            }
            
            var expr = [this.directive];
            if(this.input) {
                expr.push(this.input);
            }
            else {
                for(var i in this.params) {
                    if(this.params.hasOwnProperty(i)) {
                        expr.push(i+'='+JSON.stringify(this.params[i]));
                    }
                }
            }
            return this.expr = '{{'+expr.join(' ')+'}}';
        },
        
        
        getPreview : function(data)
        {
            if(!data) {
                return 'N/A';
            }
            var match;
            // order.getCustomerEmail()
            if(match = data.match(/([a-z._-]+)\.get([a-z0-9]+)\(/i)) {
                return normalizeLabel(match[1] + ' ' + match[2]);
            }
            
            // order.shipping_address.format('html')
            if(match = data.match(/([a-z._-]+)\.[a-z0-9]+\(/i)) {
                return normalizeLabel(match[1]);
            }
            return normalizeLabel(data);
        },
        
        
        toElement : function(render)
        {
            if(render) {
                this.render();
            }
            var element = new Element('span', {'class': 'mzax-placeholder'}),
                params = this.params,
                directive = this.directive;
            
            element.addClassName(this.isBlock ? 'is-block' : 'is-inline');
            element.addClassName(directive);
            
            
            function getBlockHtml(title, info) {
                return '<span class="mzax-center">'
                     +   '<span class="mzax-title">' + (title || '') + '</span>'
                     +   '<span class="mzax-info">' + (info || '') + '</span>'
                     + '</span>';
            }
            
            
            switch(directive) {
            
                case 'include':
                    element.update(getBlockHtml('Include Template', params['template']));
                    break;
            
                case 'layout':
                    element.update(getBlockHtml('Layout Handle', params['handle']));
                    break;
                    
                case 'widget':
                    element.update(getBlockHtml('Widget', params['type'])); break;  
                    
                case 'block':
                    element.update(getBlockHtml(
                        params['type'] || 'Block', 
                        params['id'] || params['template']
                    )); 
                    break;
                    
                case 'store':
                case 'skin':  
                case 'media':
                    element.update('('+directive+': '+(params['url']||'')+')');
                    break;
                    
                case 'var':
                    element.update(this.getPreview(this.input));
                    break;
                    
                case 'config':
                    element.update(params['path']);
                    break;
                    
                case 'customvar':
                    element.update(this.getPreview(params['code']));
                    break; 
                    
                case 'htmlescape':
                    element.update(this.getPreview(params['var']));
                    break;
                    
                case 'if':
                case 'depend':
                    element.addClassName('is-lang');
                    element.update(this.closing 
                        ? '/'+directive.toUpperCase() 
                        : directive.toUpperCase() + ' ( ' +this.getPreview(this.input) + ' )' );
                    break;
                    
                case 'else':
                    element.addClassName('is-lang');
                    element.update('ELSE');
                    break;
                default:
                    element.update(this.expr);
                    break;
            }
            
            return element;
        },
        
        toText : function(render)
        {
            var params = this.params,
                directive = this.directive;
            
            switch(directive) {
            
                case 'include':
                case 'layout':
                case 'widget':
                case 'block':
                    return this.expr;
                    
                case 'store':
                case 'skin':  
                case 'media':
                    return params['url'];
                    
                case 'var':
                    return this.getPreview(this.input);
                    
                case 'config':
                    return params['path'];
                    
                case 'customvar':
                    return this.getPreview(params['code']);
                    
                case 'htmlescape':
                    return this.getPreview(params['var']);
                    
                
                case 'if':
                case 'depend':
                case 'else':
                    return '';
                    
                default:
                    return this.expr;
            }
        },
        
        
        
        toHtml : function(render)
        {
            var element = this.toElement(render);
            return outerHTML(element);
        }
        
    });
    
    
    /**
     * Static Placeholder Methods
     * 
     */
    (function(Placeholder) {
    
        
        /**
         * Replace all placeholders for a given element
         * in a html friendly way, means only replace text nodes
         * and don't touch attribute values
         * 
         * @param DomElement element
         * @param Function func
         * @param Boolean skipEditables
         */
        Placeholder.replaceHtml = function(element, func, skipEditables)
        {
            
            var tempPlaceholder = '@@@!{!{!----!%s!----!}!}!@@@',
                document = element.ownerDocument,
                node,
                placeholders = {},
                uid = Math.ceil(Math.random()*1000000),
                editableElements = skipEditables && findEditableElements(element);
                walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, function(node) {
                    if(node.nodeValue.length <= 5) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }, false);
            
            
            /*
             * Don't replace any content within any editable if
             * skipEditables is false
             */
            if(editableElements) {
                for (var i = 0, length = editableElements.length; i < length; i++) {
                    var elm = editableElements[i];
                    var placeholderId = tempPlaceholder.replace('%s', ++uid);
                    
                    placeholders[placeholderId] = elm.innerHTML;
                    elm.innerHTML = placeholderId;
                }
            }
            
            
            
            while(node = walker.nextNode()) {
                // we can not direclty add html to text nodes, but we can create unqiue placeholders that we later can replace
                node.nodeValue = node.nodeValue.replace(Placeholder.prototype.REGEX_PLACEHOLDER, function(match) {
                    var placeholder = new Placeholder(match);
                    if(!placeholder.valid) {
                        return;
                    }
                    // some unique string that we can replace later using innerHTML
                    var placeholderId = tempPlaceholder.replace('%s', ++uid);
                    placeholders[placeholderId] = func ? func.call(node, placeholder, true  /* allow html ?*/ ) : placeholder.toHtml(); 
                    return placeholderId;
                });
            }
            
            var html = Placeholder.replace(element.innerHTML, function(placeholder, match) {
                var placeholderId = tempPlaceholder.replace('%s', ++uid);
                placeholders[placeholderId] = func ? func.call(node, placeholder, false /* do not allow html */) : placeholder.toText(); 
                return placeholderId;
            });
            
            // replace our placeholder placeholders with valid html
            element.innerHTML = html.replace(new RegExp(tempPlaceholder.replace('%s', '[0-9]+'), 'g'), function(placeholderId) {
                if(placeholders[placeholderId]) {
                    return placeholders[placeholderId];
                }
                return placeholderId;
            });
        };
        
        
        /**
         * Simple text replacement
         * 
         */
        Placeholder.replace = function(text, func)
        {
            return text.replace(Placeholder.prototype.REGEX_PLACEHOLDER, function(match) {
                
                var placeholder = new Placeholder(match);
                if(!placeholder.valid) {
                    return;
                }
                
                if(func) {
                    return func.call(placeholder, placeholder, match); 
                }
                else {
                    return placeholder.toText();
                }
            } );
        };
    
    })(mzax.ui.Placeholder);
    
    
    





    mzax.ui.EditorField = Class.create({
        

        initialize: function(id, index) 
        {
            this.id = id || '';
            this.value = null;
            this.remove = false;
            this.removable = false;
            this.element = null;
            this.index = index||0;
            this.type = 'html';
            this._refresh = [];
            this.children = {};
            this.uid = _uid++;
        },
        
        
        
        sleep : function() 
        {
            return {
                remove: this.remove,
                alt: this.alt||null,
                value: this.getValue()
            };
        },
        
        load : function(data)
        {
            this.remove = !!data.remove;
            this.alt    = data.alt||null;
            this.value  = data.value||null;
            return this;
        },
        
        
        
        setElement : function(element)
        {
            if(!this.element) {
                this.element = element;
                this._clone = element.clone();
                element.data('field', this);
                this.parent = element.parents('[mage\\:id]').data('field');
                if( this.parent ) {
                    this.parent.children[this.id] = this;
                }
            }
            return this;
        },
        
        
        _walk: function(func)
        {
            var id, children = this.children;
            for(id in children) {
                if(children.hasOwnProperty(id)) {
                    func.call(this, children[id], id);
                }
            }
        },
        
        
        swap : function(field)
        {
            var index = this.index;
            this.index = field.index;
            field.index = index;
            
            this._walk(function(child, id) {
                child.swap(field.children[id]);
            });
            return this;
        },
        
        
        
        
        
        flagAsDeleted : function()
        {
            var property;
            this.deleted = true;
            this.parent = null;
            if(this.element) {
                if( this.cke ) {
                    this.cke.destroy(false);
                }
                this.element.remove();
            }
            
            for(property in this) {
                if(this.hasOwnProperty(property)) {
                    if(this[property] 
                    && typeof this[property] === 'object' 
                    && this[property].remove 
                    && this[property].jquery) 
                    {
                        this[property].remove();
                    }
                }
            }
            
            this._walk(function(child) {
                child.flagAsDeleted();
            });
        },
        
        
        
        
        clone : function()
        {
            return this._clone.clone();
        },
        
        
        
        
        setValue : function(value)
        {
            
            if(this.cke) {
                this.value = removeIndentation(value);
                this.cke.setData(value);
            }
            else if(this.type === 'html' && this.element) {
                this.value = removeIndentation(value);
                this.element.html(value);
                mzax.ui.Placeholder.replaceHtml(this.element.get(0));
                if(this.onHtmlChange) {
                    this.onHtmlChange(this.element);
                }
            }
            else if(this.type === 'css' && this.element) {
                this.value = removeIndentation(value);
                this.element.html(value);
                if(this.onHtmlChange) {
                    this.onHtmlChange(this.element);
                }
            }
            
            
            
            
            else {
                this.value = value;
            }
            return this;
        },
        
        getValue : function()
        {
            if(this.cke) {
                return this.cke.getData();
            }
            return this.value;
        },
        
        
        refreshUi : function(func)
        {
            var handlers = this._refresh,
                i = handlers.length;
            if(typeof func === 'function') {
                handlers.push(func);
            }
            else {
                while(--i > -1) {
                    handlers[i].call(this, func);
                }
            }
            return this;
        }
        
        
        
        
    });
    
    
    mzax.ui.EditorField.indexSort = function(a, b) {
        return a.index-b.index;
    };
    
    
    mzax.ui.PreviewFrame = Class.create({
        
        version : 1,
        
        initialize: function(div, options) 
        {
            var scope = this;
            
            scope.mediaUrl = '/media/';
            scope.skinUrl = '/skin/';
            scope.storeUrl = '/skin/';
            
            scope.$ = div;
            scope.ckeditorSrc = '/js/mzax/ckeditor/ckeditor.js';
            scope.jquerySrc = 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.js';
            scope.editorCss = '/skin/adminhtml/default/default/mzax/editor.css';
            scope.imageFlag = true;
            scope.enableCKEditor = true;
            scope.enableAce = true;
            
            Object.extend(this, options || {});
            
            scope.init();
            
            if(options.html) {
                scope.setHtml(options.html);
            }
            
            if(options.startEdit) {
                scope.editMode();
            }
            
        },
        
        
        init : function()
        {
            var editor = this, element = editor.$;
            element.innerHTML = '<div class="mzax-editor-header"></div>'
                              + '<iframe class="mzax-editor-frame"></iframe>'
                              + '<div class="mzax-editor-footer"></div>'
                              + '<div class="mzax-editor-input">'
                              +     '<div class="controls"></div>'
                              +     '<div class="input-wrapper"><textarea class="text-input"></textarea></div>'
                              + '</div>'
                              + '<div class="mzax-loader"><div class="label"></div></div>';
            
            element.addClassName('mzax-editor');
            editor.iframe = element.down('.mzax-editor-frame');
            editor.input  = element.down('.mzax-editor-input');
            editor.loader = element.down('.mzax-loader');
            
            
            editor.loader.down('.label').innerHTML = "Loading Editor";
            
            Element.hide(editor.input);
            Element.hide(editor.loader);
            
            if(editor.enableAce && ace) {
                editor.ace = ace.edit(element.down('.text-input'));
                editor.ace.setMageSnippets(editor.snippets);
                editor.ace.owner = editor;
                editor.ace.setTheme("ace/theme/mage");
                editor.ace.getSession().setMode("ace/mode/mage");
                editor.ace.setOptions({
                    enableBasicAutocompletion: true, 
                    enableSnippets: true, 
                    enableMageLiveAutocompletion:true
                });
                
                editor.ace.browserMedia = function() {
                    editor.browserMedia(function(value) {
                        editor.ace.insert(value);
                    }, false);
                };
                
                editor.ace.insertWidget = function() {
                    editor.insertWidget(function(value) {
                        editor.ace.insert(value);
                    }, false);
                };
            }
            
            
            
            var ctrlBar = element.down('.controls');
            editor._createButton({
                label: 'Apply',
                cls: 'save btn-right',
                target: ctrlBar,
                click: editor.applyChanges.bind(editor)
            });
            editor._createButton({
                label: 'Discard',
                cls: 'back btn-right',
                target: ctrlBar,
                click: editor.discardChanges.bind(editor)
            });
            
            
            
            // those buttons only work with ace
            if(editor.ace) {
                editor._createButton({
                    label: 'Insert Variable',
                    cls: 'mzax-code html-only',
                    target: ctrlBar,
                    click: function() {
                        editor.ace.execCommand('mage', {source: 'mage'});
                    }
                });
                editor._createButton({
                    label: 'Insert Image',
                    cls: 'mzax-image html-only',
                    target: ctrlBar,
                    click: function() {
                        editor.browserMedia(function(value) {
                            editor.ace.insert(value);
                        }, false);
                    }
                });
                
                editor._createButton({
                    label: 'Insert Widget',
                    cls: 'mzax-widget html-only',
                    target: ctrlBar,
                    click: function() {
                        editor.insertWidget(function(value) {
                            editor.ace.insert(value);
                        }, false);
                    }
                });
                
            }
            
            
        },
        
        
        /**
         * Generate new magento button
         * <button type="button" class="scalable mzax-widget"><span>{label}</span></button>
         * 
         * @param object options
         * @return Element
         */
        _createButton : function(options)
        {
            var btn = new Element('button', {type:'button', 'class': 'scalable ' + (options.cls||'')}).update('<span>'+options.label+'</span>');
            if(options.click) {
                btn.observe('click', options.click);
            }
            if(options.target) {
                options.target.insert(btn);
            }
            return btn;
        },
        
        
        
        
        /**
         * Set html for editor
         * 
         * @param string code
         * @return mzax.ui.PreviewFrame
         */
        setHtml : function(code, preview)
        {
            this._lastScrollTop = this.scrollTop();
            this.documentLoaded = false;
            this.editorReady = false;
            this.html = code;
            this.preview = preview||false;
            this.jQuery = null;
            this.CKEDITOR = null;
            this._customStyles = null;
            
            if(this.iframe) {
                try {
                    this.iframe.src = '';
                }
                catch(e) {}
            }
            
            
            if(this.cancelEditorLoad) {
                this.cancelEditorLoad();
            }
            if(this._docTimer) {
                clearInterval(this._docTimer);
            }
            Element.show(this.loader);
            this._docTimer = onReady(window.document, (function() {
                var doc = this.getDocument();
                doc.open();
                doc.write(code);
                doc.close();
                this.initDocument();
            }).bind(this));
            return this;
        },
        
        
        scrollTop : function(value)
        {
            if(this.html && this.documentLoaded) {
                if(typeof value !== 'undefined') {
                    this.getFrameWindow().scrollTo(0, value);
                }
                return this.getFrameWindow().scrollY;
            }
            return 0;
        },
        
        
        
        
        /**
         * Set value that assumes a JSON string
         * 
         * @param string value JSON
         * @return boolen Ture on success
         */
        setValue : function(value)
        {
            if(value) {
                try {
                    this.setData(JSON.parse(value));
                }
                catch(e) {
                    return false;
                }
            }
            else {
                this.setData(null);
            }
            return true;
        },
        
        
        
        
        setData : function(data)
        {
            if(!data) {
                this.fields = null;
                this.customCss = null;
                this.html && this.setHtml(this.html);
                return this;
            }
            this._data = data;
            this.fields = null;
            var id, i;
            if(data && data.fields) {
                for(id in data.fields) {
                    if(data.fields.hasOwnProperty(id)) {
                        for(i = 0; i< data.fields[id].length; i++) {
                            this.getField(id, i).load(data.fields[id][i]||{});
                        }
                    }
                }
            }
            
            if(data.customCss) {
                this.customCss = new mzax.ui.EditorField();
                this.customCss.load(data.customCss);
                this.customCss.type = 'css';
            }
            
            
            this.html && this.setHtml(this.html);
            return this;
        },
        
        

        getData : function()
        {
            this.refreshUi();
            
            var result = {
                    version: this.version
                },
                data = {}, 
                fields = this.fields, 
                field, i, id, entries;
            
            if( fields ) {
                for(id in fields) {
                    if(fields.hasOwnProperty(id)) {
                        entries = data[id] = [];
                        for(i = 0; i < fields[id].length; i++) {
                            if( field = fields[id][i] ) {
                                entries.push(field.sleep());
                            }
                        }
                    }
                }
            }
            
            if(this.customCss) {
                result.customCss = this.customCss.sleep();
            }
            
            result.fields = data;
            return result;
        },
        
        
        
        /**
         * Retrieve data as JSON
         * 
         * @return string
         */
        getValue : function() 
        {
            return JSON.stringify(this.getData());
        },
        
        
        /**
         * Render all placeholders
         * 
         * 
         */
        renderPlaceholders : function(skipEditables)
        {
            var editor = this;
            mzax.ui.Placeholder.replaceHtml(editor.getDocument().body, function(placeholder, allowHtml) {
                switch(placeholder.directive) {
                    case 'media':
                    case 'skin':
                        return editor.getImagePreviewUrl(placeholder.expr);
                }
                if( editor.snippets ) {
                    var snippet = find(editor.snippets, function(snippet) {
                        if(normalizeLabel(snippet.snippet) === normalizeLabel(placeholder.expr)) {
                            return snippet;
                        }
                    });
                    if(snippet) {
                        if(allowHtml && snippet.html) {
                            return snippet.html;
                        }
                        else if(snippet.text) {
                            return snippet.text;
                        }
                    }
                }
                return allowHtml ? placeholder.toHtml() : placeholder.toText();
            }, skipEditables || false);
        },
        
        
        
        
        initDocument : function()
        {
            var editor = this;
            editor.documentLoaded = true;
            if(editor.preview) {
                editor.renderPlaceholders();
                editor.editorCss && editor.loadStyle(editor.editorCss);
            }
            editor.scrollTop(editor._lastScrollTop || 0);
            
            if(editor.editModeFlag) {
                editor.loadEditor();
            }
            else {
                Element.hide(editor.loader);
            }
        },
        
        
        
        getDocument : function()
        {
            var iframe = this.iframe;
            if (iframe.contentWindow) {
                this.content = iframe.contentWindow.document;
             } 
             else {
                if (iframe.contentDocument && iframe.contentDocument.document) {
                    this.content = iframe.contentDocument.document;
                }
                else {
                    this.content = iframe.contentDocument;
                }
             }
            return this.content;
        },
        
        
        /**
         * Retrieve window object from frame
         * 
         * @return window
         */
        getFrameWindow : function()
        {
            var content = this.getDocument();
            return content.parentWindow || content.defaultView;
        },
        
        
        
        /**
         * CSS query select
         * 
         * Uses prototype js, no editor needs to be loaded
         * for this
         * 
         * @return Array
         */
        select : function(query)
        {
            var document = this.getDocument();
            if(document) {
                return Element.select(document, query);
            }
            return [];
        },
        
        
        
        get : function(query)
        {
            var result = this.select(query);
            if(result.length) {
                return result[0];
            }
            return null;
        },
        
        
        
        
        /**
         * Load Script
         * 
         * @param string source
         */
        loadScript : function(source)
        {
            if(!this.documentLoaded) {
                throw new Error("Document is not yet loaded and ready");
            }
            var doc = this.getDocument();
            onReady(doc, function() {
                var script = new Element('script', {type: 'text/javascript', src: source});
                var head = this.select('head');
                if( head.length === 1 ) {
                    head[0].appendChild(script);
                    console.log(script);
                }
                else {
                    alert("todo, no head tag found");
                }
                
            }, this);
        },
        
        
        
        /**
         * Load Style
         * 
         * @param string source
         */
        loadStyle : function(source)
        {
            if(!this.documentLoaded) {
                throw new Error("Document is not yet loaded and ready");
            }
            var doc = this.getDocument();
            onReady(doc, function() {
                var style = new Element('link', {href: source, rel:'stylesheet', type:'text/css', media:'all'});
                var head = this.select('head');
                if( head.length === 1 ) {
                    head[0].appendChild(style);
                }
                else {
                    alert("todo, no head tag found");
                }
                
            }, this);
        },
        
        
        
        
        /**
         * Load all the required local components for the editor
         * 
         * @param callback
         * @return mzax.ui.PreviewFrame
         */
        loadEditor : function()
        {
            // already loaded
            if(this.editorReady) {
                return this;
            }
            
            // document needs to be loaded
            if(!this.documentLoaded) {
                return this;
            }
                        
            var editor = this,
                timers = [],
                window = editor.getFrameWindow();
            
            Element.show(editor.loader);
            
            // function to remove all exsting load timers
            editor.cancelEditorLoad = function() {
                var i = timers.length;
                while(--i > -1) {
                    clearInterval(timers[i]);
                }
                editor.cancelEditorLoad = null;
            };
            
            // quick function to check for required components
            function loadCheck() 
            {
                if(editor.jquerySrc && !editor.jQuery) {
                    return;
                }
                // make sure ckeditor is loaded if enabled
                if(editor.enableCKEditor && !editor.CKEDITOR) {
                    return;
                }
                // make sure styles are available of attached
                if(editor.editorCss && !editor.cssLoaded) {
                    return;
                }
                editor.CKEDITOR && editor.initCKEditor(editor.CKEDITOR);
                editor.cancelEditorLoad = null;
                editor.initEditor();
                editor.editorReady = true;
            }
            
            // try loading ckeditor into iframe
            if(editor.enableCKEditor && editor.ckeditorSrc) {
                editor.CKEDITOR = null;
                editor.loadScript(editor.ckeditorSrc);
                var ckeTimer = setInterval(function() {
                    // @todo timeout?
                    if(window['CKEDITOR'] && window['CKEDITOR'].status === 'loaded') {
                        // store local reference
                        editor.CKEDITOR = window['CKEDITOR'];
                        clearInterval(ckeTimer);
                        loadCheck();
                    }
                },10);
                timers.push(ckeTimer);
            }
            
            
            if(editor.jquerySrc) {
                editor.jQuery = null;
                editor.loadScript(editor.jquerySrc);
                var jqueryTimer = setInterval(function() {
                    // @todo timeout?
                    if(window['jQuery']) {
                        // init jquery
                        editor.initJQuery(window['jQuery']);
                        clearInterval(jqueryTimer);
                        loadCheck();
                    }
                },10);
                timers.push(jqueryTimer);
            }
            
            
            
            // load editor CSS into iframe
            if(editor.editorCss) {
                editor.cssLoaded = false;
                editor.loadStyle(editor.editorCss);
                // not a real check lets just wait a bit
                var cssTimer = setInterval(function() {
                    editor.cssLoaded = true;
                    clearInterval(cssTimer);
                    loadCheck();
                }, 1000);
                timers.push(cssTimer);
            }
            
            // check in case nothing is required
            loadCheck();
            return this;
        },
        
        
        
        /**
         * Init editor
         * 
         * Called once all components for the editor are loaded
         * 
         */
        initEditor: function()
        {
            var editor = this,
                CKEDITOR = editor.CKEDITOR,
                win = editor.getFrameWindow(),
                $ = editor.jQuery,
                body = $('body');
            
            
            // make sure to refresh the UI after scoll and resize
            $(win).on('resize scroll', function() {
                editor.refreshUi(10);
            });
            
            // as well as after any image has loaded
            $('img').on('load error', function() {
                editor.refreshUi(10);
            });
            
            
            // Prevent the backspace key from navigating back.
            $(win).bind('keydown', function (event) {
                if (event.keyCode === 8 /* BACKSPACE */) {
                    if(!$(event.target).is('.cke_editable,:input')) {
                        event.preventDefault();
                        return;
                    }
                    if(editor.CKEDITOR && editor.CKEDITOR.currentInstance) {
                        try {
                            if(editor.CKEDITOR.currentInstance.getSelection().getRanges().length === 0 ) {
                                event.preventDefault();
                            }
                        }
                        catch(err) {
                            event.preventDefault();
                        }
                    }
                }
            });
            
            
            
            if(body.length) {
                
                editor.renderPlaceholders(true);
                
                function getBodyMargin() {
                    return parseInt($('body').css('margin-top'));
                }
                
                
                var removeMarker = $('<div class="mzax-remove-marker mzax-marker" />').appendTo(body),
                    repeatMarker = $('<div class="mzax-repeat-marker mzax-marker" />').appendTo(body),
                    swapMarkerA  = $('<div class="mzax-swap-marker mzax-marker mzax-a" />').appendTo(body),
                    swapMarkerB  = $('<div class="mzax-swap-marker mzax-marker mzax-b" />').appendTo(body);
                    customCss  = $('<div class="mzax-custom-css" />').html('&laquo;CSS&raquo;').appendTo(body);
                
                

                $.fn.highlight = function() {
                    var element = this;
                    // quickly highlight element
                    return element.scrollIntoView(function() {
                       repeatMarker.moveOnTop(element).show().fadeOut(1000);
                   },300);
               };
               
               
               if(editor.allowCustomCss !== false) {
                   editor.customCss = editor.customCss || new mzax.ui.EditorField();
                   editor.customCss.type = 'css';
                   editor.customCss.setElement($('<style />').appendTo('head').html(editor.customCss.getValue()));
                   customCss.click(function() {
                       editor.editField(editor.customCss);
                   });
               }
               else {
                   customCss.remove();
               }
               
               
                /**
                 * EDITABLE WORKER
                 * 
                 */
                function initWorker()
                {
                    var field,
                        element = $(this),
                        id = element.mage('id');
                
                    if(!id) {
                        return;
                    }
                    
                    field = editor.getField(id, element.mage('index')).setElement( element );
                }
                
                
                
                
                /**
                 * EDITABLE WORKER
                 * 
                 */
                function editableWorker() 
                {
                    var field, ckElement,
                        element = $(this),
                        id = element.mage('id');
                    
                    if(!id || !isTrue(element.mage('editable'))) {
                        return;
                    }
                    
                    field = editor.getField(id, element.mage('index')).setElement( element );
                    
                    
                    // Special treatment for images
                    if(element.is('img')) {
                        field.type = 'image';
                        element.on({
                            click: function(event) {
                                editor.editField(field, event);
                            },
                            mouseenter: function() {
                                element.addClass('mzax-editor-highlight-image');
                            },
                            mouseleave: function() {
                                element.removeClass('mzax-editor-highlight-image');
                            }
                        });
                        if(typeof field.value !== 'string' ) {
                            field.value = element.attr('src');
                        }
                        if(typeof field.alt !== 'string' ) {
                            field.alt = element.attr('alt') || '';
                        }
                        // try to load a preview of the image
                        element.attr('src', editor.getImagePreviewUrl(field.value));
                        element.attr('alt', field.alt);
                        return;
                    }
                    
                    
                    if(typeof field.value === 'string' ) {
                        element.html(field.value);
                    }
                    else {
                        field.setValue(element.html());
                    }
                    
                    
                   
                    // CKEDITOR is optional make sure its enabled be for working with it
                    if(CKEDITOR) {
                        // TD tags require a div tag in order to make correctly
                        if(element.is('td')) {
                            ckElement = $('<div class="mzax-inline-ckeditor" contenteditable="true" />')
                                .html(field.value)
                                .appendTo(element.empty());
                            
                        }
                        // DIV tags can just stay divs
                        else if(element.is('div')) {
                            ckElement = element.addClass('mzax-inline-ckeditor').addAttr('contenteditable', true);
                        }
                        // if Tag is not supported fall back to normal method
                        if(ckElement) {
                            field.cke = CKEDITOR.inline(ckElement[0], {
                                autoParagraph: false,
                                allowedContent: true,
                                readOnly: false,
                                extraPlugins: 'mage_code,mzax_editor,mzax_image',
                                format_tags: 'p;h1;h2;h3;h4;h5;h6;pre;address;div',
                                stylesSet: editor.getCustomStyles(),
                                sharedSpaces: {
                                    top: 'mzax-ckeditor-top'
                                }
                            });
                            field.cke.field = field;
                            field.cke.on( 'instanceReady', function( event ) {
                                event.editor.setReadOnly(false);
                            });
                            return;
                        }
                    }
                    
                    // use normal click & edit method
                    element.on({
                        click: function(event) {
                            editor.editField(field, event);
                        },
                        mouseenter: function() {
                            element.addClass('mzax-editor-highlight');
                        },
                        mouseleave: function() {
                            element.removeClass('mzax-editor-highlight');
                        }
                    });
                    
                }
                
                
                
                /**
                 * REMOVABLE WORKER
                 * 
                 */
                function removableWorker() {
                    var removeHandle, handleContainer, field, 
                        ownerDomain = body,
                        element = $(this),
                        isRepeatable = isTrue(element.mage('repeatable')),
                        isRemovable  = isTrue(element.mage('removable')),
                        id = element.mage('id');
                    
                    if(!id || (!isRemovable && !isRepeatable) ) {
                        return;
                    }
                    
                    if(element.is('.mzax-removable-worker')) {
                        return;
                    }
                    
                    
                    removeHandle = $('<div class="mzax-remove-handle"><span>'+normalizeLabel(id)+'</span></div>')
                        .appendTo(body)
                        .hide();
                    
                    if(isRepeatable) {
                        removeHandle.addClass('mzax-repeatable');
                    }
                    
                    field = editor.getField(id, element.mage('index')).setElement(element);
                    field.removable = true;
                    field.removeHandle = removeHandle;
                    
                    
                    
                    if(field.parent) {
                        ownerDomain = field.parent.element;
                        removeHandle.addClass('mzax-field-'+field.parent.uid);
                    }
                    
                    
                    
                    element
                        .addClass('mzax-removable-worker')
                        .on({
                        mouseenter: function(event) {
                            clearTimeout(hideTimeout);
                            
                            if(isRemovable || field.index) {
                                editor.refreshUi();
                            }
                        },
                        mouseleave: function(event) {
                            if(!removeHandle.hasElement(event.relatedTarget)) {
                                removeHandle.hide();
                            }
                        },
                        mousemove: function(event)
                        {
                            if(!isRepeatable) {
                                removeHandle.css({
                                    top:event.pageY - removeHandle.height()/2,
                                    left: element.offset().left - removeHandle.width()/2,
                                    opacity: 1.2-((event.pageX - $(this).offset().left)/140)
                                }).show();
                            }
                            else if(field.index) {
                                removeHandle.show();
                            }
                            
                        }
                        
                    });
                    
                    var hideTimeout;
                    removeHandle.on({
                        mousemove: function(event)
                        {
                            if(!field.remove && !isRepeatable) {
                                var y = element.offset().top,
                                    h = element.height(),
                                    h2 = removeHandle.height()/2;
                                removeHandle.css({
                                    top:limit(event.pageY - h2, y-h2, y+h-h2)
                                });
                            }
                        },
                        mouseenter: function() {
                            clearTimeout(hideTimeout);
                                                        
                            if(!field.remove) {
                                removeMarker.moveOnTop(element).show();
                            }
                        },
                        mouseleave: function(event) {
                            removeMarker.hide();
                            if(!field.remove) {
                                removeHandle.hide();
                                /*
                                hideTimeout = setTimeout(function() {
                                    removeHandle.hide();
                                },5);*/
                            }
                        },
                        click: function(event) {
                            if(isRemovable) {
                                clearTimeout(hideTimeout);
                                var flag = field.remove = !field.remove;
                                element.toggleClass('mzax-removed', flag)[flag?'fadeOut':'fadeIn']('fast');
                                removeHandle.toggleClass('mzax-removed', flag).fixedPosition(flag && !field.parent);
                                removeMarker.hide();
                                editor.refreshUi();
                                
                                if(!flag /* on insert*/) {
                                    removeHandle.hide();
                                    element.highlight();
                                }
                            }
                            else if(isRepeatable && field.index) {
                                if(confirm("Are you sure?")) {
                                    field.element.fadeOut(300, function() {
                                        field.flagAsDeleted();
                                        editor.reindexFields();
                                    });
                                    removeMarker.fadeOut(300);
                                    removeHandle.hide();
                                }
                            }
                        }
                        
                        
                    });
                                
                    if(field.remove) {
                        element.addClass('mzax-removed');
                        removeHandle.addClass('mzax-removed').fixedPosition(!field.parent);
                        // CKEDITOR needs a moment before we can hide it
                        setTimeout(function() {
                            element.hide();
                        }, 500);
                        
                    }
                    
                    field.refreshUi(function(data) {
                        var offset, top, prev, left;
                        if(!isRemovable && !field.index) {
                            removeHandle.hide();
                            return;
                        }
                        if(!element.is('.mzax-removed')) {
                            var offset = element.offset();
                            if(isRepeatable) {
                                offset.left += element.width() - removeHandle.width() +3;
                                offset.top  += -3;
                            }
                            else {
                                offset.left -= removeHandle.width()/2;
                                delete offset.top;
                                //offset.top  += element.height()/2 - removeHandle.height()/2;
                            }
                            removeHandle.css(offset);
                        }
                        else {
                            if(element.parents('.mzax-removed').length) {
                                removeHandle.hide();
                            }
                            else {
                               
                                if(ownerDomain === body) {
                                    top  = data.topOffset + 10;
                                    left = 5;
                                    prev = removeHandle.prevAll('.mzax-remove-handle.mzax-removed:visible');
                                }
                                else {
                                    offset = ownerDomain.offset();
                                    top  = offset.top + 25;
                                    left = Math.max(offset.left + ownerDomain.width());
                                    prev = removeHandle.prevAll('.mzax-remove-handle.mzax-field-'+field.parent.uid);
                                }
                                
                                removeHandle.show().animate({
                                    top: top + (prev.length * (removeHandle.height() + 5)), 
                                    left: left,
                                    opacity: 1
                                },{
                                    duration:300,
                                    complete: function() {
                                        // just in case
                                        removeHandle.show();
                                    }
                                });
                            }
                            
                        }
                    });
                }
                
                
                

                /**
                 * REPEATABLE WORKER
                 * 
                 */
                function repeatableWorker() {
                    
                    var repeatHandle, field, swapHandler,
                        highlightClass = 'mzax-editor-highlight-repeat',
                        element = $(this),
                        id = element.mage('id');
                    
                    
                    if(!id || !isTrue(element.mage('repeatable'))) {
                        return;
                    }
                    
                    // repeatable elements shall not have repeatable parents
                    // no need to overcomplicate things here for now
                    if(element.parents('[mage\\:repeatable]').length) {
                        element.removeAttr('mage:repeatable');
                        return;
                    }
                    
                    repeatHandle = $('<div class="mzax-repeat-handle"></div>').appendTo(body).hide();
                    swapHandler = $('<div class="mzax-swap-handle"></div>').appendTo(body).hide();
                    
                    
                    field = editor.getField(id, element.mage('index')).setElement(element);
                    field.repeatable   = true;
                    field.repeatHandle = repeatHandle;
                    field.swapHandler  = swapHandler;
                    
                    var swap, swapDir;
                    element.on({
                        
                        mousemove: function(event)
                        {
                            var offsetY = event.pageY - $(this).offset().top;
                            var offsetX = event.pageX - $(this).offset().left;
                            var visible = swapHandler.is(":visible"), yPos = 0;
                            var threshold = Math.min(element.height()/2-5, 100);
                            
                            if(offsetY < threshold) {
                                if(!visible && (swap = element.prev('[mage\\:id="'+id+'"]:visible')).length) {
                                    visible = true;
                                    swapDown = false;
                                }
                            }
                            else if(offsetY > element.height()-threshold) {
                                if(!visible && (swap = element.next('[mage\\:id="'+id+'"]:visible')).length) {
                                    visible = true;
                                    swapDown = true;
                                }
                                yPos = element.height();
                            }
                            else {
                                visible = false;
                            }
                            
                            if(visible) {
                                var offset = element.offset();
                                offset.top  += yPos - swapHandler.height()/2;
                                offset.left += element.width() - 20 - swapHandler.width();
                                offset.opacity = Math.min(1.5-(element.width()-offsetX)/100,
                                                          1.2-Math.abs(yPos-offsetY)/50);
                                swapHandler.css(offset);
                            }
                            $('.mzax-swap-handle').not(swapHandler).hide();
                            swapHandler.toggle(visible);
                            
                            
                            repeatHandle.css({
                                opacity: 1.1-(element.height()-offsetY)/100
                            });
                            
                            
                        },
                    
                        
                        
                        
                        mouseenter: function() {
                            $('.mzax-repeat-handle').hide();
                            repeatHandle.show();
                            editor.refreshUi();
                        },
                        mouseleave: function(event) {
                            if(!swapHandler.hasElement(event.relatedTarget)) {
                                setTimeout(swapHandler.hide.bind(swapHandler),10);
                            }
                            if(!repeatHandle.hasElement(event.relatedTarget)) {
                                repeatHandle.hide();
                                
                            }
                        }
                    });
                    
                    
                    
                    
                    swapHandler.on({
                        
                        mouseenter: function()
                        {
                            var bgSize = Math.min(element.height(), swap.height(), 60)+'px';
                            
                            swapMarkerA.moveOnTop(swap)
                                .toggleClass('mzax-a', swapDown)
                                .toggleClass('mzax-b', !swapDown)
                                .css('background-size',bgSize)
                                .flicker().show();
                            
                            swapMarkerB.moveOnTop(element)
                                .toggleClass('mzax-b', swapDown)
                                .toggleClass('mzax-a', !swapDown)
                                .css('background-size',bgSize)
                                .flicker().show();
                            
                        },
                        
                        mouseleave : function() 
                        {
                            swapMarkerA.moveOnTop(swap).flicker(false).hide();
                            swapMarkerB.moveOnTop(element).flicker(false).hide();
                           // swap.flicker(false);
                           // element.flicker(false);
                        },
                        
                        click: function(event) {
                            console.log("SWAP "+swapDown);
                            if(swap.length) {
                                swap[!swapDown ? 'insertAfter' : 'insertBefore'](element).data('field').swap(field);
                                swapDown = !swapDown;
                                editor.refreshUi();
                            }
                            swapHandler.hide();
                        }
                    });
                    
                    
                    
                    
                    repeatHandle.on({
                        
                        mouseenter: function()
                        {
                            repeatMarker.moveOnTop(element).show();
                        },
                        
                        mouseleave : function() 
                        {
                            repeatMarker.hide();
                        },
                        
                        click: function(event) {
                            
                            var nextIndex = field.index+1;
                            
                            editor.insertField(field.id, nextIndex);
                            
                            var element = field.clone()
                                // set index for all child items
                                .find('[mage\\:id]').andSelf().attr('mage:index', nextIndex).end().end()
                                .insertAfter(field.element)
                                
                                // run workers on all elements
                                .each(removableWorker)
                                .each(repeatableWorker)
                                .find('[mage\\:editable]').each(editableWorker).end()
                                .find('[mage\\:removable]').each(removableWorker).end();
                            
                            element.highlight();
                        }
                    });
                    
                    var fields = editor.fields[id];
                    if(fields.length > 1) {
                        for(var i = 1; i < fields.length; i++) {
                            if(!fields[i].element) {
                                field.clone()
                                    // set index for all child items
                                    .find('[mage\\:id]').andSelf().attr('mage:index', i).end().end()
                                    .insertAfter(field.element)
                                    
                                    // run workers on all elements
                                    .each(removableWorker)
                                    .each(repeatableWorker)
                                    .find('[mage\\:editable]').each(editableWorker).end()
                                    .find('[mage\\:removable]').each(removableWorker).end();
                            }
                        }
                    }
                    
                    
                    field.refreshUi(function(data) {
                        var offset = element.offset();
                        offset.top  += element.height()  - repeatHandle.height()/2;
                        offset.left += element.width()/2 - repeatHandle.width()/2;
                        
                        repeatHandle.offset(offset);
                        
                        /*
                        var offset = element.offset();
                            offset.top  -= swapHandler.height()/2;
                            offset.left += element.width()/4 - swapHandler.width()/2;
                            
                        swapHandler.offset(offset);*/
                    });
                    
                }
                
                $('[mage\\:id]').each(initWorker);
                $('[mage\\:editable]').each(editableWorker);
                $('[mage\\:repeatable]').each(repeatableWorker).each(removableWorker);
                $('[mage\\:removable]').each(removableWorker);
                
                
                /*
                setTimeout(function() {
                    $('[mage\\:removable]').each(removableWorker);
                },1000);*/
                
            }
            
            
            
            this.disableLinks(function(link) {
                alert("Link ("+link.href+")");
            });
            
            
            this.refreshUi();
            setTimeout(function() {
                Element.hide(editor.loader);
            }, 500);
        },
        
        
        
        /**
         * Initialize the CKEDITOR object
         * 
         * @param CKEDITOR
         * @return void
         */
        initCKEditor : function(CKEDITOR)
        {
            var editor = this,
                $ = editor.jQuery;
            
            editor.CKEDITOR = CKEDITOR;
            
            CKEDITOR.owner = editor;
            CKEDITOR.mzax = mzax;
            CKEDITOR.mageSnippets = editor.snippets || {};
            CKEDITOR.disableAutoInline = true;
            CKEDITOR.plugins.addExternal( 'mage_code,mzax_editor,mzax_image', '/js/mzax/', 'ckeditor.plugin.mage.js');
            CKEDITOR.on('instanceReady', editor.refreshUi.bind(editor));
            CKEDITOR.on('currentInstance', editor.refreshUi.bind(editor));
            CKEDITOR.selectImageSource = function(setValueFunc) {
                editor.browserMedia(setValueFunc, true /* mediaExprOnly */);
            };
            CKEDITOR.getImagePreviewUrl = editor.getImagePreviewUrl.bind(editor);
            
            
            $('body').addClass('mzax-ckeditor-enabled');
            $('<div id="mzax-ckeditor-top" />').prependTo('body');
        },
        
        
        
        /**
         * Initialize jQuery
         * 
         * @param jQuery $
         * @return void
         */
        initJQuery : function($)
        {
            var editor = this,
                win = $(editor.getFrameWindow());
            
            editor.jQuery = $;
            registerJQueryPlugins($);
            
            
            /**
             * Retrieve a mage:xza attribute
             * 
             * @param string attribute mage attribute name
             * @return string
             */
            $.fn.mage = function(attribute) {
                return this.attr('mage:' + attribute);
            };
            
            
            
            /**
             * Convert element from fixed to absolute
             * without chaingin its position
             * 
             * @param boolen flag
             * @return jQuery chaining
             */
            $.fn.fixedPosition = function(flag) 
            {
                var offset   = this.offset(),
                    position = flag ? 'fixed' : 'absolute',
                    flag     = flag ? -1 : 1;
                
                if(this.css('position') === position) {
                    return this;
                }
                return this.css({
                    position: position,
                    top: offset.top + $('body').scrollTop()*flag,
                    left: offset.left + $('body').scrollLeft()*flag,
                });
            };
            
            
           
            /**
             * Check if the element contains
             * the given element
             * 
             * @param jQuery element The element to look for
             * @return boolean
             */
            $.fn.hasElement = function(element) {
                return (this.length > 0 && (this.index(element) !== -1 || $.contains(this[0], element)));
            };
            
            
            
            /**
             * Place an absolute element on top of the
             * specified element
             * 
             * @param jQuery element The element to look for
             * @return jQuery Chaining
             */
            $.fn.moveOnTop = function(element) {
                var offset = element.offset();
                return this.css({
                    left:offset.left,
                    top:offset.top,
                    height:element.height(),
                    width:element.width()
                });
            };
            
            
            
            /**
             * Scroll element into view
             * 
             * at the moment we check only for vertical alignment,
             * shout be good enough for emails
             */
            $.fn.scrollIntoView = function(callback, duration)
            {
                var doc = $('html, body'),
                    margin = parseInt($('body').css('margin-top')),
                    scrollTop = win.scrollTop(),
                    offset = this.offset();
                
                // don't do anything if it is in view
                // todo left scroll?
                if(scrollTop < offset.top && scrollTop + win.height() > offset.top + this.height()) {
                    callback.call(this);
                    return this;
                }
                
                $('html, body').animate({
                    scrollTop: offset.top-20-margin
                },{
                    duration: duration || 150,
                    complete :callback
                });
            };
            
            
            
            /**
             * Make object flicker by randomly chainging
             * its opacity value until stop.
             * 
             * @param boolean flag 
             * @param number speed The duration of the animations
             * @return self
             */
            $.fn.flicker = function(flag, speed) {
                var element = this;
                if(flag !== false) {
                    var animate = function() {
                        element.animate({opacity:limit(Math.random(), 0.2,1)}, {
                            duration: speed || 500,
                            queue:'flicker',
                            complete: animate
                        }).dequeue('flicker');
                    };
                    animate();
                }
                else {
                    element.stop('flicker', true, false).css('opacity', '');
                }
                return this;
            };
            
        }, // END initJQuery
        
        
        
        
        /**
         * Take a given src from the editor and try to
         * convert it to an actuall URL
         * 
         * Image urls sometimes contain placeholders as src path,
         * with simple reformating we can retrieve the actual image
         * url.
         * 
         * @param string src
         * @return string
         */
        getImagePreviewUrl : function(src)
        {
            var placeholder = new mzax.ui.Placeholder(src);
            if( placeholder.valid && placeholder.params['url']) {
                var baseUrl = this[placeholder.directive+'Url'] || '/';
                return baseUrl + placeholder.params['url'];
            }
            return src;
        },
        
        
        
        
        /**
         * Reindex the editor fields.
         * 
         * Index can change when inserting, deleting, swaping
         * the elements. Then we should reindex those fields
         * 
         * @param func Optional function to call for each field
         * @return void
         */
        reindexFields : function(func)
        {
            var editor = this,
                fields = editor.fields, field, id, i, reindex;
            
            if( fields ) {
                for(id in fields) {
                    if(fields.hasOwnProperty(id)) {
                        reindex = [];
                        i = fields[id].length;
                        while(--i > -1) {
                            if( (field = fields[id][i]) && !field.deleted ) {
                                reindex.push(field);
                            }
                        }
                        reindex = reindex.sort(mzax.ui.EditorField.indexSort);
                        i = reindex.length;
                        while(--i > -1) {
                            reindex[i].index = i;
                            if(func) {
                                func.call(reindex[i], reindex[i], i);
                            }
                        }
                        fields[id] = reindex;
                    }
                }
            }
        },
        
        
        
        /**
         * Update all UI elements
         * 
         * Refresh all ui elements and give each editor
         * field a chance to update its UI as well.
         * 
         * The refresh can be delayed so not to many call get made at once,
         * helpfull for scroll and resize events
         * 
         * @param integer delay
         * @return void
         */
        refreshUi : function(delay)
        {
            var editor = this,
                $ = editor.jQuery,
                window = $ ? $(editor.getFrameWindow()) : null;
            
            if(this._refreshTimer) {
                clearTimeout(this._refreshTimer);
            }
            
            function refresh() {
                console.log("refreshUi");
                
                // resize ace instance
                if( editor.ace ) {
                    editor.ace.resize();
                }
                
                if(!$) {
                    return;
                }
                
                var data = {
                    window: window,
                    editor: editor,
                    topOffset: $('#mzax-ckeditor-top').height(),
                    height: window.height()
                };
                
                data.bottom = window.scrollTop() + data.height;
                data.top = window.scrollTop() + data.topOffset;
                
                
                // add space at top for floating ckeditor
                if(editor.CKEDITOR) {
                    $('body').css('margin-top', data.topOffset);
                }
                
                // reindex and update fields
                editor.reindexFields(function(field) {
                    field.refreshUi(data);
                });
            }
            if(delay === false) {
                refresh();
            }
            else {
                this._refreshTimer = setTimeout(refresh, delay||5);
            }
        },
        
        
        
        /**
         * Retrieve EditorField by its id and index
         * 
         * @param string id
         * @param number index
         * @return mzax.ui.EditorField
         */
        getField : function(id, index)
        {
            var editor = this,
                fields = editor.fields || (editor.fields = {});
            
            index = index||0;
            
            if(!fields[id]) {
                fields[id] = [];
            }
            if(!fields[id][index]) {
                fields[id][index] = new mzax.ui.EditorField(id, index*1);
                fields[id][index].onHtmlChange = function(element) {
                    element.find('img').on('load error', editor.refreshUi.bind(editor));
                };
            }
            return this.fields[id][index];
        },
        
        
        
        /**
         * If we want to insert a field between two existing fields
         * we need to make some space
         * 
         * @param string id
         * @param number index
         * @return void
         */
        insertField : function(id, index)
        {
            var editor = this, field, id, i,
                fields = editor.fields || (editor.fields = {});
            
            index = index||0;
            
            
            if(fields.hasOwnProperty(id)) {
                i = fields[id].length;
                while(--i >= index) {
                    if(field = fields[id][i]) {
                        field.index++;
                        delete fields[id][i];
                        fields[id][tmp.index] = field;
                    }
                }
                
                // do the same for all child elements
                if( field ) {
                    field._walk(function(child, id) {
                        editor.insertField(id, index);
                    });
                }
            }
        },
        
        
        /**
         * Method for editing any field
         * 
         * @param field field
         * @param Event event Optional event, usally click event
         * @return void
         */
        editField : function(field, event)
        {
            var editor = this;
            editor.activeField = field;
            
            editor.$.select('.controls .html-only').map(Element.hide);
            
            
            switch(field.type) {

            
                case 'css':
                    if(editor.ace) {
                        editor.ace.getSession().setMode("ace/mode/css");
                        editor.ace.setValue(field.getValue());
                        editor.ace.clearSelection();
                    }
                    else {
                        editor.input.down('textarea').value = field.getValue();
                    }
                    Element.show(editor.input);
                    break;
            
            
                case 'html':
                    if(editor.ace) {
                        editor.ace.getSession().setMode("ace/mode/mage");
                        editor.ace.setValue(field.getValue());
                        editor.ace.clearSelection();
                    }
                    else {
                        editor.input.down('textarea').value = field.getValue();
                    }
                    editor.$.select('.controls .html-only').map(Element.show);
                    Element.show(editor.input);
                    break;
                    
                case 'image':
                    if(event && event.altKey) {
                        field.alt = window.prompt("Please enter a alt text", field.alt);
                        if(field.element) {
                            field.element.attr('alt', field.alt);
                        }
                    }
                    else {
                        function setSrc(src) {
                            if(src) {
                                field.setValue(src);
                                field.element.attr('src', editor.getImagePreviewUrl(src));
                            }
                        }
                        if(event && event.metaKey) {
                            setSrc(window.prompt("Please enter any source path you like", field.value));
                        }
                        else {
                            editor.browserMedia(setSrc, true);
                        }
                    }
                    break;
            }
            
            this.refreshUi();
        },
        
        
        
        
        
        /**
         * Retrieve available style configurations from html code
         * A simple regex css parser for extracting styles
         * 
         * @return iFrame.Array
         */
        getCustomStyles : function()
        {
            if(this._customStyles) {
                return this._customStyles;
            }
            
            var i,j,styles,rules,name,selector,css,
                // make sure to use array from frame context
                result = this.getFrameWindow().Array();
            if(this.html) {
                
                if(css = this.html.match(/<style(\s+type="text\/css")?\s*>([\s\S]*)<\/style>/gmi)) {
                    i = css.length;
                    while(--i > -1) {
                        if(rules = css[i].match(/\/\*\*[^{]*@name[\s\S]+?\*\/\s*[a-z0-9]+\.[a-z0-9_-]+\s*{[^}]*}/gmi)) {
                            j = rules.length;
                            while(--j > -1) {
                                name     = rules[j].match(/@name\s+([a-z0-9 _-]+)/i);
                                selector = rules[j].match(/([a-z0-9]+)\.([a-z0-9]+)\s{/i);
                                styles   = rules[j].match(/{([^}]+)?}/i);
                                
                                if(name && selector) {
                                    result.push({
                                        name: name[1],
                                        element: selector[1],
                                        attributes: {
                                            'class': selector[2]
                                        },
                                        _ST: this.CKEDITOR.tools.normalizeCssText(styles[1], true)
                                    });
                                }
                            }
                        }
                    }
                }
                this._customStyles = result;
            }
            return result;
        },
        
        
        
        
        
        /**
         * Apply all changes to the current active element and close editor
         * 
         */
        applyChanges : function()
        {
            var field = this.activeField;
            if(field) {
                if(field.type === 'html' || field.type === 'css') {
                    if(this.ace) {
                        field.setValue(this.ace.getValue());
                    }
                    else {
                        field.setValue(this.input.down('textarea').value);
                    }
                }
            }
            this.activeField = null;
            Element.hide(this.input);
            return this;
        },
        
        
        /**
         * Discard any changes on the current element and close editor
         * 
         */
        discardChanges : function()
        {
            this.activeElement = null;
            this.activeObject = null;
            Element.hide(this.input);
            return this;
        },
        
        
        
        
        
        editMode : function()
        {
            this.editModeFlag = true;
            this.loadEditor();
        },
        
        
        
        /**
         * Retrieve a list of all elements that have any
         * background image defined with css
         * 
         * @return Array
         */
        getBackgroundElements : function()
        {
            if(!this._bgElements) {
                var result = [];
                $A(this.getDocument().getElementsByTagName('*')).each(function(element) {
                    var style = window.getComputedStyle(element);
                    if(style.backgroundImage != 'none') {
                        result.push({
                            element: element,
                            background: style.backgroundImage
                        });
                    }
                });
                this._bgElements = result;
            }
            return this._bgElements;
        },
        
        
        
        /**
         * Toggle Images
         * 
         * This will hide/show all images inside the preview
         */
        toggleImages : function(flag)
        {
            this.imageFlag = flag = flag !== undefined ? !!flag : !this.imageFlag;
            
            /* hide <img> tag images*/
            this.select('img').each(function(img) {
                if(flag) {
                    img.src = img._src;
                }
                else {
                    img._src = img._src||img.src;
                    img.src = '';
                }
            });
            
            /* hide css background images */
            this.getBackgroundElements().each(function(item) {
                Element.setStyle(item.element, {backgroundImage: flag ? item.background : 'none'});
            });
        },
        
        
        
        /**
         * Browse Magento Media
         * 
         * Opens if media browser url is defined the magento media
         * browser.
         * Provide a callback set will recive the value once selected.
         */
        browserMedia : function(callback, mediaExprOnly) 
        {
            if(this.mediaBrowserUrl) {
                MediabrowserUtility.browse(this.mediaBrowserUrl, function(src) {
                    if(src) {
                        var m = src.match(/\{\{([^\[\}])+\}\}/);
                        callback(mediaExprOnly && m ? m[0] : src);
                    }
                });
            }
        },
        
        
        insertWidget : function(callback)
        {
            if(this.widgetToolsUrl) {
                widgetTools.openDialog(this.widgetToolsUrl, function(src) {
                    if(src) {
                        callback(src);
                    }
                });
            }
        },
        
        
        
        disableLinks : function(onclick)
        {
            var self = this;
            this.select('a').each(function(a) {
                Element.observe(a, 'click', function(event) {
                    Event.stop(event);
                    if(onclick) {
                        onclick.call(self, a);
                    }
                });
            });
        },
        
        
        loadTemplate : function(templateId)
        {
            if(this.templateLoadUrl) {
                var editor = this;
                Element.show(editor.loader);
                new Ajax.Request(editor.templateLoadUrl, {
                    parameters:{template: templateId},
                    evalJSON: true,
                    onComplete: function(transport) {
                        try {
                            if(!transport.responseJSON) {
                                throw "No valid JSON response: "+transport.responseText;
                            }
                            var d = transport.responseJSON;
                            if(d.error) {
                                throw (d.message || 'Unknown server error');
                            }
                            
                            if(d.html) {
                                editor.setHtml(d.html);
                            }
                            
                        } catch(e) {
                            alert(e);
                        }
                    }
                });
            }
        },
        
        
        quicksave : function(url, fieldName)
        {
            var editor = this,
                extraFields = editor.quicksaveFields;
            
            url = url || editor.quicksaveUrl;
            fieldName = fieldName || editor.fieldName;
            
            if(url && fieldName) {
                var postBody = ['data[' + fieldName + ']=' + encodeURIComponent(editor.getValue())];
                if(FORM_KEY) {
                    postBody.push('form_key=' + FORM_KEY);
                }
                if(extraFields) {
                    
                    walkObject(extraFields, function(fieldId, fieldName) {
                        var field = $(fieldId);
                        if( field ) {
                            postBody.push('data[' + fieldName + ']=' + encodeURIComponent(field.value));
                        }
                    });
                    /*
                    for(fieldName in extraFields) {
                        if(extraFields.hasOwnProperty(fieldName)) {
                            field = extraFields[fieldName]
                        }
                    }
                    
                    this.quicksaveFields.forEach(function(fieldId) {
                        var field = $(fieldId);
                        if(field.name) {
                            postBody.push(field.name + '=' + encodeURIComponent(field.value));
                        }
                    });*/
                }
                new Ajax.Request(url, {
                    postBody:postBody.join('&'),
                    evalJSON: true,
                    onComplete: function(transport) {
                        try {
                            if(!transport.responseJSON) {
                                throw "No valid JSON response: "+transport.responseText;
                            }
                            var d = transport.responseJSON;
                            if(d.error) {
                                throw (d.message || 'Unknown server error');
                            }
                            
                            var noitfy = window.previewWindows || [];
                            var i = noitfy.length;
                            while(--i > -1) {
                                try {noitfy[i] && noitfy[i].call(editor, {id:editor.htmlId, editor:editor});}
                                catch(e) {/* preview popup has been closed */}
                            }
                            
                            // preview windows would like to now :)
                            if(varienGlobalEvents) {
                                varienGlobalEvents.fireEvent('quicksave', {id:editor.htmlId, editor:editor});
                            }
                        } catch(e) {
                            alert(e);
                        }
                    }.bind(this)
                });
            }
            return this;
        }
        
        
        
    });
    
    
    
    
    
    
    /*****************************************************************
     * 
     * TEMPLATE EDITOR
     * 
     *****************************************************************/
    
    mzax.ui.TemplateEditor = Class.create({
        
        initialize: function(div, options) 
        {
            var scope = this;
            options = options || {};
            
            scope.mediaUrl = '/media/';
            scope.skinUrl = '/skin/';
            scope.storeUrl = '/skin/';
            
            scope.$ = div;
            scope.jquerySrc = 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.js';
            scope.editorCss = '/skin/adminhtml/default/default/mzax/editor.css';
            scope.enablePreview = true;
            scope.enableAce = true;
            scope.layout = 1;
            
            scope._options = options;
            Object.extend(this, options);
            
            scope.init();
            
            
        },
        
        
        init : function()
        {
            var editor = this, 
                element = editor.$;
            element.innerHTML = '<div class="mzax-content mzax-content-editor"><textarea class="text-input"></textarea></div>'
                              + '<div class="mzax-content mzax-content-preview"></div>'
                              + '<div class="mzax-seperator"></div>'
                              + '<div class="mzax-disable-ui"></div>';
            
            element.addClassName('mzax-template-editor');
            element.addClassName('mzax-editor');
            editor.$editor  = element.down('.mzax-content-editor');
            editor.$preview = element.down('.mzax-content-preview');
            editor.$seperator = element.down('.mzax-seperator');
            editor.$disableUi = element.down('.mzax-disable-ui');
            
            if(editor.enableAce && ace) {
                ace.require("ace/ext/mage_autocomplete");
                
                editor.ace = ace.edit(element.down('.text-input'));
                editor.ace.owner = this;
                editor.ace.setMageSnippets(editor.snippets);
                editor.ace.setTheme("ace/theme/mage");
                editor.ace.getSession().setMode("ace/mode/mage");
                editor.ace.setOptions({
                    enableBasicAutocompletion: true, 
                    enableSnippets: true, 
                    enableMageLiveAutocompletion:true
                });
                editor.ace.getSession().on('change', function(e) {
                    editor.updatePreview(1000);
                });
                
            }
            else {
                element.down('.text-input').observe('keyup', function() {
                    editor.updatePreview(1000);
                });
            }
            if(editor.enablePreview) {
                editor.preview = new mzax.ui.PreviewFrame(editor.$preview, 
                    Object.extend(editor._options, {
                        enableAce: editor.enableAce,
                        enableCKEditor: false
                    })
                );
            }
            
            
            
            this.switchLayout(this.layout);
            
            
            var editor = this,
                $seperator = this.$seperator, 
                document = $(window.document);
            
            if($seperator) {
                
                var offset, size;
                
                var mouseup = function(e) {
                    document.stopObserving('mouseup', mouseup);
                    document.stopObserving('mousemove', mousemove);
                    Element.hide(editor.$disableUi);
                };
                
                var mousemove = function(e) {
                    e.stop();
                    editor.setSeperatorPosition(editor.layout === 1
                        ? (e.pageY - offset.top)/(size.height||1)
                        : (e.pageX - offset.left)/(size.width||1)
                    );
                };
                
                $seperator.observe('mousedown', function(e) {
                    offset = editor.$.cumulativeOffset();
                    size = editor.$.getDimensions();
                    
                    document.observe('mouseup', mouseup);
                    document.observe('mousemove', mousemove);
                    Element.show(editor.$disableUi);
                });
            }
            
            Element.hide(editor.$disableUi);
            
            
            
            
            varienGlobalEvents.attachEventHandler('formValidateAjaxComplete', function(transport) {
                var errors = transport.responseText.evalJSON().html_template_errors;
                console.log(errors);
                
                if(errors && editor.ace) {
                    var Range = ace.require('ace/range').Range,
                        session = editor.ace.session;
                    
                    editor.removeErrorHighlights();
                    var i = errors.length;
                    while(--i > -1) {
                        editor.errorMarkers.push(session.addMarker(
                            new Range(errors[i].line-1, 0, errors[i].line-1, errors[i].column-1), "errorHighlight", "line"));
                        
                    }
                }
                
                
            });
        },
        
        
        removeErrorHighlights : function()
        {
            if(this.ace && this.errorMarkers) {
                var i = this.errorMarkers.length;
                while(--i > -1) {
                    this.ace.getSession().removeMarker(this.errorMarkers[i]);
                }
            }
            this.errorMarkers = [];
            return this;
        },
        
        
        
        setSeperatorPosition: function(pos)
        {
            var editor = this;
            pos = limit(pos, 0, 1);
            
            editor._sepPos = pos;
            
            editor.$editor.writeAttribute('style', '');
            editor.$preview.writeAttribute('style', '');
            editor.$seperator.writeAttribute('style', '');
            
            var props = editor.layout === 1
                ? ['bottom', 'top']
                : ['right', 'left'];
                
            var s1 = {}, s2 ={};
            s1[props[0]] = (100-pos*100)+'%';
            s2[props[1]] = (pos*100)+'%';
            
            editor.$editor.setStyle(s1);
            editor.$preview.setStyle(s2);
            editor.$seperator.setStyle(s2);
            
            editor.refreshUi();
        },
        
        switchLayout : function(layout)
        {
            var element = this.$, ns = 'mzax-layout-';
            this.layout = layout|| (this.layout%2)+1;
            
            element.removeClassName(ns + 'horz');
            element.removeClassName(ns + 'vert');
            switch(this.layout) {
                case 1:
                    element.addClassName(ns + 'vert');
                    break;
                case 2:
                    element.addClassName(ns + 'horz');
                    break;  
            }
            this.setSeperatorPosition(this._sepPos||0.5);
            return this;
        },
        
        
        setValue : function(data)
        {
            var editor = this;
            
            if(editor.ace) {
                editor.ace.setValue(data);
                editor.ace.clearSelection();
            }
            else {
                editor.$editor.down('textarea').value = data;
            }
            editor.updatePreview(false);
            return editor;
        },
        
        
        getValue : function()
        {
            return this.ace 
                ? this.ace.getValue() 
                : this.$editor.down('textarea').value;
        },
        
        
        
        refreshUi : function()
        {
            // resize ace instance
            if( this.ace ) {
                this.ace.resize();
            }
            if( this.preview ) {
                this.preview.refreshUi();
            }
        },
        
        
        updatePreview : function(delay)
        {
            var editor = this;
            if(!editor.preview) {
                return false;
            }
            
            if(editor._updateTimer) {
                clearTimeout(editor._updateTimer);
            }
            
            function update() {
                editor.preview.setHtml(editor.ace 
                    ? editor.ace.getValue() 
                    : editor.$editor.down('textarea').value, true);
                
            }
            if(delay === false) {
                update();
            }
            else {
                editor._updateTimer = setTimeout(update, delay||5);
            }
        },
        
        execCommand : function(cmd)
        {
            if(this.ace) {
                this.ace.execCommand(cmd, {source: 'mage'});
            }
            return this;
        },
        
        
        insert : function(text)
        {
            if(this.ace) {
                this.ace.insert(text);
            }
        },
        
        
        
        
        /**
         * Browse Magento Media
         * 
         * Opens if media browser url is defined the magento media
         * browser.
         * Provide a callback set will recive the value once selected.
         */
        browserMedia : function() 
        {
            var editor = this;
            if(this.mediaBrowserUrl) {
                MediabrowserUtility.browse(this.mediaBrowserUrl, function(src) {
                    if(src) {
                        editor.insert(src);
                    }
                });
            }
        },
        
        
        insertWidget : function(callback)
        {
            var editor = this;
            if(this.widgetToolsUrl) {
                widgetTools.openDialog(this.widgetToolsUrl, function(src) {
                    if(src) {
                        editor.insert(src);
                    }
                });
            }
        }
        
        
        
        
    });
    
    
    
    /*****************************************************************
     * 
     * TEXT EDITOR
     * 
     *****************************************************************/
    
    mzax.ui.TextEditor = Class.create({
        
        
        initialize: function(div, options) 
        {
            var scope = this;
            
            scope.$ = div;
            scope.enableAce = true;
            scope.readOnly = false;
            scope.autosize = false;
            scope.useWrapMode = false;
            Object.extend(this, options || {});
            
            scope.init();
        },
        
        
        init : function()
        {
            var editor = this, aceInstance,
                element = editor.$,
                value = decodeHtml(element.innerHTML);
            
            
            element.innerHTML = '<div class="mzax-content mzax-content-editor"><textarea class="text-input"></textarea></div>';
            
            element.addClassName('mzax-text-editor');
            element.addClassName('mzax-editor');
            
            editor.$editor  = element.down('.mzax-content-editor');
            
            if(editor.enableAce && ace) {
                aceInstance = editor.ace = ace.edit(element.down('.text-input'));
                aceInstance.owner = this;
                aceInstance.setTheme(editor.theme || "ace/theme/mage");
                aceInstance.getSession().setMode(editor.mode || "ace/mode/mage");
                aceInstance.getSession().setUseWrapMode(editor.useWrapMode);
                aceInstance.setOptions({
                    enableBasicAutocompletion: true, 
                    enableSnippets: true, 
                    enableMageLiveAutocompletion:true
                });
                aceInstance.setReadOnly(editor.readOnly);
            }
            editor.refreshUi();
            
            
            Event.observe(window, "resize", function() {
                editor.refreshUi();
            });
            
            editor.setValue(value);
            
        },
        
        
        /**
         * Autosize editor so content fits without vertical scrolling
         * 
         * 
         * @return void
         */
        doAutosize : function() {
            var ace = this.ace;
            if( ace ) {
                var autoHeight =
                    ace.getSession().getScreenLength()
                          * ace.renderer.lineHeight
                          + ace.renderer.scrollBar.getWidth()
                          + 25;
    
                this.$.setStyle({height: autoHeight.toString() + "px"});
            }
            else {
                var txt = this.$editor.down('textarea');
                txt.setStyle({height: "1px"});
                txt.setStyle({height: (25+txt.scrollHeight)+"px"});
            }
        },
        
        
        

        execCommand : function(cmd)
        {
            if(this.ace) {
                this.ace.execCommand(cmd, {source: 'mage'});
            }
            return this;
        },
        
        
        insert : function(text)
        {
            if(this.ace) {
                this.ace.insert(text);
            }
        },
        
        
        setValue : function(data)
        {
            var editor = this;
            if(editor.ace) {
                editor.ace.setValue(data);
                editor.ace.clearSelection();
            }
            else {
                editor.$editor.down('textarea').value = data;
            }
            editor.refreshUi();
            return editor;
        },
        
        
        getValue : function()
        {
            return this.ace 
                ? this.ace.getValue() 
                : this.$editor.down('textarea').value;
        },
        
        
        
        refreshUi : function()
        {
            if(this.autosize) {
                this.$.addClassName('mzax-autosize');
                this.doAutosize();
            }
            else {
                this.$.removeClassName('mzax-autosize');
            }
            if( this.ace ) {
                this.ace.resize();
            }
        }
        
        
        
        
        
    });
    
})(window, window.mzax);





/*****************************************************************
 * 
 * MEDIABROWSER OVERWRITE
 * 
 *****************************************************************/

(function(window, Mediabrowser, MediabrowserUtility) {
    
    if(!Mediabrowser || !MediabrowserUtility) {
        return;
    }
    
    function getSelectedImage(event)
    {
        var div;
        if (event != undefined) {
            div = Event.findElement(event, 'DIV');
        } else {
            $$('div.selected').each(function (e) {
                div = $(e.id);
            });
        }
        if ($(div.id) == undefined) {
            return false;
        }
        return div.id;
    }
    
    
    
    var orgiInsert = Mediabrowser.prototype.insert,
        orgiClose  = MediabrowserUtility.closeDialog;
    
    Mediabrowser.prototype.insert = function(event) 
    {
        // use original if no editor found
        if(!window[this.targetElementId] || !MediabrowserUtility._callback) {
            return orgiInsert.call(this, event);
        }
        
        var params,
            editor = window[this.targetElementId],
            fileId = getSelectedImage(event);
        
        if(!fileId) {
            return false;
        }
        
        params = {filename:fileId, node:this.currentNode.id, store:this.storeId, as_is:1};

        new Ajax.Request(this.onInsertUrl, {
            parameters: params,
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    if (this.getMediaBrowserOpener()) {
                        self.blur();
                    }
                    
                    if( MediabrowserUtility._callback ) {
                        
                        var data = transport.responseText;
                        // we need valid html, so convert double quotes to single quotes in attributes
                        data = data.replace(/"[^"]*\{\{(.*?)\}\}[^"]*"/g, function(match) {
                            return '"' + match.substring(1,match.length-1).replace(/"/g, "'") + '"';
                        });
                        
                        MediabrowserUtility._callback.call(null, data);
                        MediabrowserUtility._callback = null;
                    }
                    
                    Windows.close('browser_window');
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    };
    
    MediabrowserUtility.browse = function(url, callback, width, height, title) {
        this._callback = callback;
        this.openDialog(url, width, height, title);
    };
    
    MediabrowserUtility.closeDialog = function(window) {
        if( this._callback ) {
            this._callback.call(window, null);
            this._callback = null;
        }
        return orgiClose.call(this, window);
    };
    
    

})(window, Mediabrowser, MediabrowserUtility);





/*****************************************************************
 * 
 * WYSIWYG WIDGET OVERWRITE
 * 
 *****************************************************************/

(function(window, WysiwygWidget, widgetTools) {
    if(!WysiwygWidget || !WysiwygWidget.Widget || !widgetTools) {
        return;
    }
    var Widget = WysiwygWidget.Widget,
        openOrgi = widgetTools.openDialog,
        closeOrgi = widgetTools.closeDialog,
        insertOrgi = Widget.prototype.insertWidget;
    
    Widget.prototype.insertWidget = function() 
    {
        // use original if no editor found with target id
        if(!window[this.widgetTargetId] && !widgetTools._callback) {
            return insertOrgi.call(this);
         }
         var editor = window[this.widgetTargetId];
         var widgetOptionsForm = new varienForm(this.formEl);
        
        if(widgetOptionsForm.validator && widgetOptionsForm.validator.validate() || !widgetOptionsForm.validator){
            var formElements = [];
            var i = 0;
            Form.getElements($(this.formEl)).each(function(e) {
                if(!e.hasClassName('skip-submit')) {
                    formElements[i] = e;
                    i++;
                }
            });
            
            var params = Form.serializeElements(formElements);
                params = params + '&as_is=1';
            
            new Ajax.Request($(this.formEl).action,
            {
                parameters: params,
                onComplete: function(transport) {
                    try {
                        widgetTools.onAjaxSuccess(transport);
                        
                        if( widgetTools._callback ) {
                            widgetTools._callback.call(widgetTools, transport.responseText);
                            widgetTools._callback = null;
                        }
                        Windows.close("widget_window");
                    } catch(e) {
                        alert(e.message);
                    }
                }.bind(this)
            });
        }
    };
    
    
    
    widgetTools.openDialog = function(widgetUrl, callback) {
        widgetTools._callback = callback;
        return openOrgi.call(widgetTools, widgetUrl);
    };
    
    
    widgetTools.closeDialog = function(window) {
        if( widgetTools._callback ) {
            widgetTools._callback.call(window, null);
            widgetTools._callback = null;
        }
        return closeOrgi.call(this, window);
    };

    
})(window, WysiwygWidget, widgetTools);





/*****************************************************************
 * 
 * jQuery Plugins
 * 
 *****************************************************************/




