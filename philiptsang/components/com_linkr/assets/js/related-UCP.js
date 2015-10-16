/*
 * Linkr - Related Links Plugin
 *
 */

var LinkrRelated	=
{
	/*
	 * Messages
	 */
	msg : [],
	setMsg : function(m) {
		if ($type(m) == 'string' && m.length > 0)
			this.msg[this.msg.length]	= m;
		if ($type(m) == 'array' && m.length > 0)
			this.msg.merge(m);
	},
	
	/*
	 * Loads content into Linkr
	 */
	display : function(c, e)
	{
		var h	= new Element('div');
		
		// Messages
		this.setMsg(Linkr.getAllErrors());
		Linkr.insertMsgDiv(this.msg, h);
		this.msg	= [];
		
		// Content
		Linkr.htmlContent(h, c, false);
		
		// Load in element
		if (['element', 'string'].contains($type(e))) {
			h.injectInside($(e).empty());
		}
		
		// Layout
		else {
			Linkr.htmlLayout(this._('RELATED_ARTICLES'), h);
		}
	},
	
	/*
	 * Landing page
	 */
	landing : function() {
		Linkr.delayIdleDiv('layout', this.home.bind(this));
	},
	
	/*
	 * Displays the layout
	 */
	home : function(a)
	{
		// Set Linkr return page
		Linkr.setReturnPage(['LinkrRelated', 'home']);
		
		// Plugin check
		if (this.isPluginInstalled === false)
			return this.display(this._('NOTICE_INSTALL'));
		
		// Parameters check
		if (this.loadedByDefault === true)
			this.setMsg(this._('NOTICE_RELATED'));
		
		// Container DIV
		var c	= new Element('div').setStyle('text-align', 'center');
		
		// Input box
		new Element('span').setHTML(this._('TYPE_IN_KEYWORDS')).injectInside(c);
		new Element('input', {
			id : 'words',
			type : 'text',
			styles : {margin : '0 5px'}
		}).injectInside(c);
		new Element('input', {
			type : 'button',
			value : this._('PREVIEW'),
			events : {
				click : function(e) {
					new Event(e).stop();
					LinkrRelated.preview();
				}
			}
		}).injectInside(c);
		
		// Preview
		new Element('div', {
			id : 'preview'
		}).injectInside(c);
		
		// Display home layout
		this.display(c);
		
		// Load data from linkr anchor
		a	= this.parseAnchor(a);
		if (a && a.keywords && a.keywords.length > 0) {
			$('words').value	= a.keywords;
			this.preview(a);
		}
	},
	
	/*
	 * Create related links preview
	 */
	preview : function(a)
	{
		// Get keywords
		var w	= $('words').value;
		if (w.length < 1) {
			alert(this._('TYPE_IN_KEYWORDS'));
			return $('words').focus();
		}
		
		Linkr.delayIdleDiv('preview', true, 70);
		LinkrRelated._psc.delay(90, LinkrRelated, [w, a]);
	},
	_psc : function(w, a)
	{
		// Get request URL
		var u	= Linkr.createRequest('ra');
		u	+= '&kw='+ Linkr.Base64.Encode(w, true);
		if (a) {
			u	+= '&title='+ Linkr.Base64.Encode((a.title || ''), true);
			u	+= '&limit='+ (a.limit || '5');
			u	+= '&exclude='+ (a.exclude || '');
		}
		
		// Make request
		Linkr.json(u, this.displayPreview.bind(this));
	},
	
	/*
	 * Display preview
	 */
	displayPreview : function(i)
	{
		// Check for errors
		if ($type(i) != 'object')
			return this.display('An error occured', 'preview');
		if (Linkr.isError(i))
			return this.display('Error: '+ i.msg, 'preview');
		if ($type(i.articles) == 'string')
			return this.display(i.articles, 'preview');
		if ($type(i.articles) != 'array')
			return this.display('An error occured', 'preview');
		
		// Display preview
		Linkr.delayDisplay('preview', 70, [this, '_dpsc'], i);
	},
	
	_dpsc : function(i)
	{
		// Content array
		var c	= [];
		
		// Links
		var ls	= [];
		ls.include(['trs', this._('RL_CONFIG_TITLE')]);
		ls.include(['tal', this._('RL_CONFIG_SHOW_ALL')]);
		ls.include([false, this._('RL_CONFIG_UPDATE'), [this, 'update']]);
		ls.include([false, this._('GET_REL'), [this, 'insert']]);
		var tb	= Linkr.htmlTBLinks(ls);
		c.include(tb);
		
		// Config
		var cfg	= Linkr.htmlConfig('', [
			['title', this._('TITLE'), Linkr.UTF8.Decode(i.title)],
			['limit', this._('RL_CONFIG_LIMIT'), i.limit.toInt()]
		], this._('RL_CONFIG_TITLE'));
		c.include(cfg);
		
		// Article list
		var li	= new Element('div', {
			id : 'articles',
			styles : {
				padding : 5,
				'margin-bottom' : 5,
				'background-color' : '#eeeeee'
			}
		});
		var chk	= new Element('input', {
			type : 'checkbox',
			'class' : 'rl-exclude-check',
			styles : {border : 'none'}
		});
		
		// Exclude
		new Element('div', {
			styles : {
				padding : '0 30px',
				'text-align' : 'center',
				'font-weight' : 'bold'
			}
		}).setHTML(this._('NOTICE_RELATED_RANDOM')).injectInside(li);
		
		// Articles
		var fa	= [];
		i.articles.each(function(a)
		{
			var ti	= new Element('a', {
				href : 'javascript:void(0)',
				title : Linkr.UTF8.Decode(a.title),
				styles : {'padding-left' : 5},
				events : {
					click : function(e) {
						$(a.id).checked	= !$(a.id).checked;
					}
				}
			}).setHTML(Linkr.UTF8.Decode(a.stitle));
			var cb	= chk.clone().setProperty('id', a.id);
			
			var el	= new Element('div', {
				styles : {
					padding : 5,
					width : 275,
					'float' : 'left',
					'text-align' : 'left'
				}
			});
			
			if (i.exclude.contains(a.id.toInt()))
				cb.checked	= true;
			else
				fa.include(a);
			
			// Insert item
			cb.injectInside(el);
			ti.injectInside(el);
			el.injectInside(li);
		});
		
		// Clear
		new Element('div', {
			styles : {clear : 'both'}
		}).injectInside(li);
		c.include(li);
		
		// Preview wrapper
		var pw	= new Element('div');
		new Element('div', {
			styles : {
				'letter-spacing' : 5,
				'font-weight' : 'bold',
				'background-color' : '#dddddd'
			}
		}).setHTML(this._('PREVIEW')).injectInside(pw);
		var pre	= new Element('div', {
			styles : {
				padding : 5,
				'text-align' : 'left',
				border : '1px solid #dddddd',
				'background-color' : '#f9f9f9'
			}
		}).injectInside(pw);
		
		new Element('div', {
			styles : {
				'font-weight' : 'bold',
				'text-align' : 'center'
			}
		}).setHTML(Linkr.UTF8.Decode(i.title)).injectInside(pre);
		var ul	= new Element('ul').injectInside(pre);
		
		// Preview
		var limit	= Math.max(1, Math.min(i.limit, fa.length - 1));
		if (fa.length > 0)
		{
			for (n = 0; n < limit; n++)
			{
				var li	= new Element('li').injectInside(ul);
				var ti	= Linkr.UTF8.Decode(fa[n].title);
				new Element('a', {
					href : 'javascript:void(0)'
				}).setHTML(ti).injectInside(li);
			}
		}
		ul.injectInside(pre);
		c.include(pw);
		
		// Display content
		this.display(c, 'preview');
		
		// Toggle links
		Linkr.createToggleLink('settings', 'trs');
		Linkr.createToggleLink('articles', 'tal');
		
		// Internet Explorer fix
		if (window.ie && i.exclude.length > 0) {
			i.exclude.each(function(id) {
				var el	= document.getElementById(id);
				if ($type(el) == 'element') {
					el.checked	= true;
				}
			});
		}
	},
	
	/*
	 * Update related links preview
	 */
	update : function()
	{
		// Get keywords
		var w	= $('words').value || '';
		if (w.length < 1) {
			alert(this._('TYPE_IN_KEYWORDS'));
			return $('words').focus();
		}
		
		// Other options
		var l	= $('limit').value || 5;
		var t	= $('title').value || '';
		var x	= this.getExcludeList();
		
		Linkr.delayIdleDiv('preview', true, 70);
		LinkrRelated._usc.delay(90, LinkrRelated, [w, l, t, x]);
	},
	
	_usc : function(w, l, t, x)
	{
		// Get request URL
		var u	= Linkr.createRequest('ra') +
					'&kw='+ Linkr.Base64.Encode(w, true) +
					'&limit='+ l.toInt() +
					'&title='+ Linkr.Base64.Encode(t, true);
		if (x) u += '&exclude='+ x.join(',');
		
		// Make request
		Linkr.json(u, this.displayPreview.bind(this));
	},
	
	/*
	 * Get list of excluded articles
	 */
	getExcludeList : function()
	{
		var l	= [];
		$$('.rl-exclude-check').each(function(el) {
			if (el.checked == true)
				l.include(el.getProperty('id'));
		});
		
		return l.length > 0 ? l : false;
	},
	
	/*
	 * Insert related articles anchor
	 */
	insert : function()
	{
		// Get keywords
		var w	= $('words').value || '';
		if (w.length < 1) {
			alert(this._('TYPE_IN_KEYWORDS'));
			return $('words').focus();
		}
		
		// Related links options
		w	= Linkr.urlencode(Linkr.UTF8.Encode(w));
		var a	= '{linkr:related;keywords:'+ w;
		
		// Exclude list
		var x	= this.getExcludeList();
		if (x)
			a	+= ';exclude:'+ x.join(',');
		
		// Limit
		var l	= $('limit').value.toInt();
		if (l > 0)
			a	+= ';limit:'+ l;
		
		// Title
		var t	= $('title').value;
		if (t.length)
			a	+= ';title:'+ Linkr.urlencode(Linkr.UTF8.Encode(t));
		
		// Insert anchor
		var r	= (Linkr.linkrAnchor == true);
		return r ? Linkr.insert(a +'}') : Linkr.insertAtEnd(a +'}');
	},
	
	/*
	 * Parse linkr anchor
	 */
	parseAnchor : function(an)
	{
		if ($type(an) != 'string' || !an.contains('{linkr:related;'))
			return false;
		
		var a	= {};
		an	= an.substr(an.indexOf('{') + 1);
		an	= an.substr(0, an.lastIndexOf('}'));
		p	= an.split(';');
		p.each(function(kv)
		{
			if (kv.indexOf(':') > 0) {
				var k	= kv.substr(0, kv.indexOf(':'));
				var v	= kv.substr(kv.indexOf(':') + 1);
				v	= Linkr.UTF8.Decode(Linkr.urldecode(v));
			} else {
				var k	= kv;
				var v	= true;
			}
			a[k]	= v;
		});
		
		a.anchor	= an;
		return a;
	},
	
	/*
	 * LinkrHelper.getL18N shortcut
	 */
	_ : function(t, a) {
		return Linkr.getL18N(t, a);
	},
	
	/*
	 * Debugging
	 */
	dump : function(a) {
		var dump	= Linkr.dump(a, false, true);
		dump		= dump.replace(/\n/g, '<br/>');
		dump		= dump.replace(/\s/g, '&nbsp;');
		this.display(dump);
		return false;
	}
};

