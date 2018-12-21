/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    //config.enterMode = CKEDITOR.ENTER_BR; //定义Enter的实际操作，换行还是添加新的p标记
	config.language = 'zh-cn';
	config.height = 500;
    
	config.mathJaxLib = '//cdn.mathjax.org/mathjax/2.6-latest/MathJax.js?config=TeX-AMS_HTML';
	config.extraPlugins = 'notification,lineutils,widget,prism,chart,htmlwriter,' +
                          'codesnippet,codemirror,mathjax,contextmenu,brclear,' +
                          'placeholder,footnotes,tableresize';
    
	config.toolbarGroups = [
                    		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
                    		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
                    		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
                    		{ name: 'forms', groups: [ 'forms' ] },
                    		'/',
                    		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                    		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
                    		{ name: 'links', groups: [ 'links' ] },
                    		{ name: 'about', groups: [ 'about' ] },
                    		{ name: 'tools', groups: [ 'tools' ] },
                    		'/',
                    		{ name: 'styles', groups: [ 'styles' ] },
                    		{ name: 'colors', groups: [ 'colors' ] },
                    		{ name: 'insert', groups: [ 'insert' ] },
                    		{ name: 'others', groups: [ 'others' ] }
                    	];

	config.removeButtons = 'Flash';
};
