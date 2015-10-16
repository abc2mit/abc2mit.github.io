/*
 * Linkr - Tree Class
 *
 */

var LinkrTree	=
{
	/*
	 * Settings
	 */
	options :
	{
		// Plugin title
		title : 'Linkr',
		
		// Object name
		objName : 'LinkrTree',
		
		// Cookie settings
		cookieSettings : {
			duration : 14,
			autoSave : false
		}
	},
	
	/*
	 * Search functionality
	 */
	search :
	{
		// Search queries by type
		sql : {}
	},
	
	/*
	 * Layout helper methods
	 */
	tmpl :
	{
		// Tree setup
		setupTree : function(r) {
			this.setMsg('No Tree setup method found!');
		},
		
		// Display tree
		buildTree : function(is, el)
		{
			// Checks
			var isChild	= $type(el) == 'element';
			if ($type(is) != 'object') {
				if (isChild) is = {};
				else return this.setMsg('Invalid tree items');
			}
			
			// Create tree element
			if (isChild) {
				var te	= new Element('ul').injectInside(el);
			} else {
				var te	= new Element('ul', {
					id : 'tree'
				}).injectInside($('treeDiv'));
			}
			
			// Populate items
			for (n in is)
			{
				// Item
				if (!$defined(is[n])) continue;
				var i	= this.getItemObject(is[n]);
				i.id	= n;
				
				// List element
				i.element	= new Element('li').injectInside(te);
				
				// Span container
				var ie	= new Element('span').injectInside(i.element);
				
				// Icon
				i.icon	= new Element('img', {
					src : i.expandIcon,
					align : 'absmiddle'
				}).injectInside(ie);
				
				// Link
				i.link	= new Element('a', {
					href : 'javascript:void(0)',
					events : {
						click : this.tmpl.toggle.bind(this, [i])
					}
				}).setHTML(i.title).injectInside(ie);
				
				// Tree reference
				this.itemRef[n]	= i;
			}
			
			return te;
		},
		
		// Toggle tree item
		toggle : function(i)
		{
			// Get item object
			i	= this.getItemObject(i);
			
			// Collapse
			if (i.list) this.tmpl.collapse(i);
			
			// Expand
			else this.tmpl.expand(i);
			
			// Select item
			this.selectItem(i);
		},
		
		// Expand tree item
		expand : function(i)
		{
			// Get item object
			i	= this.getItemObject(i);
			
			// Performance check
			if (i.list) return;
			
			// Expand tree
			if (i.children)
			{
				// Display loading icon
				i.icon.setProperty('src', Linkr.loadingIcon);
				
				// Insert children items
				this.tmpl.children(i.children, i);
			}
		},
		expandAll : function()
		{
			// Loading icon
			var o	= new Element('img', {
				src : Linkr.loadingIcon,
				alt : '...'
			});
			$('xt').replaceWith(o);
			
			var z;
			for (id in this.itemRef)
			{
				// Save last item
				z	= id;
				
				// Expand item
				this.tmpl.expand(this.itemRef[id]);
			}
			
			// Wait for last item to remove loading icon
			this.timer	= function(i, o)
			{
				if (i.list)
				{
					this.timer	= $clear(this.timer);
					o.replaceWith(new Element('a', {
						id : 'xt',
						href : 'javascript:void(0);',
						styles : {'font-weight' : 'bold'},
						events : {
							click : this.tmpl.expandAll.bind(this)
						}
					}).setHTML(this._('EXPAND')));
				}
			}.periodical(50, this, [this.itemRef[z], o]);
		},
		
		// Insert child items
		children : function(dbr, i)
		{
			// Children tree already loaded
			if ($defined(i.treeItems)) {
				i.list	= this.tmpl.buildTree(i.treeItems, i.element);
				return i.icon.setProperty('src', i.collapseIcon);
			}
			
			// Database query
			if ($type(dbr) == 'array')
			{
				// Get query
				var q	= this._buildSQL(dbr[0], $pick(dbr[1], false));
				
				// Return with results
				return this.tmpl.dbList(q, 0, 0, this.tmpl.children.bind(this), [i]);
			}
			
			// Check for errors
			var err	= this.tmpl.isError(dbr);
			if (err !== false) return this.setMsg(err);
			
			// Tree elements
			i.treeItems	= this.tmpl.setupTree(dbr.result, i.level + 1);
			
			// Buid children tree
			i.list	= this.tmpl.buildTree(i.treeItems, i.element);
			
			// Display collapse icon
			i.icon.setProperty('src', i.collapseIcon);
		},
		
		// Collapse tree item
		collapse : function(i)
		{
			// Get item object
			i	= this.getItemObject(i);
			
			// Performance check
			if (!i.list) return;
			
			// Collapse tree
			i.list.remove();
			i.list	= false;
			
			// Display expand icon
			i.icon.setProperty('src', i.expandIcon);
		},
		collapseAll : function()
		{
			for (id in this.itemRef)
				this.tmpl.collapse(this.itemRef[id]);
		},
		
		// LinkrHelper.dbList shortcut
		dbList : function(q, s, l, c, a) {
			var d	= Linkr.dbList(q, s, l, c, a);
			return d === false ? this.home() : null;
		},
		
		// LinkrHelper.dbObject shortcut
		dbObject : function(q, c, a) {
			var d	= Linkr.dbObject(q, c, a);
			return d === false ? this.home() : null;
		},
		
		// Check database object for errors
		isError : function(o)
		{
			// Check object
			if (!Linkr.isDBRO(o))
				return 'Linkr Error: Invalid database object';
			
			// Check for errors
			if (Linkr.isError(o))
				return 'Database Error: '+ o.msg;
			
			return false;
		}
	},
	
	/*
	 * Default icons
	 */
	icons :
	{
		expand : 'administrator/images/expandall.png',
		collapse : 'administrator/images/collapseall.png'
	},
	
	/*
	 * Item reference
	 */
	itemRef : {},
	
	/*
	 * Returns an instance of LinkrTree
	 *
	 *	var o		= {};	// Object settings
	 *	var so	= {};	// Search settings
	 *	var lo	= {};	// Layout methods
	 *	var to	= {};	// Miscellaneous methods
	 *	var myTree	= LinkrTree.getInstance(o, so, lo, to);
	 */
	getInstance : function(c, o, p, y)
	{
		// Defaults
		c	= $type(c) == 'object' ? c : {};
		p	= $type(p) == 'object' ? p : {};
		y	= $type(y) == 'object' ? y : {};
		
		// Copy
		var copy	= new Class(this);
		copy.implement(new Options, new Events);
		
		// Return copy
		return new copy(c, o, p, y);
	},
	
	/*
	 * Initialize object instance. Called automatically
	 */
	initialize : function(o, r, s, t)
	{
		// Set settings
		this.setOptions(o);
		
		// Save initial tree elements
		this._tree	= r;
		
		// Set search options
		this.search	= $merge(this.search, $pick(s, {}));
		
		// Set template methods
		this.tmpl	= $merge(this.tmpl, $pick(t, {}));
		
		// Fixup method references
		for (m in this.layout)
			this.layout[m]	= this.layout[m].bind(this);
		for (m in this.tmpl)
			this.tmpl[m]	= this.tmpl[m].bind(this);
		
		// Call custom "initialize" function
		if (this.options.initialize) this.options.initialize.call(this);
	},
	
	/*
	 * Messages
	 */
	setMsg : function(m, r)
	{
		// Display message
		if (['array', 'string'].contains($type(m)) && m.length > 0) {
			var w	= new Element('div').injectBefore($('tree'));
			Linkr.insertMsgDiv(m, w);
		}
		
		// Return passed value
		return r;
	},
	
	/*
	 * Landing page. Fades in the home page
	 */
	landing : function() {
		Linkr.delayIdleDiv('layout', this.home.bind(this));
	},
	
	home : function()
	{
		// Tree DIV
		var tree	= new Element('div', {id : 'treeDiv'});
		Linkr.htmlLayout(this.options.title, tree);
		
		// Toolbar links
		Linkr.htmlTBLinks([
			['xt', this._('EXPAND'), this.tmpl.expandAll.bind(this)],
			['tgls', this._('CONFIGURE_LINK')],
			[0, this._('GET_LINK'), this.insert.bind(this)]
		]).injectInside(tree);
		
		// Link configuration
		Linkr.htmlConfig('', [
			['linkText', this._('LC_TEXT'), Linkr.getSelectedText()],
			['target', this._('LC_TARGET'), [
				['_self', this._('LC_TARGET_SELF'), 'selected'],
				['_blank', this._('LC_TARGET_BLANK')]
			]],
			['linkTitle', this._('LC_TITLE')],
			['linkClass', this._('LC_CLASS')],
			['linkRelation', this._('LC_RELATION')],
			['linkURL', 'URL', Linkr.htmlInput('linkURL', 'index.php', {
				'class' : 'inputbox value',
				disabled : true
			})]
		]).setStyles({
			'border-bottom' : '2px solid #ccc'
		}).injectInside(tree);
		
		// Tree elements
		var is	= this.tmpl.setupTree(this._tree, 0);
		
		// Display tree
		this.tmpl.buildTree(is);
		
		// Selected item notice
		this._itemDiv	= new Element('div', {
			styles : {
				position : 'fixed',
				bottom : 5,
				right : 40,
				margin : 0,
				padding : '3px 10px',
				width : 'auto',
				border : '1px solid #aaa',
				'border-left-width' : 5,
				'border-right-width' : 5,
				'background-color' : '#eee'
			}
		}).setHTML('('+ this._('NONE') +')').injectInside($('layout'));
		
		// Setup toggle link
		Linkr.createToggleLink('settings', 'tgls');
		
		// Return page
		Linkr.setReturnPage([this.options.objName, 'home']);
	},
	
	/*
	 * Formats a tree item object
	 */
	getItemObject : function(o)
	{
		// Get item from reference
		if (['number', 'string'].contains($type(o)))
			o	= $pick(this.itemRef[o], {});
		
		// Check item
		if ($type(o) != 'object') o = {};
		
		// Title
		var t	= $pick(o.title, '');
		if ($type(t) != 'string' || t.length < 1) o.title = '...?';
		
		// Description
		var d	= $pick(o.description, '');
		if ($type(d) != 'string' || d.length < 1)
			o.description	= false;
		
		// Link
		var u	= $pick(o.url, '');
		if ($type(u) != 'string' || u.length < 1) o.url = false;
		
		// Level
		o.level	= $pick(o.level, 0);
		
		// Expand icon
		var e	= $pick(o.expandIcon, '');
		if ($type(e) != 'string' || e.length < 5)
			o.expandIcon	= Linkr.siteRoot + this.icons.expand;
		
		// Collapse icon
		var c	= $pick(o.collapseIcon, '');
		if ($type(c) != 'string' || c.length < 5)
			o.collapseIcon	= Linkr.siteRoot + this.icons.collapse;
		
		// Children
		var k	= $pick(o.children, 0);
		if ($type(k) != 'array' || k.length < 1)
			o.children	= false;
		
		return o;
	},
	
	/*
	 * Sets item link
	 */
	selectItem : function(i)
	{
		i	= this.getItemObject(i);
		
		// Get URL, title
		if (i.url)
		{
			var u = i.url, c = '#ffd',
				t = i.title,
				h = Linkr.getDefaultText(i.title);
		}
		else
		{
			var u = 'index.php', c = '#eee',
				t = '('+ this._('NONE') +')',
				h = Linkr.getSelectedText();
		}
		
		// Set URL, title
		$('linkURL').value	= u;
		$('linkText').value = h;
		this._itemDiv.setStyle('background-color', c).setHTML(t);
	},
	
	/*
	 * Inserts a link
	 */
	insert : function()
	{
		// Check elements
		var te	= $('linkText');
		if (!te) return this.setMsg('Missing "linkText" element');
		var ue	= $('linkURL');
		if (!ue) return this.setMsg('Missing "linkURL" element');
		
		// Check text
		var txt	= te.getValue();
		if (txt.trim().length == 0)
			return alert(Linkr.missingText);
		
		// Get URL
		var u	= ue.getValue();
		
		// Add format
		var fe	= $('format');
		if (fe)
		{
			var f	= fe.getValue();
			switch (f)
			{
				case 'rss':
				case 'atom':
					u	+= '&format=feed&type='+ f;
					break;
				
				default:
					if (f != '' && f != 'html') u += '&format='+ f;
			}
		}
		
		Linkr.link(u, txt);
	},
	link : function() {
		return this.insert();
	},
	
	/*
	 * Build SQL search query
	 */
	_buildSQL : function(t, w)
	{
		// Query word
		var wt	= $type(w);
		if (wt == 'string' && w.length > 0)
			w = Linkr.dbQuote(w);
		else if (wt != 'number')
			w = false;
		
		// Get query
		var q	= false;
		var qo	= $pick(this.search.sql[t], false);
		var qot	= $type(qo);
		if (qot != 'string' && qot != 'function')
			return this.setMsg('Invalid SQL query type "'+ t +'"', '');
		
		// From function
		if (qot == 'function')
			q	= qo.attempt([w], this);
		
		// From string
		if (qot == 'string')
			q	= qo.replace(/\[query]/g, w).replace(/\[access]/g, Linkr.userAccessLevel);
		
		// Check query
		if ($type(q) != 'string' || q.length < 1)
			return this.setMsg('Invalid SQL query type "'+ t +'"', '');
		
		return q;
	},
	
	/*
	 * Cookie
	 */
	getCookie : function(rc) {
		return Linkr.getCookie(this.options.objName, this.options.cookieSettings, rc);
	},
	setCookie : function(v) {
		return Linkr.setCookie(this.options.objName, v, this.options.cookieSettings);
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
		dump		= dump.replace(/\n/g, '<br/>').replace(/\s/g, '&nbsp;');
		this.display(dump);
		return false;
	}
};



