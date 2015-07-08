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


'use strict';

( function(CKEDITOR) {
    // require mzax component
    if(!CKEDITOR.mzax) {
        throw new Error("CKEDITOR.mzax is not defined");
        return;
    }
    
    
    
    /*****************************************************************
     * 
     * PRIVATE VARIABLES
     * 
     *****************************************************************/

    // CONSTANTS
    var SKIN_PATH = '/skin/adminhtml/default/default/mzax/',
        IMAGE_DATE_ATTR = 'data-mage-src',
        
    // Classes
        Placeholder = CKEDITOR.mzax.ui.Placeholder;
    
    

    
    
    
    
    /*****************************************************************
     * 
     * PRIVATE FUNCTIONS
     * 
     *****************************************************************/
    
    /**
     * Uppercase Words
     * 
     * HELLO world => Hello World
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
     * Retrieve Snippets
     * 
     * Retrieve a list of all available snippets
     * 
     * @return Array
     */
    function getSnippets()
    {
        var result = [], snippets, snippet, i;
        if(CKEDITOR.mageSnippets) {
            snippets = CKEDITOR.mageSnippets;
            for(i = 0; i < snippets.length; i++) {
                snippet = snippets[i];
                result.push([snippet.title||snippet.value,snippet.value]);
            }
        }
        return result;
    }
    
    
    /**
     * Retrieve Snippet by value
     * 
     * @param string snippetValue
     * @return Object|null
     */
    function getSnippet(snippetValue)
    {
        var snippets, i;
        if(CKEDITOR.mageSnippets) {
            snippets = CKEDITOR.mageSnippets;
            for(i = 0; i < snippets.length; i++) {
                if(snippets[i].value === snippetValue) {
                    return snippets[i];
                }
            }
        }
        return null;
    }
    
    
    
    
    
    
    
    
    
    /*****************************************************************
     * 
     * INIT
     * 
     *****************************************************************/
    
    // DEFINE ICONS USED
    CKEDITOR.skin.addIcon( 'mage-code', SKIN_PATH+'/images/mage_code.png',   0, '34px 16px');
    CKEDITOR.skin.addIcon( 'image',     SKIN_PATH+'/images/icon_image.png',  0, '16px 16px');
    CKEDITOR.skin.addIcon( 'source',    SKIN_PATH+'/images/icon_source.png', 0, '16px 16px');
    
    
    
    
    
    /*****************************************************************
     * 
     * SHOW SOURCE EDITOR PLUGIN
     * 
     * Simple plugin to show the source code but let our owner
     * decide how to do so.
     * This way the owner can use ACE or other code editor
     * 
     *****************************************************************/
    
    CKEDITOR.plugins.add( 'mzax_editor', {
        init: function( editor ) {
            editor.addCommand( 'show_source', {
                exec: function(editor) {
                    if(CKEDITOR.owner && editor.field) {
                        CKEDITOR.owner.editField(editor.field);
                    }
                }
            });
            editor.ui.addButton( 'mzax_editor', {
                icon: 'source',
                label: 'Source',
                className: 'mzax-cke-show-source',
                command: 'show_source',
                toolbar: 'tools'
            });
        }
    });
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*****************************************************************
     * 
     * MAGENTO IMAGE PLUGIN
     * 
     * This is a simple image plugin that uses a provided
     * api by the owner. So we can use the magento image medium browser
     * 
     *****************************************************************/
    
    CKEDITOR.plugins.add( 'mzax_image', {
        requires: 'dialog',
        init: function( editor ) 
        {
            editor.addCommand( 'mzax_image', new CKEDITOR.command( editor, {
                allowedContent: 'img[alt]{float,width,height}(*)',
                requiredContent: 'img[alt,src]',
                contentTransformations: [
                    [ 'img{width}: sizeToStyle', 'img[width]: sizeToAttribute' ],
                    [ 'img{float}: alignmentToStyle', 'img[align]: alignmentToAttribute' ]
                ],
                exec: function( editor ) {
                    var element = new CKEDITOR.dom.element('img');
                    editor.openDialog('mzaxImageDialog', function(dialog) {
                        dialog.on('show', function() {
                            dialog.setupContent(element);
                        });
                        dialog.on('ok', function() {
                            dialog.commitContent(element);
                            editor.insertElement(element);
                        });
                    });
                }
            }));
            
            editor.ui.addButton( 'mzax_image', {
                icon: 'image',
                label: 'Image',
                className: 'mzax-cke-image',
                command: 'mzax_image',
                toolbar: 'insert'
            });
            
            
            // Open image dialog on image double click
            editor.on( 'doubleclick', function( event ) {
                var element = event.data.element;
                if( element.is('img') && !element.data( 'cke-realelement' ) && !element.isReadOnly() ) {
                    editor.openDialog('mzaxImageDialog', function(dialog) {
                        dialog.on('show', function() {
                            dialog.setupContent(element);
                        });
                        dialog.on('ok', function() {
                            dialog.commitContent(element);
                        });
                    });
                    event.stop();
                }
            });
        },
        
        afterInit: function( editor ) 
        {
            // save real image src so we can change it for preview
            editor.dataProcessor.dataFilter.addRules( {
                elements: { 
                    img: function( element ) {
                        var src = element.attributes['src'];
                        element.attributes[IMAGE_DATE_ATTR] = src;
                        if(CKEDITOR.getImagePreviewUrl) {
                            if(src = CKEDITOR.getImagePreviewUrl.call(editor, src)) {
                                element.attributes['src'] = src;
                            }
                        }
                    }
                }
            });
            
            // use orginal img src and delete the element
            editor.dataProcessor.htmlFilter.addRules( {
                elements: { 
                    img: function( element ) {
                        element.attributes['src'] = element.attributes[IMAGE_DATE_ATTR];
                        if(element.attributes['data-cke-saved-src']) {
                            element.attributes['data-cke-saved-src'] = element.attributes[IMAGE_DATE_ATTR];
                        }
                        delete element.attributes[IMAGE_DATE_ATTR];
                        console.log(element);
                    }
                }
            });
        }
    } );
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*****************************************************************
     * 
     * MAGENTO EXPRESSION PLACEHOLDER
     * 
     * The magento expression placeholder widget.
     * 
     *****************************************************************/
    
    CKEDITOR.plugins.add( 'mage_code', {
        requires: 'widget,dialog',
        
        init: function( editor ) {
            
            editor.addCommand( 'insert_mage_expr', new CKEDITOR.dialogCommand( 'mageCodeDialog' ) );
            
            
            editor.addCommand( 'insert_mage_expr', new CKEDITOR.command( editor, {
                exec: function( editor ) {
                    editor.openDialog('mageCodeDialog', function(dialog) {
                        dialog.on('show', function() {
                            dialog.setupContent(getSnippets());
                        });
                        dialog.on('ok', function() {
                            var snippet = getSnippet(dialog.getContentElement('info', 'snippet').getValue());
                            
                            dialog.commitContent(snippet);
                            console.log(snippet);
                            editor.insertHtml(snippet.snippet);
                        });
                    });
                }
            }));
            
            
            
            editor.ui.addButton( 'Mage', {
                icon: 'mage-code',
                label: 'Insert Magento Code',
                className: 'mzax-cke-mage-expr',
                command: 'insert_mage_expr',
                toolbar: 'insert'
            });
            
            
            // Put ur init code here.
            editor.widgets.add( 'mage_code', {
                // Widget code.
                dialog: 'mageCodeDialog',
                pathName: 'paceholder',
                // We need to have wrapping element, otherwise there are issues in
                // add dialog.
                template: '<span class="mzax-paceholder">[[]]</span>',

                edit: function(e) {
                    
                    var placeholder = this.placeholder,
                        elements = [],
                        definition = {
                            title: 'Magento Code',
                            minWidth: 400,
                            minHeight: 200,
                            contents: [{
                                id: 'info',
                                label: placeholder.expr,
                                title: placeholder.expr,
                                elements: elements
                            }]
                        };
                    
                    if(placeholder.directive === 'else' || placeholder.closing) {
                        e.cancel();
                        return;
                    }
                    
                    if(placeholder.input) {
                        elements.push({
                            id: 'input',
                            type: 'text',
                            label: 'Input',
                            setup: function( widget ) {
                                this.setValue( widget.placeholder.input );
                            },
                            commit: function( widget ) {
                                widget.placeholder.input = this.getValue();
                                widget.setData('expr', widget.placeholder.render());
                            },
                        });
                    }
                    else {
                        for(var i in placeholder.params) {
                            if(placeholder.params.hasOwnProperty(i)) {
                                (function(param) {
                                    elements.push({
                                        id: 'param_'+param,
                                        type: 'text',
                                        label: ucwords(param.replace(/[^a-z]+/gi, ' ').replace(/^\s*(.*?)\s*$/, '$1')),
                                        setup: function( widget ) {
                                            this.setValue(widget.placeholder.params[param]);
                                        },
                                        commit: function( widget ) {
                                            widget.placeholder.params[param] = this.getValue();
                                            widget.setData('expr', widget.placeholder.render());
                                        },
                                    });
                                })(i);
                            }
                        }
                    }
                    
                    e.data.dialog = 'mageExprEdit';
                    if(editor._.storedDialogs) {
                        editor._.storedDialogs[e.data.dialog] = null;
                    }
                    CKEDITOR.dialog.add(e.data.dialog, function() {return definition;});
                },
                
                
                downcast: function() {
                    return new CKEDITOR.htmlParser.text( this.placeholder.render() );
                },
                
                init: function() {
                    this.setData(JSON.parse(this.element.getAttribute('expr')));
                    this.placeholder = new Placeholder(this.data.expr);
                },
                
                data: function() {
                    this.element.setHtml(this.placeholder.toHtml(true));
                }
            } );
            
        },
        afterInit: function( editor ) 
        {
            editor.dataProcessor.dataFilter.addRules( {
                text: function( text, node ) {
                    var dtd = node.parent && CKEDITOR.dtd[ node.parent.name ];

                    // Skip the case when placeholder is in elements like <title> or <textarea>
                    // but upcast placeholder in custom elements (no DTD).
                    if ( dtd && !dtd.span )
                        return;
                    
                    return Placeholder.replace(text, function(placeholder) {
                        
                        // Creating widget code.
                        var widgetWrapper = null,
                            innerElement = new CKEDITOR.htmlParser.element(placeholder.isBlock ? 'div' : 'span');
                        
                        innerElement.add( new CKEDITOR.htmlParser.text( placeholder.toHtml() ) );
                        innerElement.attributes['expr'] = JSON.stringify({expr: placeholder.expr});
                        
                        widgetWrapper = editor.widgets.wrapElement( innerElement, 'mage_code' );
                        return widgetWrapper.getOuterHtml();
                    } );
                }
            } );
        }
    } );
    
    
    
    
    
    
    
    
    /*****************************************************************
     * 
     * IMAGE DIALOG
     * 
     *****************************************************************/
    
    CKEDITOR.dialog.add( 'mzaxImageDialog', function ( editor ) {
        
        var lang = editor.lang.placeholder,
            generalLabel = editor.lang.common.generalTab,
            previewSrc;
        
        function getSize(value) {
            var m = value.match(/[\d.]+%?/);
            return m ? m[0] : '';
        }
        
        return {
            title: 'Image',
            minWidth: 400,
            minHeight: 200,
            contents: [
               {
                   id: 'info',
                   label: generalLabel,
                   title: generalLabel,
                   elements: [
                       {
                           type: 'hbox',
                           width: '100%',
                           widths: [ '280px', '110px' ],
                           align: 'right',
                           children: [{
                               label: 'Image Source',
                               required: true,
                               type: 'text',
                               id: 'src',
                               setup: function( element ) {
                                   previewSrc = null;
                                   this.setValue((
                                       element.getAttribute(IMAGE_DATE_ATTR) ||
                                       element.getAttribute('src')
                                       ).replace(/'/g, '"'));
                                   return;
                               },
                               commit: function( element ) {
                                   var value = this.getValue().replace(/"/g, "'")
                                   element.setAttribute(IMAGE_DATE_ATTR, value);
                                   if(!previewSrc && CKEDITOR.getImagePreviewUrl) {
                                       if(previewSrc = CKEDITOR.getImagePreviewUrl.call(editor, value)) {
                                           element.setAttribute('src', previewSrc);
                                       }
                                   }
                                   else {
                                       element.setAttribute('src', previewSrc||value);
                                   }
                               }
                           },{
                               style: 'display:inline-block;margin-top:15px;width:100%',
                               align: 'right',
                               type: 'button',
                               id: 'browse',
                               hidden: !CKEDITOR.selectImageSource,
                               label: 'Browse',
                               onClick: function(e) {
                                   var dialog = e.data.dialog;
                                   if( dialog ) {
                                       var input = dialog.getContentElement('info', 'src');
                                       CKEDITOR.selectImageSource(function(value, preview) {
                                           input.setValue(value);
                                           previewSrc = preview||null;
                                       });
                                   }
                               }
                           }]
                       },{
                           id: 'alt',
                           type: 'text',
                           style: 'width: 100%;',
                           label: "Alt",
                           required: true,
                           setup: function( element ) {
                               this.setValue(element.getAttribute('alt'));
                           },
                           commit: function( element ) {
                               element.setAttribute('alt', this.getValue());
                           }
                       },{
                           id: 'align',
                           type: 'select',
                           style: 'width: 100%;',
                           label: "Align",
                           items: [['None', ''],
                                   ['Left', 'left'],
                                   ['Right', 'right']],
                           setup: function( element ) {
                               var value = element.getStyle( 'float' );
                               switch ( value ) {
                                   // Ignore those unrelated values.
                                   case 'inherit':
                                   case 'none':
                                       value = '';
                               }
                               !value && ( value = ( element.getAttribute( 'align' ) || '' ).toLowerCase() );
                               this.setValue( value );
                           },
                           commit: function( element ) {
                               var value = this.getValue();
                               if( value ) {
                                   element.setStyle('float', value);
                               }
                               else {
                                   element.removeStyle('float');
                               }
                           }
                       },{
                           id: 'width',
                           type: 'text',
                           style: 'width: 100%;',
                           label: "Width",
                           setup: function( element ) {
                               this.setValue( getSize(element.getStyle('width')) );
                           },
                           commit: function( element ) {
                               var value = this.getValue();
                               if( value ) {
                                   element.setStyle('width', CKEDITOR.tools.cssLength(value));
                               }
                               else {
                                   element.removeStyle('width');
                               }
                           }
                       },{
                           id: 'height',
                           type: 'text',
                           style: 'width: 100%;',
                           label: "Height",
                           setup: function( element ) {
                               this.setValue( getSize(element.getStyle('height')) );
                           },
                           commit: function( element ) {
                               var value = this.getValue();
                               if( value ) {
                                   element.setStyle('height', CKEDITOR.tools.cssLength(value));
                               }
                               else {
                                   element.removeStyle('height');
                               }
                           }
                       }
                   ]
               }
           ]
        };
    });
    
    
    
    
    
    
    
    
    
    /*****************************************************************
     * 
     * MAGENTO EXPRESSION DIALOG
     * 
     *****************************************************************/
    
    CKEDITOR.dialog.add( 'mageCodeDialog', function ( editor ) {
        
        var lang = editor.lang.placeholder,
            generalLabel = editor.lang.common.generalTab,
            validNameRegex = /^[^\[\]<>]+$/;
        
        
        var tpl = new CKEDITOR.template( '<h5 class="title">{title}</h5><p class="description">{description}</p><code>{snippet}</code>' );
        
        return {
            title: 'Magento Code',
            minWidth: 400,
            minHeight: 200,
            contents: [
               {
                   id: 'info',
                   label: generalLabel,
                   title: generalLabel,
                   elements: [
                       {
                           id: 'snippet',
                           type: 'select',
                           style: 'width: 100%;',
                           label: "Choose a code",
                           className: 'mzax-placeholder-snippet',
                           required: true,
                           items: getSnippets(),
                           onChange: function(api) {
                               var description = this.getDialog().getContentElement('info', 'description');
                               var snippet = getSnippet(this.getValue());
                               description.getElement().setHtml(tpl.output(snippet));
                           }
                       },
                       {
                           id: 'description',
                           type: 'html',
                           style: 'width: 100%;',
                           label: "Description",
                           className: 'mzax-placeholder-desc',
                           html: "Please choose Magento code snippet",
                           required: false
                       }
                   ]
               }
           ]
        };
    });
    
    
    
    
    

} )(CKEDITOR);



