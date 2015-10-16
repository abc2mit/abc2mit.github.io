/*
 *		LinkrAPI / LinkrHelper
 * 
 * Author:
 *	Francis Amankrah (francisamankrah [at] gmail [dot] com)
 * 
 * License:
 *	http://www.gnu.org/licenses/gpl.html (GNU/GPL)
 * 
 * Credits:
 *	Ralig, Developers at XHTMLSuite.com, reviewers at extensions.joomla.com, and many others...
*/

/*
 * Linkr General Variable
 */
var LinkrAPI	= new Class({

	initialize : function(l, i, n, k, r)
	{
		// Version
		this.majorVersion	= l[0];
		this.minorVersion	= l[1];
		this.buildVersion	= l[2];
		
		// Internal variables
		this._errMsg	= [];
		this._events	= {onload : [], oninsert : [], onclose : []};
		
		// General information
		this.mode			= r[0];
		this.editor			= n;
		this.siteRoot		= k;
		this.sslSiteRoot	= this.siteRoot.replace('http:', 'https:');
		this.requestURL		= i;
		this.language		= r[1];
		this.userAccessLevel	= r[5];
		this.imgPattern		= /<([\s+]?)img([^>]*)\/?([\s+]?)>/i;
		
		// Icons
		this.iconsFolder	= r[4];
		this.loadingIcon	= r[4] +'loading.gif';
		
		// Loading HTML
		this.loading		= '%3Cimg src="'+ this.loadingIcon +'"alt="..."style="margin:3px;border:none;"/%3E';
		this.wideLoading	= '%3Ccenter style="padding:20px;"%3E'+ this.loading +'%3C/center%3E';
		this.loading		= unescape(this.loading);
		this.wideLoading	= unescape(this.wideLoading);
		
		// Text
		this.missingText	= this.UTF8.Decode(r[2]);
		this.imgAnchor		= this.UTF8.Decode(r[3]);
		
		// Set Editor
		var _pw	= this.mode == 'popup' ? window.opener : window.parent;
		if ($type(_pw.tinymce) == 'object') {
			if ($type(_pw.JContentEditor) == 'object') {
				this.editorPlugin	= 'JCE';
			} else {
				this.editorPlugin	= 'TinyMCE';
			}
			this.editorPlugin	+= _pw.tinymce.majorVersion;
		} else if ($type(_pw.tinyMCE) == 'object') {
			this.editorPlugin	= 'TinyMCE'+ _pw.tinyMCE.majorVersion;
		} else if ($type(_pw.FCKeditorAPI) == 'object') {
			this.editorPlugin	= 'FCK'+ _pw.FCKeditorAPI.Version.toFloat();
		} else if ($type(_pw.editortext) == 'object') {
			this.editorPlugin	= 'TME'+ _pw.editortext.config.version.replace(/[^0-9\.]/ig, '');
		} else if ($type(_pw.xs_text) == 'object') {
			this.editorPlugin	= 'XHTMLSuite'+ _pw.xs_text.config.version.toFloat();
		} else if ($type(_pw.document.getElementById('xstandard')) == 'element') {
			this.editorPlugin	= 'XStandard';
		} else if ($type(_pw.Xinha) == 'function') {
			this.editorPlugin	= 'Xinha';
			this.editorPluginVersion	= _pw.Xinha.version.Release;
		} else if ($type(_pw.CKEDITOR) == 'object') {
			this.editorPlugin	= 'CKEditor'+ _pw.CKEDITOR.version;
		} else {
			this.editorPlugin	= '?';
		}
		
		// Find selected text
		this.HTMLText		= '';
		this.selectedText	= '';
		switch(this.editorPlugin)
		{
			// TinyMCE. Thanks to ralig
			case 'TinyMCE2':
				if (_pw.tinyMCE.selectedInstance.selection)
					this.HTMLText	= _pw.tinyMCE.selectedInstance.selection.getSelectedHTML();
				break;
			
			// JCE, TinyMCE 3
			case 'JCE':
			case 'JCE3':
			case 'TinyMCE3':
				if (_pw.tinyMCE.activeEditor.selection)
					this.HTMLText	= _pw.tinyMCE.activeEditor.selection.getContent();
				break;
			
			// Joomla FCK...
			case 'FCK2.5':
			case 'FCK2.6':
				var _i	= _pw.FCKeditorAPI.GetInstance(this.editor);
				switch(_i.Selection.GetType().toLowerCase())
				{
					case'text':
						if (window.getSelection && _i.EditorWindow)
							this.HTMLText	= _i.EditorWindow.getSelection().toString();
						else if (document.selection && _i.EditorDocument.selection)
							this.HTMLText	= _i.EditorDocument.selection.createRange().text;
						else this.HTMLText	= '';
						break;
					
					case'control':
						var s	= _i.Selection.GetSelectedElement();
						if (s.nodeName.toLowerCase() == 'img')
						{
							this.HTMLText	= '<img src="'+ s.src.replace(this.siteRoot, '') +'"';
							if(s.alt) this.HTMLText += 'alt="'+ s.alt +'"';
							if(s.name) this.HTMLText += 'name="'+ s.name +'"';
							if(s.border) this.HTMLText += 'border="'+ s.border +'"';
							if(s.height) this.HTMLText += 'height="'+ s.height +'"';
							if(s.width) this.HTMLText += 'width="'+ s.width +'"';
							if(s.align) this.HTMLText += 'align="'+ s.align +'"';
							this.HTMLText += '/>';
						} else {
							this.HTMLText	= '';
						}
						break;
				}
				break;
			
			// CKEditor 3.0
			case 'CKEditor3.0':
				var _i	= _pw.CKEDITOR.instances[this.editor];
				if (!_i.getSelection()) break;
				switch (_i.getSelection().getType())
				{
					case _pw.CKEDITOR.SELECTION_TEXT:
						if (window.getSelection)
							this.HTMLText	= _i.getSelection().getNative().toString();
						else if (document.selection)
							this.HTMLText	= _i.getSelection().getNative().createRange().text;
						break;
					
					case _pw.CKEDITOR.SELECTION_ELEMENT:
						var _e	= _i.getSelection().getSelectedElement();
						if (_e && _e.getName() == 'img')
							this.HTMLText	= _e.getOuterHtml();
						break;
				}
				break;
			
			// TMEdit
			case 'TME1.1':
			case 'TME1.2':
				if (_pw.editortext)
					this.HTMLText	= _pw.editortext.getSelectedHTML();
				break;
			
			// XStandard
			case 'XStandard':
				if (_pw.document)
					this.HTMLText	= _pw.document.getElementById('xstandard').SelectedXML;
				break;
			
			// XHTMLSuite
			case 'XHTMLSuite1':
			case 'XHTMLSuite2':
				if (_pw.xs_text)
					this.HTMLText	= _pw.xs_text.getSelectedHTML();
				break;
			
			// Xinha
			case 'Xinha':
				if (this.editorPluginVersion < 2 && _pw.xinha_editors.text)
					this.HTMLText	= _pw.xinha_editors.text.getSelectedHTML();
					break;
		}
		
		// Is selected text an image?
		this.HTMLText	= $type(this.HTMLText) == 'string' ? this.HTMLText : '';
		this.selectedText	= this.imgPattern.test(this.HTMLText) ? this.imgAnchor : this.HTMLText.replace(/(<([^>]+)>)/ig, '');
		
		// Linkr anchors
		this.linkrAnchor	= false;
		if (this.HTMLText.contains('{linkr:'))
		{
			// Bookmarks
			if (this.HTMLText.contains('{linkr:bookmarks;')) {
				this.linkrAnchor	= true;
				this.selectedText	= '[linkr]';
				this.setReturnPage(['LinkrBookmarks', 'home'], [this.HTMLText]);
			}
			
			// Related links
			if (this.HTMLText.contains('{linkr:related;')) {
				this.linkrAnchor	= true;
				this.selectedText	= '[linkr]';
				this.setReturnPage(['LinkrRelated', 'home'], [this.HTMLText]);
			}
		}
		
		// onLoad
		this.addEvent('onLoad', function() {
			var a	= Linkr._events.onload;
			if (a.length > 0)
				a.each(function(f) {
					Linkr.__af(f);
				});
		});
		
		// onInsert
		this.addEvent('onInsert', function(h) {
			var a	= Linkr._events.oninsert;
			if (a.length > 0)
				a.each(function(f) {
					Linkr.__af(f, [h]);
				});
		});
		
		// onClose
		this.addEvent('onClose', function() {
			var a	= Linkr._events.onclose;
			if (a.length > 0)
				a.each(function(f) {
					Linkr.__af(f);
				});
		});
		
		// Common text
		// NOTE: Don't Remove
		this.txt	= {};
		this.addEventListener('onLoad', function()
		{
			this.txt			= {
				linkAttributes	: this.getL18N('LC_ATTRIBUTES'),
				linkText		: this.getL18N('LC_TEXT'),
				linkTarget		: this.getL18N('LC_TARGET'),
				linkTargetSelf	: this.getL18N('LC_TARGET_SELF'),
				linkTargetBlank	: this.getL18N('LC_TARGET_BLANK'),
				linkTitle		: this.getL18N('LC_TITLE'),
				linkClass		: this.getL18N('LC_CLASS'),
				linkRelation	: this.getL18N('LC_RELATION'),
				linkConfigure	: this.getL18N('CONFIGURE_LINK'),
				linkGet			: this.getL18N('GET_LINK'),
				pick			: this.getL18N('PICK'),
				noResultsFound	: this.getL18N('NORESULTS')
			};
		}.bind(this));
		
		// MooTools tips
		this.addEventListener('onLoad', function() {
			this._tips	= new Tips({}, {maxTitleChars: 50});
		}.bind(this));
	},
	
	/*
	 * Internal method to attempt a function / object & method array
	 */
	__af : function(fn, a)
	{
		var ft	= $type(fn);
		a	= Linkr.__ca(a);
		if (ft == 'function') {
			fn.attempt(a);
		} else if (ft == 'array' && $type(fn[0]) == 'object') {
			var o	= fn[0];
			if ($type(fn[1]) == 'string' && fn[1].length > 0) {
				var m	= fn[1];
				o[m].attempt(a, o);
			}
		}
	},
	
	/*
	 * Internal function to check arguments for LinkrHelper.__af()
	 */
	__ca : function(a, m)
	{
		// Empty arguments
		if (typeof(a) == 'undefined' || a == null)
			return $type(m) == 'array' ? m : null;
		
		// Single argument
		if ($type(a) != 'array')
			return $type(m) == 'array' ? m.merge([a]) : [a];
		
		// Several arguments
		return $type(m) == 'array' ? m.merge(a) : a;
	},

	/*
	 * Test Linkr Application
	 */
	test : function(msg) {
		alert($pick(msg, 'Editor plugin is "' + Linkr.editorPlugin + '"'));
	},
	
	/*
	 * Error handling
	 */
	setError : function(msg, r) {
		this._errMsg[this._errMsg.length]	= msg;
		return r ? r : false;
	},
	getError : function(c) {
		var e	= this._errMsg.length ? this._errMsg[this._errMsg.length-1] : '';
		if (c !== false) this._errMsg = [];
		return e;
	},
	getAllErrors : function(c) {
		var e	= this._errMsg;
		if (c !== false) this._errMsg = [];
		return e;
	},
	insertMsgDiv : function(msg, el, id)
	{
		if ($type(msg) == 'string') msg = [msg];
		if ($type(msg) != 'array' || msg.length < 1 || $type(el) != 'element')
			return false;
		
		// Message DIV
		id	= $pick(id, $time() + $random(0, 999));
		var div	= new Element('div', {
			id : id,
			'class' : 'linkr-notice',
			styles : {
				position : 'relative',
				margin : '15px 0'
			}
		}).setHTML(msg.join('<br />')).injectInside(el);
		
		// Close link
		var a	= new Element('a', {
			href : 'javascript:void(0);',
			styles : {
				position : 'absolute',
				top : 3,
				right : 4
			},
			events : {
				click : div.remove.bind(div)
			}
		}).injectInside(div);
		
		// Close image
		new Element('img', {
			alt : '[x]',
			src : this.siteRoot +'administrator/images/publish_x.png'
		}).injectInside(a);
		
		return id;
	},
	
	/*
	 * Events
	 */
	addEventListener : function(t, f)
	{
		// Checks
		if ($type(f) != 'function' && $type(f) != 'array')
			return this.setError('Linkr.addEventListener - Invalid function', false);
		if ($type(t) != 'string' || t.length < 1)
			return this.setError('Linkr.addEventListener - Invalid event', false);
		
		var et	= t.toLowerCase();
		if (!$defined(this._events[et]))
			return this.setError('Linkr.addEventListener - Event "'+ t +'"not supported', false);
		
		// Add function
		this._events[et].include(f);
		return true;
	},
	
	/*
	 * UTF-8 encoding
	 */
	UTF8 :
	{
		isEncoded : function(s)
		{
			var n	= true;
			try {
				this.decodeString(s);
			} catch(e) {
				n	= false;
			}
			return n;
		},
		
		Encode : function(s) {
			return this.isEncoded(s) ? s : this.encodeString(s);
		},
		Decode : function(s) {
			return this.isEncoded(s) ? this.decodeString(s) : s;
		},
		
		encodeString : function(s) {
			return (encodeURIComponent) ? unescape( encodeURIComponent( s ) ) : escape(s);
		},
		decodeString : function(s) {
			return (decodeURIComponent) ? decodeURIComponent( escape( s ) ) : unescape(s);
		}
	},
	
	/*
	 * Base64 encoding
	 *	http://www.webtoolkit.info/javascript-base64.html
	 */
	Base64 :
	{
		_k : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',
		
		Encode : function(s, a)
		{
			// Performance check
			if ($type(s) != 'string' || s.length < 1) return '';
			s	= Linkr.UTF8.Encode(s);
			
			var o	= '';
			var c1, c2, c3, e1, e2, e3, e4;
			var i	= 0;
			
			while (i < s.length)
			{
				c1	= s.charCodeAt(i++);
				c2	= s.charCodeAt(i++);
				c3	= s.charCodeAt(i++);
				
				e1	= c1 >> 2;
				e2	= ((c1 & 3) << 4) | (c2 >> 4);
				e3	= ((c2 & 15) << 2) | (c3 >> 6);
				e4	= c3 & 63;
				
				if (isNaN(c2)) e3 = e4 = 64;
				else if (isNaN(c3)) e4 = 64;
				
				o	+= Linkr.Base64._k.charAt(e1);
				o	+= Linkr.Base64._k.charAt(e2);
				o	+= Linkr.Base64._k.charAt(e3);
				o	+= Linkr.Base64._k.charAt(e4);
			}
			
			return a ? o.replace(/[+]/g, ',') : o;
		},
		
		Decode : function(s)
		{
			// Performance check
			if ($type(s) != 'string') return '';
			s	= s.replace(/[^A-Z0-9\+\/\=]/gi, '');
			if (s.length < 1) return '';
			
			var o	= '';
			var c1, c2, c3, e1, e2, e3, e4;
			var i = 0;
			
			while (i < s.length)
			{
				e1	= Linkr.Base64._k.indexOf(s.charAt(i++));
				e2	= Linkr.Base64._k.indexOf(s.charAt(i++));
				e3	= Linkr.Base64._k.indexOf(s.charAt(i++));
				e4	= Linkr.Base64._k.indexOf(s.charAt(i++));
				
				c1	= (e1 << 2) | (e2 >> 4);
				c2	= ((e2 & 15) << 4) | (e3 >> 2);
				c3	= ((e3 & 3) << 6) | e4;
				
				o	+= String.fromCharCode(c1);
				if (e3 != 64) o += String.fromCharCode(c2);
				if (e4 != 64) o += String.fromCharCode(c3);
			}
			
			return Linkr.UTF8.Decode(o);
		}
	},
	
	/*
	 * URL-Encoding:
	 *	http://phpjs.org/functions/urlencode
	 *	http://xkr.us/articles/javascript/encode-compare
	 */
	urlencode : function(s)
	{
		if ($type(s) != 'string') return '';
		
		s	= (encodeURIComponent) ? encodeURIComponent(s) : escape(s);
		s	= s.replace(/'/g, '%27');
		s	= s.replace(/\(/g, '%28');
		s	= s.replace(/\)/g, '%29');
		s	= s.replace(/\*/g, '%2A');
		s	= s.replace(/~/g, '%7E');
		s	= s.replace(/\!/g, '%21');
		s	= s.replace(/%20/g, '+');
		
		return s.replace(/(\%([a-z0-9]{2}))/g, function(a, b, c) {
			return '%'+ c.toUpperCase();
		});
	},
	urldecode : function(s)
	{
		if ($type(s) != 'string') return '';
		
		s	= s.replace(/%27/g, '\'');
		s	= s.replace(/%28/g, '(');
		s	= s.replace(/%29/g, ')');
		s	= s.replace(/%2A/g, '*');
		s	= s.replace(/%7E/g, '~');
		s	= s.replace(/%21/g, '!');
		s	= s.replace(/\+/g, '%20');
		
		return (decodeURIComponent) ? decodeURIComponent(s) : unescape(s);
	},
	
	/*
	 * Text localization
	 */
	L18N : {},
	setL18N : function(k, v)
	{
		// From array
		if ($type(k) == 'array')
			return k.each(function(p) {
				this.L18N[p[0]]	= this.UTF8.Encode(p[1]);
			}.bind(this));
		
		// Key-value pair
		this.L18N[k]	= this.UTF8.Encode(v);
	},
	getL18N : function(k, a)
	{
		// Get localized text
		var s	= $type(this.L18N[k]) == 'string' ? this.L18N[k] : k;
		
		// Sprintf
		if ($type(a) == 'array') {
			a	= [this.UTF8.Decode(s)].merge(a);
			return sprintfWrapper.init.attempt(a);
		}
		
		// Normal text
		return this.UTF8.Decode(s);
	},
	
	/*
	 * Cookie handling
	 */
	getCookie : function(n, o, rc)
	{
		// Performance check
		if (n.length < 1) return {};
		
		// Get cookie
		o	= $pick(o, {autoSave : false});
		var c	= new Hash.Cookie(n, o);
		
		// Return cookie or object
		return rc ? c : c.obj;
	},
	setCookie : function(n, v, o)
	{
		// Performance check
		if (n.length < 1) return false;
		
		// Set new values
		var old	= Linkr.getCookie(n, o, true).extend(v);
		
		// Save settings
		var e	= 'Could not save cookie "'+ n +'"';
		return old.save() ? true : Linkr.setError(e, false);
	},
	
	/*
	 * Remember last visited page
	 */
	setReturnPage : function(a, b)
	{
		// Get cookie
		var c	= new Hash.Cookie('linkrpage', {
			autoSave : false,
			duration : 30
		});
		
		// Regular function
		if ($type(a) == 'string' && a.length > 0) {
			c.empty();
			c.set('f', a);
		}
		
		// Array
		else if ($type(a) == 'array' && a[0].length > 0 && a[1].length > 0) {
			c.empty();
			c.set('o', a[0]);
			c.set('m', a[1]);
		}
		
		// Invalid callback function
		else {
			this.setError('Linkr.setReturnPage: Invalid callback function');
			return false;
		}
		
		// Return true if cookie was saved
		c.set('a', this.__ca(b));
		return c.save();
	},

	/*
	 * Builds URL for JSON requests
	 */
	createRequest : function(r) {
		return this.requestURL +'&request='+ r +'&__linkr='+ $time();
	},

	/*
	 * Builds URL for JSON requests
	 */
	createListRequest : function(l, id, k) {
		return this.requestURL +'&request='+ l +'&'+ $pick(k, 'id') +'='+ id +'&__linkr='+ $time();
	},
	
	/*
	 * "Json.remote" shortcut
	 */
	json : function(u, fn, e)
	{
		new Json.Remote(u, {
			onComplete : fn,
			onFailure : ($type(e) == 'function' ? e : this.onJsonFailure.bind(this))
		}).send();
	},

	/*
	 * Inserts a link into the article
	 */
	link : function(u, te)
	{
		// Check elements
		var lt	= $('linkTitle');
		var lc	= $('linkClass');
		var lr	= $('linkRelation');
		if (!lt || !lc || !lr)
			return this.setError('LinkrHelper.link - Missing link title, class, or relation', false);
		
		// Link text
		if	((te == this.imgAnchor) || (te == this.getSelectedText(false)))
			var t	= this.getSelectedText(true);
		else
			var t	= te;
		
		// Linkr target
		if ($('target')) var lx = $('target').value;
		else var lx = '';
		
		// Link HTML
		var l	= '<a href="'+ u +'"';
		if (lx != '_self') l += ' target="'+ lx +'"';
		if (lt.value.length > 0) l += ' title="'+ lt.value +'"';
		if (lc.value.length > 0) l += ' class="'+ lc.value +'"';
		if (lr.value.length > 0) l += ' rel="'+ lr.value +'"';
		l	+= '>'+ t +'</a>';
		
		// Popup mode
		if (this.mode == 'popup')
		{
			window.opener.LinkrInsert('object', {
				url : u,
				text : t,
				target : lx,
				title : lt.value,
				'class' : lc.value,
				relation : lr.value,
				html : l
			}, this.editor);
			return self.close();
		}
		
		// Insert link into editor
		this.insert(l);
	},

	/*
	 * Creates a link for toggling DIVs
	 */
	createToggleLink : function(a, b, c, d, f)
	{
		// Get settings, fix Linkr 2.2.2
		if ($type(b) == 'number') {
			var dur	= b || 400;
			var el	= c;
		} else {	
			var el	= b;
			var dur	= c || 400;
		}
		
		var slide	= new Fx.Slide($(a), {duration : dur});
		slide.hide();
		$(el).addEvent('click', function(e)
		{
			new Event(e).stop();
			slide.toggle();
			
			// Function
			if ($type(d) == 'function' || $type(d) == 'array')
			{
				// Arguments
				var w	= slide.wrapper;
				var args	= [(w.offsetHeight == 0 || w.offsetWidth == 0)];
				if ($type(f) == 'array') f = args.merge(f);
				else if ($type(f) != false) args.include(f);
				
				// Attempt function
				Linkr.__af(d, args);
			}
		});
	},

	/*
	 * Returns selected text
	 */
	getSelectedText : function(h) {
		return (h) ? this.HTMLText : this.selectedText;
	},
	getDefaultText : function(d) {
		return this.selectedText.length > 0 ? this.selectedText : d;
	},

	/*
	 * Inserts text at the end of an article
	 */
	insertAtEnd : function(a)
	{
		this.fireEvent('onInsert', a);
		var pw	= this.mode == 'popup' ? window.opener : window.parent;
		switch(this.editorPlugin)
		{
			// Joomla FCK
			case 'FCK2.5':
			case 'FCK2.6':
				var i	= pw.FCKeditorAPI.GetInstance(this.editor),
				n		= i.GetData() + a;
				i.SetData(n);
				break;
			
			// CKEditor 3.0
			case 'CKEditor3.0':
				var i	= pw.CKEDITOR.instances[this.editor],
				n		= i.getData() + a;
				i.setData(n);
				break;
			
			// TinyMCE
			case 'TinyMCE2':
				var i	= pw.tinyMCE.selectedInstance,
				n		= i.getHTML() + a;
				i.setHTML(n);
				break;
			
			// JCE, TinyMCE 3
			case 'JCE':
			case 'JCE3':
			case 'TinyMCE3':
				var i	= pw.tinyMCE.activeEditor,
				n		= i.getContent() + a;
				i.setContent(n);
				break;
			
			// TMEdit
			case 'TME1.1':
			case 'TME1.2':
				var i	= pw.editortext,
				n		= i.getInnerHTML() + a;
				i.setHTML(n);
				break;
			
			// XStandard
			case 'XStandard':
				var i	= pw.document.getElementById('xstandard'),
				n		= i.value + a;
				i.value	= n;
				break;
			
			// XHTMLSuite
			case 'XHTMLSuite1':
			case 'XHTMLSuite2':
				var i	= pw.xs_text,
				n		= i.getHTML() + a;
				i.setHTML(n);
				break;
			
			// Xinha
			case 'Xinha':
				if (this.editorPluginVersion < 2) {
					var i	= pw.xinha_editors.text,
					n		= i.getHTML() + a;
					i.setHTML(n);
				}
				break;
			
			default:
				pw.jInsertEditorText(a, this.editor);
		}
		
		// Close Linkr
		(this.mode == 'popup') ? self.close() : window.parent.document.getElementById('sbox-window').close();
	},

	/*
	 * Inserts text at cursor position
	 */
	insert : function(a)
	{
		this.fireEvent('onInsert', a);
		
		// Popup
		if (this.mode == 'popup') {
			window.opener.LinkrInsert('string', a, this.editor);
			return self.close();
		}
		
		// Regular window
		window.parent.jInsertEditorText(a, this.editor);
		window.parent.document.getElementById('sbox-window').close();
	},

	/*
	 * Returns Linkr version
	 */
	getVersion : function() {
		return this.majorVersion +'.'+ this.minorVersion +'.'+ this.buildVersion;
	},

	/*
	 * DEPRECATED: Makes AJAX request for layout and loads result
	 */
	extLayout : function(rURL, func)
	{
		if ($type(rURL) != 'string') return;
		$('layout').setHTML(this.wideLoading);
		var txt	= this.getSelectedText();
		if (txt.length) rURL += '&ltxt='+ txt;
		if ($type(func) != 'function') func = Class.empty;
		new Ajax(rURL, {
			method : 'get',
			update : $('layout'),
			onComplete : func
		}).request();
	},

	/*
	 * DEPRECATED: Makes AJAX request for content and loads result in
	 * specified DIV
	 */
	extContent : function(rURL, update, func)
	{
		if ($type(rURL) != 'string') return;
		var txt	= this.getSelectedText();
		if (txt.length) rURL += '&ltxt='+ txt;
		if ($type(func) != 'function') func = Class.empty;
		new Ajax(rURL, {
			method : 'get',
			update : $(update),
			onComplete : func
		}).request();
	},

	/*
	 * Displays a loading icon
	 */
	idleDiv : function(div, wide) {
		var el	= $(div);
		if($type(el) == 'element')
			el.setHTML(wide ? this.wideLoading : this.loading);
		return el;
	},

	/*
	 * Fades in the loading icon and executes a function
	 */
	delayIdleDiv : function(el, wide, dur)
	{
		el	= $(el);
		if (!el) return this.setError('Linkr.delayIdleDiv: Invalid element');
		
		// Fade content away
		dur	= $pick(dur, 70);
		var fx	= new Fx.Style(el, 'opacity', {duration : dur}).start(1.0, 0);
		
		// Load content
		if ($type(wide) == 'element') {
			wide.injectInside.delay((dur + 20), wide, el);
		} else if ($type(wide) == 'string') {
			div	= new Element('div').setHTML(wide);
			div.injectInside.delay((dur + 20), div, el);
		} else if ($type(wide) == 'function') {
			wide.delay((dur + 20));
		} else {
			this.idleDiv.delay((dur + 20), this, [el, wide]);
		}
		
		// Fade in
		fx.start.delay((dur + 40), fx, [0, 1.0]);
	},
	
	delayDisplay : function(el, dur, func, args)
	{
		el	= $(el);
		if (!el) return this.setError('Linkr.delayDisplay: Invalid element');
		
		// Fade loading icon away
		dur	= $pick(dur, 70);
		var fx	= new Fx.Style(el, 'opacity', {duration : dur}).start(1.0, 0);
		
		// Execute function
		if ($type(func) == 'function' || $type(func) == 'array')
			this.__af.delay((dur + 10), this, [func, args]);
		
		// Fade in
		fx.start.delay((dur + 30), fx, [0, 1.0]);
	},

	/*
	 * Creates a layout div
	 */
	htmlLayout : function(t, h)
	{
		// Title
		var c	= [new Element('div', {
			styles : {
				margin : '10px 0 10px 80px',
				'font-weight' : 'bold',
				'font-size' : 14
			}
		}).setHTML(t)];
		
		// Array of contents
		if ($type(h) == 'array') c.merge(h);
		
		// HTML code
		else if ($type(h) == 'string')
			c.include(new Element('div').setHTML(h));
		
		else c.include(h);
		
		// Insert content
		this.htmlContent('layout', c);
	},

	/*
	 * Sets content of an HTML div
	 */
	htmlContent	: function(div, c, clear)
	{
		div	= $(div);
		if ($type(div) != 'element') return;
		
		// Empty DIV contents
		if (clear !== false) div.empty();
		
		// HTML string
		if ($type(c) == 'string')
			new Element('div').setHTML(c).injectInside(div);
		
		// Element
		if ($type(c) == 'element') c.injectInside(div);
		
		// Array
		if ($type(c) == 'array')
		{
			var i	= new Element('div').injectInside(div);
			c.each(function(el) {
				Linkr.htmlContent(i, el, false);
			});
		}
	},

	/*
	 * Creates a form input
	 */
	htmlInput : function(n, v, o)
	{
		// Defaults
		if ($type(o) != 'object') o = {};
		if ($type(v) != 'string' && $type(v) != 'number') v = '';
		
		// Values
		o.id	= n;
		o.name	= n;
		o.value	= v;
		
		// Return input element
		return new Element('input', o);
	},

	/*
	 * Creates an html select list
	 */
	htmlSelect : function(li, n, chg, s)
	{
		// Defaults
		if ($type(s) != 'object') s = {};
		if ($type(chg) != 'function') chg = Class.empty;
		
		// Get options
		var o	= {
			id	: n,
			name	: n,
			styles	: s,
			events	: {
				change	: chg
			}
		};
		
		// Return select list
		return this.htmlSelectCustom(li, o);
	},

	/*
	 * Creates an html select list with custom options
	 */
	htmlSelectCustom : function(li, opt)
	{
		// Select list
		if ($type(opt) != 'object') opt = {};
		var	sel	= new Element('select', opt);
		
		// Options
		if ($type(li) == 'array')
		{
			li.each(function(o)
			{
				// Option value
				if ($type(o) == 'string') {
					var t	= o;
					var v	= o;
					var i	= '';
				} else {
					var v	= o[0];
					var t	= o[1] || v;
					var i	= o[2] || '';
				}
				
				// Insert option element
				new Element('option', {
					id : v,
					value : v,
					selected : i.contains('selected'),
					disabled : i.contains('disabled')
				}).setHTML(t).injectInside(sel);
			});
		}
		
		return sel;
	},

	/*
	 * Creates a config box
	 */
	htmlConfigLink : function(u, t, x)
	{
		// Config title
		if ($type(t) != 'string' || t.trim() == '')
			t = this.getL18N('LC_ATTRIBUTES');
		
		// Settings
		var s	= [
			['linkText', this.getL18N('LC_TEXT'), this.getSelectedText()],
			['target', this.getL18N('LC_TARGET'), [
				['_self', this.getL18N('LC_TARGET_SELF'), 'selected'],
				['_blank', this.getL18N('LC_TARGET_BLANK')]
			]],
			['linkTitle', this.getL18N('LC_TITLE')],
			['linkClass', this.getL18N('LC_CLASS')],
			['linkRelation', this.getL18N('LC_RELATION')]
		];
		
		// Extra settings
		if ($type(x) == 'array') s.merge(x);
		
		// Get config box
		return this.htmlConfig(u, s, t);
	},

	/*
	 * Creates a config box
	 */
	htmlConfig : function(u, s, t)
	{
		// Defaults
		if ($type(u) != 'string' || u.trim() == '')
			u = 'index.php';
		if ($type(t) != 'string' || t.trim() == '')
			t = this.getL18N('LC_ATTRIBUTES');
		if ($type(s) != 'array')
			s = [];
		
		// Config div
		var cfg	= new Element('div', {id : 'settings'});
		
		// Title
		new Element('div', {
			styles : {
				margin : '5px 0',
				'text-align' : 'center',
				'font-weight' : 'bold'
			}
		}).setHTML(t).injectInside(cfg);
		
		// Settings list
		var ui	= false;
		s.each(function(el)
		{
			// Label
			var id	= el[0] || $time() + $random(1, 999);
			var lbl	= new Element('label', {
				'for' : id
			}).setHTML(el[1] || '');
			
			// Select list
			if ($type(el[2]) == 'array')
				var i = this.htmlSelectCustom(el[2], {id : id});
			
			// Custom element
			else if ($type(el[2]) == 'element')
				var i	= el[2];
			
			// Input
			else
				var i = this.htmlInput(id, el[2], {'class' : 'inputbox value'});
			
			// Settings row
			lbl.injectInside(cfg);
			i.injectAfter(lbl);
			new Element('div', {styles : {clear : 'both'}}).injectAfter(i);
			
			// Don't include link twice
			if (id == 'linkURL') ui = true;
			
		}.bind(this));
		
		// URL
		if (!ui)
			this.htmlInput('linkURL', u, {
				type : 'hidden'
			}).injectInside(cfg);
		
		return cfg;
	},

	/*
	 * Creates navigation links
	 */
	htmlTBLinks : function(lks, opt)
	{
		// Toolbar container
		var tb	= new Element('div', $pick(opt, {
			styles : {
				margin : '5px 0',
				'text-align' : 'center',
				'font-weight' : 'bold'
			}
		}));
		
		// Checks
		if ($type(lks) != 'array' || lks.length < 1) return tb;
		
		// Add links
		lks.each(function(el)
		{
			var o	= {};
			o.href	= 'javascript:void(0);';
			o.styles	= {'font-weight' : 'bold'};
			o.id	= el[0] || $time() + $random(1, 999);
			
			// OnClick
			if ($type(el[2]) == 'function') {
				var r	= el[2];
				var c	= el[2];
			} else if ($type(el[2]) == 'string') {
				// Deprecated
				var r	= Class.empty;
				var c	= Class.empty;
				Linkr.setError('Linkr.htmlTBLinks: Function supplied as string');
			} else if ($type(el[2]) == 'array') {
				var fo	= el[2][0];
				var fm	= el[2][1];
				if ($type(fo) == 'object' && $type(fm) == 'string' && fm.length > 1) {
					var r	= fo;
					var c	= fo[fm];
				} else {
					var r	= Class.empty;
					var c	= Class.empty;
				}
			} else {
				var r	= Class.empty;
				var c	= Class.empty;
			}
			
			// OnClick arguments
			var fa	= Linkr.__ca(el[3]);
			
			// Add event
			o.events	= {
				click : function(e) {
					new Event(e).stop();
					return c.attempt(fa, r);
				}
			};
			
			// Link text
			if ($type(el[1]) == 'string' && el[1].length > 0)
				var txt	= el[1];
			else
				var txt	= '';
			
			// Insert toolbar link
			new Element('a', o).setHTML(txt).injectInside(tb);
			new Element('span').setHTML('&nbsp;&nbsp;&bull;&nbsp;&nbsp;').injectInside(tb);
		});
		
		// Remove last bullet
		tb.getLast().remove();
		
		return tb;
	},
	
	/*
	 * Adds MooTools tooltips
	 */
	htmlToolTips : function(a) {
		a.each(this._tips.build, this._tips);
	},
	htmlToolTip : function(el) {
		this._tips.build.attempt(el, this._tips);
	},
	
	/*
	 * Retrieves ItemIDs from database
	 */
	getItemids : function(q, o, func, args)
	{
		// Checks
		if ($type(func) != 'function' && $type(func) != 'array')
			return this.setError('Linkr.getItemids: Invalid callback function');
		if ($type(func) == 'array' && ($type(func[0]) != 'object' || $type(func[1]) != 'string'))
			return this.setError('Linkr.getItemids: Invalid callback method');
		
		// Columns
		var cols	= $pick(o.columns, ['id', 'name', 'link']);
		
		// WHERE clause
		var w	= [];
		
		// Link query variables
		if ($type(q) == 'array' && q.length > 0)
			w.include(this._iqv(q));
		
		// Multiple conditions for link query variables
		if ($type(q) == 'object')
		{
			var qm	= [];
			for (qn in q)
			{
				if ($type(q[qn]) == 'array' && q[qn].length > 0)
					qm.include(this._iqv(q[qn]));
			}
			w.include('('+ qm.join(' OR ') +')');
		}
		
		// Component ID
		if ($defined(o.componentid))
			w.include('componentid='+ o.componentid.toInt());
		
		// Menu type
		if ($defined(o.menutype))
			w.include('menutype='+ Linkr.dbQuote(o.menutype));
		
		// Link type
		if ($defined(o.type))
			w.include('type='+ Linkr.dbQuote(o.type));
		
		// Parent
		if ($defined(o.parent))
			w.include('parent='+ o.parent.toInt());
		
		// Only published menus
		w.include('published=1');
		
		// SQL query
		var sql	=
		' SELECT '+ cols.join(',') +
		' FROM '+ Linkr.dbNameQuote('#__menu') +
		' WHERE '+ w.join(' AND ') +' ORDER BY ordering,id';
		
		// Return Itemids
		return Linkr.dbList(sql, 0, 0, func, args);
	},
	// Shortcut
	_iqv : function(q)
	{
		var s	= [];
		q.each(function(e)
		{
			var a	= Linkr.dbQuote('%'+ e);
			var b	= Linkr.dbQuote('%'+ e +'&%');
			s.include('(link LIKE '+ a +' OR link LIKE '+ b +')');
		});
		
		return '('+ s.join(' AND ') +')';
	},

	/*
	 * Queries the database for a list
	 */
	dbList : function(q, s, l, fn, args)
	{
		// Checks
		if ($type(q) != 'string' || q.length < 1)
			return this.setError('Linkr.dbList: Empty query string');
		if ($type(fn) != 'function' && $type(fn) != 'array')
			return this.setError('Linkr.dbList: Invalid callback function');
		if ($type(fn) == 'array' && ($type(fn[0]) != 'object' || $type(fn[1]) != 'string'))
			return this.setError('Linkr.dbList: Invalid callback method');
		
		// Query
		var u	= this.createRequest('dblist'+ this.dbQuery(q));
		u	+= '&s='+ $pick(s, 0) +'&l='+ $pick(l, 0);
		
		// Callback function
		var callback	= function(dbResult) {
			return Linkr.__af(fn, Linkr.__ca(args, [dbResult]));
		};
		
		// Make request
		this.json(u, callback);
	},

	/*
	 * Queries the database for an object
	 */
	dbObject : function(q, fn, args)
	{
		// Checks
		if ($type(q) != 'string' || q.length < 1)
			return this.setError('Linkr.dbObject: Empty query string');
		if ($type(fn) != 'function' && $type(fn) != 'array')
			return this.setError('Linkr.dbObject: Invalid callback function');
		if ($type(fn) == 'array' && ($type(fn[0]) != 'object' || $type(fn[1]) != 'string'))
			return this.setError('Linkr.dbObject: Invalid callback method');
		
		// Query
		var u	= this.createRequest('dbobject'+ this.dbQuery(q));
		
		// Callback function
		var callback	= function(dbResult) {
			return Linkr.__af(fn, Linkr.__ca(args, [dbResult]));
		};
		
		// Make request
		this.json(u, callback);
	},
	
	/*
	 * Escapes a string for database entry
	 * http://ca.php.net/mysql_real_escape_string
	 */
	dbEscape : function(str, extra)
	{
		// Performance check
		if ($type(str) != 'string' || str.length < 1) return '';
		
		// Escape
		str	= str.replace(/\x00/g, '\\0');
		str	= str.replace(/\n/g, '\\n');
		str	= str.replace(/\r/g, '\\r');
		str	= str.replace(/['"]/g, "\\$&");
		str	= str.replace(/\x1a/g, '\\Z');
		
		// Extra escaping
		if (extra === true) str = str.replace(/[%_]/g, "\\$&");
		
		// Return database escaped string
		return str;
	},

	/*
	 * Quotes a string for database entry
	 */
	dbQuote : function(str, esc)
	{
		// Performance check
		if ($type(str) != 'string' || str.length < 1) return '';
		
		// Database escape
		str	= esc ? this.dbEscape(str) : str;
		
		// Wildcard
		if (str.contains('%')) str = str.replace(/%/g, '_WC_');
		
		// Return database quoted string
		return '_Q_'+ str +'_Q_';
	},
	dbNameQuote : function(n) {
		return '_NQ_'+ n +'_NQ_';
	},
	
	/*
	 * Formats SQL query for use within Linkr
	 */
	dbQuery : function(q)
	{
		// Performance check
		if ($type(q) != 'string' || q.length < 1) return '';
		
		// Base64-encode query
		q	= this.Base64.Encode(q, true);
		
		// Split query into bits
		var u	= '';
		if (q.length > 60)
		{
			while (q.length > 60)
			{
				u	+= '&q[]='+ q.substr(0, 60);
				q	= q.substr(60);
			}
			
			u	+= '&q[]='+ q;
		}
		
		// No bits to split
		else {
			u	+= '&q[]='+ q;
		}
		
		return u;
	},

	/*
	 * Determines if a database result is valid
	 */
	isError	: function(o) {
		return (this.isDBRO(o) && o.status == 'error');
	},

	/*
	 * Determines if the object is a valid Linkr object
	 */
	isDBRO : function(o) {
		return ($type(o) == 'object' && $type(o.status) == 'string');
	},

	/*
	 * Returns a list of files in a folder
	 */
	listFiles : function(f, x, fn, args)
	{
		// Checks
		if ($type(f) != 'string' || f.length < 1)
			return this.setError('Linkr.listFiles: No folder specified');
		
		// Check extensions
		if ($type(x) == 'string') x = [x];
		if ($type(x) != 'array' || x.length < 1) x = ['all'];
		
		// Request
		f	= this.Base64.Encode(f);
		var u	= this.createListRequest('paths', f, 'f') +'&e='+ x.join('-');
		
		// Callback function
		var callback	= function(fileList) {
			return Linkr.__af(fn, Linkr.__ca(args, [fileList]));
		};
		
		// Make request
		this.json(u, callback);
		/*new Json.Remote(u, {
			onComplete : callback,
			onFailure : Linkr.onJsonFailure
		}).send();*/
	},
	
	/*
	 * Handles errors
	 */
	onJsonFailure : function(r)
	{
		var d	= new Element('div', {styles : {padding : 10, 'background-color' : '#dddddd'}});
		
		// Error code
		new Element('span').setHTML('Linkr says:').injectInside(d);
		new Element('a', {
			href : 'http://wikipedia.org/wiki/Error_'+ r.status,
			target : '_blank',
			styles : {margin : '0 0 0 3px'}
		}).setHTML('error '+ r.status).injectInside(d);
		
		// Response
		if (r.responseText.length > 0) {
			new Element('div', {
				styles : {padding : 5, 'font-style' : 'italic'}
			}).setHTML(r.responseText).injectInside(d);
		}
		
		// Support page
		new Element('div', {styles : {margin : 5}}).injectInside(d);
		new Element('a', {
			href : 'http://codes.statsful.com/linkr/support',
			target : '_blank',
			styles : {margin : '0 3px 0 0'}
		}).setHTML('Click here').injectInside(d);
		new Element('span').setHTML('to go to the Linkr Support page.').injectInside(d);
		
		// Display error
		Linkr.htmlLayout('JSON Error!', d);
	},

	/*
	 * Dump a variable to view its source
	 *	http://binnyva.blogspot.com/2005/10/dump-function-javascript-equivalent-of.html
	 */
	dump : function(obj, deep, inArray, level)
	{
		var dump	= '';
		var pad		= '';
		if (!level) level	= 0;
		for (var j = 0; j < level + 1; j++) {
			pad	+= '   ';
		}
		
		// Array
		// obj.constructor
		var type	= $type(obj);
		pad		+= '['+ type.capitalize() +'] ';
		if (type == 'object' || type == 'array' || type == 'class' || type == 'regexp')
		{
			for(var i in obj)
			{
				var v	= obj[i];
				if (typeof v != 'function' || deep) {
					var t	= $type(v);
					if (t == 'object' || t == 'array' || t == 'class' || t == 'regexp') {
						dump	+= pad + i + ':\n';
						dump	+= this.dump(v, deep, true, level + 1);
					} else {
						dump	+= pad + i +': "'+ v +'"\n';
					}
				}
			}
		}
		
		// Element
		else if (type == 'element' || type == 'textnode' || type == 'whitespace') {
			dump	= '['+ obj.nodeName.capitalize() +']';
		}
		
		// Strings
		else {
			dump	= '['+ type.capitalize() +'] '+ obj;
		}
		
		// Dump
		if (!inArray) alert(dump);
		else return dump;
	}
});

LinkrAPI.implement(new Events, new Options);

/*
 * Sprintf for Javascript
 *	http://www.webtoolkit.info/javascript-sprintf.html
 */
sprintfWrapper =
{
	init : function ()
	{
		// Performance checks
		if ($type(arguments) != 'arguments') return '';
		if (arguments.length < 1) return '';
		if ($type(arguments[0]) != 'string') return '';
		if ($type(RegExp) == false) return '';
		
		var string = arguments[0];
		var exp = new RegExp(/(%([%]|(\-)?(\+|\x20)?(0)?(\d+)?(\.(\d)?)?([bcdfosxX])))/g);
		var matches = new Array();
		var strings = new Array();
		var convCount = 0;
		var stringPosStart = 0;
		var stringPosEnd = 0;
		var matchPosEnd = 0;
		var newString = '';
		var match = null;

		while (match = exp.exec(string))
		{
			if (match[9]) convCount += 1;
			
			stringPosStart = matchPosEnd;
			stringPosEnd = exp.lastIndex - match[0].length;
			strings[strings.length] = string.substring(stringPosStart, stringPosEnd);
			
			matchPosEnd = exp.lastIndex;
			matches[matches.length] = {
				match: match[0],
				left: match[3] ? true : false,
				sign: match[4] || '',
				pad: match[5] || ' ',
				min: match[6] || 0,
				precision: match[8],
				code: match[9] || '%',
				negative: parseInt(arguments[convCount]) < 0 ? true : false,
				argument: String(arguments[convCount])
			};
		}
		strings[strings.length] = string.substring(matchPosEnd);
		
		if (matches.length == 0) return string;
		if ((arguments.length - 1) < convCount) return '';
		
		var code = null;
		var match = null;
		var i = null;
		
		for (i = 0; i < matches.length; i++)
		{
			if (matches[i].code == '%') {
				substitution = '%';
			}
			else if (matches[i].code == 'b') {
				matches[i].argument = String(Math.abs(parseInt(matches[i].argument)).toString(2));
				substitution = sprintfWrapper.convert(matches[i], true);
			}
			else if (matches[i].code == 'c') {
				matches[i].argument = String(String.fromCharCode(parseInt(Math.abs(parseInt(matches[i].argument)))));
				substitution = sprintfWrapper.convert(matches[i], true);
			}
			else if (matches[i].code == 'd') {
				matches[i].argument = String(Math.abs(parseInt(matches[i].argument)));
				substitution = sprintfWrapper.convert(matches[i]);
			}
			else if (matches[i].code == 'f') {
				matches[i].argument = String(Math.abs(parseFloat(matches[i].argument)).toFixed(matches[i].precision ? matches[i].precision : 6));
				substitution = sprintfWrapper.convert(matches[i]);
			}
			else if (matches[i].code == 'o') {
				matches[i].argument = String(Math.abs(parseInt(matches[i].argument)).toString(8));
				substitution = sprintfWrapper.convert(matches[i]);
			}
			else if (matches[i].code == 's') {
				matches[i].argument = matches[i].argument.substring(0, matches[i].precision ? matches[i].precision : matches[i].argument.length);
				substitution = sprintfWrapper.convert(matches[i], true);
			}
			else if (matches[i].code == 'x') {
				matches[i].argument = String(Math.abs(parseInt(matches[i].argument)).toString(16));
				substitution = sprintfWrapper.convert(matches[i]);
			}
			else if (matches[i].code == 'X') {
				matches[i].argument = String(Math.abs(parseInt(matches[i].argument)).toString(16));
				substitution = sprintfWrapper.convert(matches[i]).toUpperCase();
			}
			else {
				substitution = matches[i].match;
			}
			newString += strings[i];
			newString += substitution;
		}
		newString += strings[i];
		
		return newString;
	},

	convert : function(match, nosign)
	{
		if (nosign) match.sign = '';
		else match.sign = match.negative ? '-' : match.sign;
		var l = match.min - match.argument.length + 1 - match.sign.length;
		var pad = new Array(l < 0 ? 0 : l).join(match.pad);
		if (!match.left)
		{
			if (match.pad == '0' || nosign)
				return match.sign + pad + match.argument;
			return pad + match.sign + match.argument;
		}
		else
		{
			if (match.pad == '0' || nosign)
				return match.sign + match.argument + pad.replace(/0/g, ' ');
			return match.sign + match.argument + pad;
		}
	}
};

window.addEvent('domready', function()
{
	/*
	 * Load return page
	 */
	Linkr.addEventListener('onLoad', function()
	{
		// Return page
		if ($type(Linkr.__fr) == 'function')
			return Linkr.__fr.attempt();
		
		// Return page cookie
		var c	= new Hash.Cookie('linkrpage');
		
		// Arguments
		var a	= c.hasKey('a') ? c.get('a') : [];
		
		// Function
		if (c.hasKey('f')) {
			var f	= c.get('f');
			return Linkr.__af(window[f], a);
		}
		
		// Object & method
		if (c.hasKey('o') && c.hasKey('m')) {
			var o	= c.get('o');
			var m	= c.get('m');
			return Linkr.__af([window[o], m], a);
		}
	});
	
	/*
	 * Add onClose event
	 */
	if (window.parent.SqueezeBox)
		window.parent.SqueezeBox.addEvent('onClose', function() {
			Linkr.fireEvent('onClose');
		});
	
	// Not ideal...
	else
		self.onunload	= function() {
			Linkr.fireEvent('onClose');
		}
});

