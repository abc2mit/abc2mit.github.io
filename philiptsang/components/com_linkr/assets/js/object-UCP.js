/*
 * Linkr - Generic Class
 *
 */

var LinkrObject	=
{
	/*
	 * Settings
	 */
	options :
	{
		// Plugin title
		title : 'Linkr',
		
		// Object name
		objName : 'LinkrObject',
		
		// List styles
		selectListStyle : {
			margin : '0 2px',
			'text-align' : 'center'
		},
		
		// Layout header format
		layoutHeaderDirection : 'horizontal',
		
		// Itemid selection
		// 0: None
		// 1: Most recent
		defaultItemid : 0,
		
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
		// Default search type
		defaultType : '',
		defaultListType : false,
		
		// Enable use of search options
		enableOptions : false,
		
		// Enable sorting of search results
		enableSorting : false,
		
		// Enable displaying of uncategorized items
		enableUncategorized : 0,
		
		// Search type names
		name : {},
		
		// Search options
		options : {},
		
		// Methods for displaying results (by type)
		result : {},
		
		// Current type (multiple types only)
		selectedType : '',
		
		// Search sorting by type
		sort : {},
		
		// Sort titles
		sortName : {
			id : 'ID',
			title : 'Title',
			name : 'Name',
			date : 'Date',
			created : 'Created',
			modified : 'Modified',
			ordering : 'Ordering'
		},
		
		// Search queries by type
		sql : {}
	},
	
	/*
	 * Sort methods
	 */
	sort :
	{
		// Sort by id
		id : function(r, k) {
			k	= $pick(k, 'id');
			return r.sort(function(a, b) {
				return (a[k].toInt() - b[k].toInt());
			});
		},
		
		// Sort by title
		title : function(r, k) {
			k	= $pick(k, 'title');
			return r.sort(function(a, b) {
				return a[k] == b[k] ? 0 : (a[k] > b[k] ? 1 : -1);
			});
		},
		
		// Sort by date
		date : function(r, k) {
			k	= $pick(k, 'date');
			return r.sort(function(a, b) {
				return (b[k].replace(/(\s|:|-)/g, '') - a[k].replace(/(\s|:|-)/g, ''));
			});
		},
		
		name : function(r) {
			return this.sort.title(r, 'name');
		},
		
		created : function(r) {
			return this.sort.date(r, 'created');
		},
		
		modified : function(r) {
			return this.sort.date(r, 'modified');
		},
		
		ordering : function(r) {
			return this.sort.id(r, 'ordering');
		}
	},
	
	/*
	 * Layout methods
	 */
	layout :
	{
		// Generic layout
		_genericLayout : function(lyt)
		{
			// Content DIV
			var cnt	= new Element('div');
			
			// Links
			var cfg	= false;
			var tb	= $pick(lyt.toolbar, 0);
			if ($type(tb) == 'object')
				cfg	= this.tmpl.insertToolbar(tb, cnt);
			
			// Link configuration... load URL later
			this.tmpl.getConfig($pick(lyt.config, {})).injectInside(cnt);
			
			// Item description container
			var inf	= new Element('div', {
				styles : {margin : 5}
			}).injectInside(cnt);
			
			// Header
			var ll	= false;
			var hd	= $pick(lyt.header, 0);
			if ($type(hd) == 'object')
				ll	= this.tmpl.insertHeader(hd, inf);
			
			// Description
			var ds	= $pick(lyt.description, 0);
			if ($type(ds) == 'object')
				this.tmpl.insertContent(ds, inf);
			
			// Load layout
			this.display(cnt);
			
			// Setup config link
			if (cfg) this.tmpl.setupToggleLink(inf);
			
			// Load lists
			if (ll) ll.each(function(lf) {lf.call()});
			
			// Return page
			var r	= $pick(lyt.returnPage, false);
			if (r) {
				var rt	= $type(r);
				if (rt == 'string') this.setReturnPage(r);
				if (rt == 'array') this.setReturnPage(r[0], r[1]);
			}
			
			// Callback function
			if ($defined(lyt.onComplete) && $type(lyt.onComplete) == 'function')
				lyt.onComplete.attempt();
		},
		_genericLayoutDelayed : function(a, d) {
			Linkr.delayDisplay('layout', $pick(d, 70), this.layout._genericLayout.bind(this, [a]));
		},
		
		// Uncategorized layout
		uncategorized : function() {
			this.setMsg('"Uncategorized" layout not set');
			this.display();
		}
	},
	
	/*
	 * Layout helper methods
	 */
	tmpl :
	{
		// Toolbar links e.g. back, configure link, get link
		insertToolbar : function(b, d)
		{
			var bl	= [];
			var c	= false;
			for (l in b)
			{
				// Back link
				if (l == 'back') {
					bl.include([0, '&#171; '+ this._('BACK').toLowerCase(), b.back]);
				}
				
				// Config link
				else if (l == 'config') {
					c	= true;
					bl.include(['toggleConfig', this._('CONFIGURE_LINK')]);
				}
				
				// Insert link
				else if (l == 'insert') {
					bl.include([0, this._('GET_LINK'), b.insert]);
				}
				
				// Custom link
				else if ($type(b[link]) == 'array') {
					bl.include(b[link]);
				}
			}
			
			// Insert toolbar links
			Linkr.htmlTBLinks(bl).injectInside(d);
			
			// Return "true" if config is enabled
			return c;
		},
		
		// Creates a header useful for a layout
		insertHeader : function(hd, d)
		{
			// Header container
			var hr	= new Element('div', {
				styles : {'margin-bottom' : 5}
			}).injectInside(d);
			
			// Header element format
			if (this.options.layoutHeaderDirection == 'vertical')
			{
				var he	= 'div';
				var hs	= {
					'margin-bottom' : 5,
					'text-align' : 'center'
				};
			} else {
				var he	= 'span';
				var hs	= {'margin-left' : 15};
			}
			
			// List loading functions
			var l	= [];
			
			for (t in hd)
			{
				// String
				var h	= hd[t];
				var ht	= $type(h);
				if (ht == 'string')
				{
					new Element(he, {
						styles : hs
					}).setHTML(h).injectInside(hr);
				}
				
				// Label/value pair
				else if (ht == 'array')
				{
					var hee	= new Element(he, {
						styles : hs
					}).injectInside(hr);
					
					// Insert label
					new Element('span').setHTML(h[0] +': ').injectInside(hee);
					
					// Insert value
					new Element('strong').setHTML(h[1]).injectInside(hee);
				}
				
				// Element
				else if (ht == 'element') {
					h.injectInside(hr);
				}
				
				// Select list (to load later)
				else if (ht == 'object')
				{
					var wrp	= new Element(he, {
						styles : hs
					}).injectInside(hr);
					
					// Insert label
					if ($defined(h.label)) {
						new Element('span').setHTML(h.label).injectInside(wrp);
					}
					
					// Insert select list
					new Element('span', {
						id : t
					}).setHTML(Linkr.loading).injectInside(wrp);
					
					// Load list function
					l.include(this.loadList.bind(this, [
						$pick(h.type, this.search.defaultType), t,
						$pick(h.databaseID, false),
						$pick(h.styles, false),
						$pick(h.enableUncategorized, 0),
						$pick(h.sqlOptions, false)
					]));
				}
			}
			
			// Return list loaders
			return l.length > 0 ? l : false;
		},
		
		// Renders item description
		insertContent : function(c, d)
		{
			// Element already rendered
			if ($defined(c.element)) {
				c.element.injectInside(d);
			}
			
			// Render description
			else
			{
				// DIV style
				var s	= $pick(c.styles, {
					padding : 3,
					'background-color' : '#eeeeee'
				});
				
				// Insert description
				new Element('div', {
					styles : s
				}).setHTML($pick(c.text, '')).injectInside(d);
			}
		},
		
		// Creates toggle link for config box
		setupToggleLink : function(e, a, b)
		{
			// Link IDs
			b	= $pick(b, 'settings');
			a	= $pick(a, 'toggleConfig');
			
			// Toggle link + fade content away (not so nice)
			/*Linkr.createToggleLink(b, a, 400, function(s)
			{
				// Get element
				e	= $(e);
				if (!e) return;
				
				// Start effect
				var fx	= new Fx.Style(e, 'opacity', {duration : 250});
				s ? fx.start(1.0, 0) : fx.start(0, 1.0);
			});*/
			
			// Create toggle link
			Linkr.createToggleLink(b, a);
		},
		
		// Renders link configuration DIV
		getConfig : function(o)
		{
			// Config div
			var cd	= new Element('div', {id : 'settings'});
			
			// Title
			new Element('div', {
				styles : {
					'text-align' : 'center',
					'font-weight' : 'bold'
				}
			}).setHTML(this._('LC_ATTRIBUTES')).injectInside(cd);
			
			// Link text
			this.tmpl.getLabel('linkText', this._('LC_TEXT')).injectInside(cd);
			this.tmpl.getInput('linkText', Linkr.getDefaultText($pick(o.text, ''))).injectInside(cd);
			this.tmpl.clear().injectInside(cd);
			
			// Link target
			if ($pick(o.target, true))
			{
				this.tmpl.getLabel('target', this._('LC_TARGET')).injectInside(cd);
				Linkr.htmlSelectCustom([
					['_self', this._('LC_TARGET_SELF'), 'selected'],
					['_blank', this._('LC_TARGET_BLANK')]
				], {id : 'target'}).injectInside(cd);
				this.tmpl.clear().injectInside(cd);
			}
			
			// Link title
			this.tmpl.getLabel('linkTitle', this._('LC_TITLE')).injectInside(cd);
			this.tmpl.getInput('linkTitle', $pick(o.linkTitle, '')).injectInside(cd);
			this.tmpl.clear().injectInside(cd);
			
			// Link class
			this.tmpl.getLabel('linkClass', this._('LC_CLASS')).injectInside(cd);
			this.tmpl.getInput('linkClass', $pick(o.linkClass, '')).injectInside(cd);
			this.tmpl.clear().injectInside(cd);
			
			// Link relation
			this.tmpl.getLabel('linkRelation', this._('LC_RELATION')).injectInside(cd);
			this.tmpl.getInput('linkRelation', $pick(o.linkRelation, '')).injectInside(cd);
			this.tmpl.clear().injectInside(cd);
			
			// Single URL
			var u	= $pick(o.url, 'index.php');
			var ut	= $type(u);
			if (ut == 'string')
				this.tmpl.getInput('linkURL', u, 'hidden').injectInside(cd);
			
			// Several URLs
			if (ut == 'object' && $defined(u.list))
			{
				this.tmpl.getLabel('linkURL', $pick(u.label, 'Itemid')).injectInside(cd);
				Linkr.htmlSelectCustom(u.list, {
					id : 'linkURL',
					name : 'linkURL',
					'class' : 'inputbox value'
				}).injectInside(cd);
				this.tmpl.clear().injectInside(cd);
			}
			
			// Itemids
			var iid	= false;
			if (ut == 'object' && $defined(u.options))
			{
				iid	= new Element('div', {
					id : 'urlDiv'
				}).setHTML(Linkr.wideLoading).injectInside(cd);
			}
			
			// Page format
			var frt	= $pick(o.format, false);
			if ($type(frt) == 'array' && frt.length > 0) {
				this.tmpl.getLabel('format', this._('LC_FORMAT')).injectInside(cd);
				Linkr.htmlSelectCustom(frt, {id : 'format'}).injectInside(cd);
				this.tmpl.clear().injectInside(cd);
			}
			
			// Page anchors
			var ans	= $pick(o.anchor, 0);
			if ($type(ans) == 'array')
			{
				// Anchor list
				ans	= [['', '-- '+ this._('PICK') +' --']].merge(ans);
				
				// Display list
				this.tmpl.getLabel('anchor', this._('LC_ANCHOR')).injectInside(cd);
				Linkr.htmlSelectCustom(ans, {id : 'anchor'}).injectInside(cd);
				this.tmpl.clear().injectInside(cd);
			}
			
			// Pagebreaks
			var pbs	= $pick(o.pageBreak, 0);
			if ($type(pbs) == 'array')
			{
				// Page break links
				var cbs	= [['', '-- '+ this._('PICK') +' --']];
				pbs.each(function(b, i) {
					if (b.length > 1)
						cbs.include([i, b]);
				});
				cbs.include(['all', '('+ this._('ALL') +')']);
				
				// Display list
				this.tmpl.getLabel('pageBreak', this._('LC_PAGEBREAK')).injectInside(cd);
				Linkr.htmlSelectCustom(cbs, {id : 'pageBreak'}).injectInside(cd);
				this.tmpl.clear().injectInside(cd);
			}
			
			// Custom configuration
			if ($type($pick(o.custom, 0)) == 'object')
			{
				for (c in o.custom)
				{
					var co	= o.custom[c];
					var cot	= $type(co);
					
					// Preformatted element
					if (cot == 'element') {
						co.injectInside(cd);
						this.tmpl.clear().injectInside(cd);
					}
					
					// Label-input pair
					else if (cot == 'array' && co.length == 2) {
						co[0].injectInside(cd);
						co[1].injectInside(cd);
						this.tmpl.clear().injectInside(cd);
					}
				}
			}
			
			// Load URLs
			if (iid) this._getItemidUrls(u.query, u.options, iid, true);
			
			return cd;
		},
		
		// Returns a label element
		getLabel : function(a, b) {
			return new Element('label', {'for' : a}).setHTML(b);
		},
		
		// Returns an input element
		getInput : function(a, b, c) {
			return new Element('input', {
				id : a,
				name : a,
				value : b,
				type : $pick(c, 'text'),
				'class' : 'inputbox value'
			});
		},
		
		// "Clear" div shortcut
		clear : function() {
			return new Element('div', {styles : {clear : 'both'}});
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
		},
		
		// Returns anchors in text
		htmlAnchors : function(txt)
		{
			// Performance check
			if ($type(txt) != 'string' || txt.length < 10) return false;
			
			// Get anchors
			var as	= txt.match(/<a[^>]*name="([^"]+)"[^>]*>[^<]*<\/a>/ig);
			if (!as) return false;
			
			// Return anchor names
			var ans	= [];
			as.each(function(a)
			{
				var s	= a.indexOf('name="');
				var e	= a.indexOf('"', s + 6);
				ans.include(a.substring(s + 6, e));
			});
			
			return ans;
		},
		
		// Returns page breaks
		pageBreaks : function(txt)
		{
			// Performance check
			if ($type(txt) != 'string' || txt.length < 35) return false;
			
			// Get page breaks
			var bs	= txt.match(/<hr[^>]*class="system-pagebreak"[^>]*\/>/ig);
			if (!bs) return false;
			
			// Return page break names
			var bns	= [];
			bs.each(function(b)
			{
				var s	= b.indexOf('title="');
				var e	= b.indexOf('"', s + 7);
				bns.include(b.substring(s + 7, e));
			});
			
			return bns;
		}
	},
	
	/*
	 * Sliders
	 */
	sliders : {},
	
	/*
	 * Returns an instance of LinkrObject
	 *
	 *	var o		= {};	// Object settings
	 *	var so	= {};	// Search settings
	 *	var lo	= {};	// Layout methods
	 *	var to	= {};	// Miscellaneous methods
	 *	var myObject	= LinkrObject.getInstance(o, so, lo, to);
	 */
	getInstance : function(c, o, p, y)
	{
		// Defaults
		c	= $type(c) == 'object' ? c : {};
		o	= $type(o) == 'object' ? o : {};
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
	initialize : function(o, s, l, t)
	{
		// Set settings
		this.setOptions(o);
		
		// Set search options
		this.search	= $merge(this.search, $pick(s, {}));
		
		// Set layout methods
		this.layout	= $merge(this.layout, $pick(l, {}));
		
		// Set template methods
		this.tmpl	= $merge(this.tmpl, $pick(t, {}));
		
		// Fixup method references
		for (m in this.layout)
			this.layout[m]	= this.layout[m].bind(this);
		for (m in this.tmpl)
			this.tmpl[m]	= this.tmpl[m].bind(this);
		
		// Sort titles
		this.search.sortName	= $merge(this.search.sortName, {
			title : this._('TITLE'),
			name : this._('NAME'),
			date : this._('DATE'),
			created : this._('CREATED'),
			modified : this._('MODIFIED'),
			ordering : this._('ORDERING')
		});
		
		// Call custom "initialize" function
		if (this.options.initialize) this.options.initialize.call(this);
	},
	
	/*
	 * Messages
	 */
	msg : [],
	setMsg : function(m, r)
	{
		// Save message
		if ($type(m) == 'string' && m.length > 0) {
			this.msg[this.msg.length]	= m;
		} else if ($type(m) == 'array' && m.length > 0) {
			this.msg.merge(m);
		}
		
		// Return passed value
		return r;
	},
	
	/*
	 * Sets Linkr return page
	 */
	setReturnPage : function(m, a)
	{
		// Check class name
		var o	= this.options.objName;
		if (o == 'LinkrObject' || o == '')
			return this.setMsg('Invalid object name (options.objName)');
		
		// Home page
		if (m == 'home' || m == 'landing')
			return Linkr.setReturnPage([o, m]);
		
		// Check method
		if ($type(this.layout[m]) != 'function')
			return this.setMsg('Invalid return page');
		
		// Set return page
		Linkr.setReturnPage([o, '_return'], [m, a]);
	},
	
	/*
	 * Return page callback method
	 */
	_return : function(p, a)
	{
		// Call layout function
		if ($type(this.layout[p]) == 'function')
			return this.layout[p].attempt(a, this);
		
		// Go home
		this.setMsg('Notice: could not load "'+ p +'"');
		this.home();
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
			Linkr.htmlLayout(this.options.title, h);
		}
	},
	
	/*
	 * Landing page. Fades in the home page
	 */
	landing : function() {
		Linkr.delayIdleDiv('layout', this.home.bind(this));
	},
	
	/*
	 * Home page (default layout)
	 */
	home : function()
	{
		// Container DIV
		var cnt	= new Element('div').setStyle('text-align', 'center');
		
		// Select DIV
		var ld	= new Element('div', {
			id : 'selectDiv'
		}).injectInside(cnt);
		
		// Select list... load later
		new Element('span', {
			id : 'selectList'
		}).setHTML(Linkr.loading).injectInside(ld);
		
		// "Search" elements
		var sr	= this._homeInsertSearch(cnt, ld);
		
		// Display home layout
		this.display(cnt);
		
		// Create sliders
		this.sliders.select	= new Fx.Slide(ld, {duration : 100});
		this.sliders.search	= new Fx.Slide(sr[0], {duration : 100}).hide();
		if ($type(sr[1]) == 'element')
			this.sliders.options	= new Fx.Slide(sr[1], {duration : 100}).hide();
		
		// Somehow this is referenced throughout the LinkrObject instances
		else
			this.sliders.options	= false;
		
		// Populate select list
		var t	= this.search.defaultType;
		var u	= this.search.enableUncategorized;
		u		= u > 0 ? true : false;
		var s	= this.options.selectListStyle;
		this.loadList(t, 'selectList', false, s, u);
		
		// Set Linkr return page
		this.setReturnPage('home');
	},
	_homeInsertSearch : function(cDiv, sDiv)
	{
		// Get search options
		var o	= this.search;
		
		// "Toggle search" link
		new Element('span', {
			styles : {'margin-left' : 5}
		}).setHTML(this._('OR') +' ').injectInside(sDiv);
		new Element('a', {
			href : 'javascript:void(0);',
			events : {
				click : function(e) {
					new Event(e).stop();
					this.sliders.select.slideOut();
					this.sliders.search.slideIn.delay(100, this.sliders.search);
				}.bind(this)
			}
		}).setHTML(this._('BTN_SEARCH') +' &#187;').injectInside(sDiv);
		
		// Search DIV
		var qDiv	= new Element('div', {
			id : 'searchDiv'
		}).injectInside(cDiv);
		
		// "Switch back" link
		var back	= $pick(o.selectListText, this._('CATEGORY'));
		new Element('a', {
			href : 'javascript:void(0);',
			styles : {'margin-right' : 5},
			events : {
				click : function(e) {
					new Event(e).stop();
					this.sliders.search.slideOut();
					this.sliders.select.slideIn.delay(100, this.sliders.select);
				}.bind(this)
			}
		}).setHTML('&#171; '+ back.toLowerCase()).injectInside(qDiv);
		
		// Search input
		new Element('input', {
			id : 'query',
			type : 'text',
			'class' : 'inputbox',
			styles : {
				margin : '0 2px',
				width : 130
			}
		}).injectInside(qDiv);
		
		// Search type list
		if (this.isSearchMultiple())
		{
			var qol	= [];
			var qnl	= $pick(o.name, {});
			
			// Set search type
			if (o.selectedType.length < 1) {
				var cs	= this.getCookie();
				o.selectedType	= $pick(cs.st, '');
			}
			
			// Add search types to list
			for (ty in o.sql)
			{
				// Get option name
				var n	= $pick(qnl[ty], ty);
				
				// "Selected" attribute
				var a	= ty == o.selectedType ? 'selected' : '';
				
				// Add option to list
				qol.include([ty, n, a]);
			}
			
			// Create type select list
			Linkr.htmlSelectCustom(qol, {
				id : 'searchType'
			}).injectInside(qDiv);
		}
		else {
			new Element('input', {
				id : 'searchType',
				type : 'hidden',
				value : (o.defaultSearchType ? o.defaultSearchType : o.defaultType)
			}).injectInside(qDiv);
		}
		
		// Search button
		new Element('input', {
			type : 'button',
			value : this._('BTN_SEARCH'),
			styles : {margin : '0 2px'},
			events : {
				click : function(e)
				{
					new Event(e).stop();
					if ($('query').getValue().trim().length < 1)
						return $('query').focus();
					
					Linkr.delayIdleDiv('results', true, 70);
					this.dbSearch.delay(90, this, true);
				}.bind(this)
			}
		}).injectInside(qDiv);
		
		// Search options
		var oDiv	= false;
		if (o.enableOptions) oDiv = this._homeInsertOptions(cDiv, qDiv);
		
		// Search results
		new Element('div', {id : 'results'}).injectInside(cDiv);
		
		return [qDiv, oDiv];
	},
	_homeInsertOptions : function(cDiv, sDiv)
	{
		// Search settings
		var o	= this.search;
		
		// Checks
		if ($type(o.options) != 'object') return this.setMsg('Invalid search options');
		
		// Options button
		new Element('input', {
			id : 'searchOptions',
			type : 'button',
			value : this._('BTN_OPTIONS'),
			styles : {margin : '0 2px'},
			events : {
				click : function(e) {
					new Event(e).stop();
					this.sliders.options.toggle();
				}.bind(this)
			}
		}).injectInside(sDiv);
		
		// Search options
		var oDiv	= new Element('div', {
			styles : {
				margin : '5px 150px',
				'text-align' : 'left'
			}
		}).injectInside(cDiv);
		
		// Option values
		var def	= this.getCookie();
		
		// Load search options
		for (son in o.options)
		{
			// Get label
			var so	= o.options[son];
			var soId	= 'so-'+ son;
			var sol	= $pick(so.label, son);
			this.tmpl.getLabel(soId, sol).injectInside(oDiv);
			
			// Get selected value
			var sos	= $pick(def[son], '');
			sos	= sos.length > 1 ? sos : $pick(so.defaultValue, '-');
			
			// Options select list
			var soa	= [];
			for (soe in so.values) {
				var val	= so.values[soe];
				var sel	= soe == sos ? 'selected' : '';
				soa.include([soe, val, sel]);
			}
			Linkr.htmlSelectCustom(soa, {
				id : soId,
				'class' : 'option-element',
				styles : {'text-align' : 'center'}
			}).injectInside(oDiv);
			this.tmpl.clear().injectInside(oDiv);
		}
		
		return oDiv;
	},
	
	/*
	 * Retrieves list of URLs (by Itemid)
	 *	Original call: Query variables, menu/link options, config DIV
	 *	Callback: dbResult, menu/link options, config DIV
	 */
	_getItemidUrls : function(a, b, c, z)
	{
		// Get ItemIDs
		if (z === true)
			return Linkr.getItemids(a, b, [this, '_getItemidUrls'], [b, c]);
		
		// Check for errors
		var e	= this.tmpl.isError(a);
		if (e !== false) return this.display(e);
		
		// No Itemids
		var d	= b.defaultURL;
		if (a.result.length < 1)
			return c.replaceWith(this.tmpl.getInput('linkURL', d, 'hidden'));
		
		// URL list
		var ul	= [[d, this._('NONE')]];
		a.result.each(function(i, n)
		{
			ul.include([
				d +'&Itemid='+ i.id,
				Linkr.UTF8.Decode(i.name) +' ('+ i.id +')'
			]);
		});
		
		// Selected item
		if (this.options.defaultItemid == 0)
			ul[0].include('selected');
		else
			ul[ul.length-1].include('selected');
		
		// Select list, label
		var sl	= Linkr.htmlSelectCustom(ul, {
			id : 'linkURL',
			name : 'linkURL',
			'class' : 'inputbox value'
		});
		c.replaceWith(sl);
		this.tmpl.clear().injectAfter(sl);
		this.tmpl.getLabel('linkURL', 'ItemID').injectBefore(sl);
	},
	
	/*
	 * Populates a select list
	 *	Original call: type, element ID, database ID, styles, "uncategorized" element, SQL options
	 *	Callback: dbResult, type, element ID, styles, "uncategorized" element
	 */
	loadList : function(a, b, c, d, e, f)
	{
		// Get sections from database
		if ($type(a) == 'string')
		{
			// Build SQL query
			var id	= c ? c.toInt() : false;
			var sql	= this._buildSQL(a, id, false, f);
			
			// Get list
			return Linkr.dbList(sql, 0, 0, [this, 'loadList'], [a, b, d, e]);
		}
		
		// Check for errors
		var err	= this.tmpl.isError(a);
		if (err !== false) return this.display(err);
		
		// Set "Uncategorized" to "true" or "false"
		if (e !== true && e !== false) {
			e	= this.search.enableUncategorized == 2 ? true : false;
		}
		
		// Empty
		if ((!a.result || a.result.length < 1) && e !== true) {
			var list	= new Element('span', {
				styles : {'font-weight' : 'bold'}
			}).setHTML(this._('NONE'));
			return $(c).replaceWith(list);
		}
		
		// Create select list
		var styles	= $type(d) == 'object' ? d : this.options.selectListStyle;
		var list	= new Element('select', {
			id : c,
			styles : styles,
			events : {
				change : function(el)
				{
					// Get element, value
					var el	= $(el);
					if (!el) return;
					var id	= el.getValue().toInt();
					
					// Uncategorized
					if (id == 0) {
						Linkr.delayIdleDiv('results', true, 70);
						this.layout.uncategorized.delay(90, this, true);
					}
					
					// List option
					else if (id > -1) {
						Linkr.delayIdleDiv('layout', true, 70);
						this.layout[b].delay(90, this, id);
					}
				}.bind(this, [c])
			}
		});
		
		// First option
		var pick	= this._('PICK');
		if (this.isSearchMultiple() && $defined(this.search.name[b])) {
			pick	+= ' ('+ this.search.name[b].toLowerCase() +')';
		}
		new Element('option', {
			value : -1
		}).setHTML('-- '+ pick +' --').injectInside(list);
		
		// "Uncategorized" option
		if (e === true) {
			new Element('option', {
				value : 0
			}).setHTML('-- '+ this._('UNCATEGORIZED') +' --').injectInside(list);
		}
		
		// List options
		if ($type(a.result) == 'array' && a.result.length > 0) {
			a.result.each(function(o, i) {
				var on	= $pick(o.title, $pick(o.name, ''));
				new Element('option', {
					value : o.id
				}).setHTML(Linkr.UTF8.Decode(on)).injectInside(list);
			});
		}
		
		// Load list
		$(c).replaceWith(list);
	},
	
	/*
	 * Searches the database
	 */
	dbSearch : function(dbr, ty)
	{
		// Query database
		if (dbr === true)
		{
			// Search text
			var txt	= $('query').getValue().trim();
			
			// Hide options, show loading icon
			if (this.sliders.options) this.sliders.options.slideOut();
			
			// Save search options
			var opts	= {};
			var oList	= $$('.option-element');
			var sets	= this.getCookie();
			if (oList && oList.length > 0)
			{
				oList.each(function(o) {
					var on	= o.getProperty('id').replace('so-', '');
					opts[on]	= o.getValue();
					sets[on]	= opts[on];
				});
			}
			
			// Save search type
			var t	= $('searchType').getValue();
			sets.st		= t;
			this.search.selectedType	= t;
			this.setCookie(sets);
			
			// Build query
			var sql		= this._buildSQL(t, false, txt, opts);
			var done	= Linkr.dbList(sql, 0, 100, [this, 'dbSearch'], [t]);
			return done === false ? this.home() : null;
		}
		
		// Display results
		Linkr.delayDisplay('results', 70, [this, 'listResults'], [dbr, ty]);
	},
	listResults : function(dbr, ty, hd)
	{
		// Check for errors
		var err	= this.tmpl.isError(dbr);
		if (err !== false) return this.display(err);
		if (dbr.result.length < 1) {
			return this.display(new Element('div', {
				styles : {
					'margin-top' : 15,
					'letter-spacing' : 3
				}
			}).setHTML(this._('NORESULTS')), 'results');
		}
		
		// Format results
		var rDiv	= new Element('div');
		
		// Heading
		if ($type(hd) == 'string') {
			var hm	= hd;
		} else {
			var tn	= $pick(this.search.name[ty], ty);
			var hm	= this._('SEARCH_TYPE_RESULTS', [tn, dbr.result.length]);
		}
		new Element('div', {
			styles : {
				width : '100%',
				margin : '10px 0 7px 0',
				'font-weight' : 'bold'
			}
		}).setHTML(hm).injectInside(rDiv);
		
		// Sorting
		if (this.search.enableSorting === true)
			this._insertSortLinks(rDiv, ty);
		
		// Clear
		this.tmpl.clear().setHTML(' ').injectInside(rDiv);
		
		// Search results
		this.searchType	= ty;
		this.searchResults	= dbr.result;
		var rlist	= new Element('div', {id : 'rlist'});
		this.displayResults(false, rlist);
		rlist.injectInside(rDiv);
		
		// Clear
		this.tmpl.clear().setHTML(' ').injectInside(rDiv);
		
		// Display results
		this.display(rDiv, 'results');
	},
	_insertSortLinks : function(rDiv, ty)
	{
		// Get sort columns
		var cs	= $pick(this.search.sort[ty], ['id', 'title']);
		if ($type(cs) != 'array' || cs.length < 1) return;
		
		// Sort DIV
		var sDiv	= new Element('div', {
			styles : {
				margin : '0 0 2px 0',
				padding : '0 0 2px 0',
				'border-bottom' : '1px solid #eeeeee'
			}
		}).injectInside(rDiv);
		
		// Instructions
		new Element('span').setHTML(this._('SORT_BY') +' ').injectInside(sDiv);
		
		// Sorting
		cs.each(function(c)
		{
			// Get column name (for read)
			var n	= $pick(this.search.sortName[c], c);
			
			// Insert sort link
			this._getSortLink(c, n).injectInside(sDiv);
			new Element('span').setHTML(' &bull; ').injectInside(sDiv);
		}, this);
		
		// Remove last bullet
		sDiv.getLast().remove();
	},
	
	displayUncategorized : Class.empty,
	
	/*
	 * Returns sort link for search
	 */
	_getSortLink : function(s, t)
	{
		return new Element('a', {
			href : '#',
			id : 'sort-'+ s,
			'class' : 'sort-link',
			styles : {margin : '0 3px'},
			events : {
				click : function(e) {
					new Event(e).stop();
					this.displayResults(s);
				}.bind(this)
			}
		}).setHTML(t);
	},
	
	/*
	 * Displays sorted search results
	 */
	displayResults : function(s, d)
	{
		// Check search results
		var r	= this.searchResults;
		if ($type(r) != 'array' || r.length < 1) return;
		
		// Get results list
		var d	= d ? d : $('rlist');
		if ($type(d) != 'element') return;
		
		// Sort results
		if (s && $defined(this.sort[s])) {
			r = this.sort[s].attempt([r], this);
		}
		
		// Display results
		d.empty();
		var ty	= this.searchType;
		r.each(function(sr)
		{
			// 3 col width: 188px
			var i	= new Element('div', {
				styles : {
					margin : 3,
					width : 560,
					'float' : 'left',
					'text-align' : 'left'
				}
			}).injectInside(d);
			
			// Get name
			var n	= false;
			if ($defined(this.search.result[ty]))
				var n	= this.search.result[ty].attempt(sr, this);
			if (!n)
			{
				n	= $pick(sr.title, $pick(sr.name, '??'));
				n	= Linkr.UTF8.Decode(n);
			}
			
			// Add link
			new Element('a', {
				href : 'javascript:void(0)',
				events : {
					click : function(e) {
						new Event(e).stop();
						this.layout[ty].attempt(sr.id.toInt(), this);
					}.bind(this)
				}
			}).setHTML('&bull; '+ n).injectInside(i);
		}, this);
		
		// Update sort links
		if (s)
		{
			$$('.sort-link').each(function(el)
			{
				if (el.getProperty('id') == 'sort-'+ s) {
					el.setStyle('color', '#666666');
				} else {
					el.setStyle('color', '');
				}
			});
		}
	},
	
	/*
	 * Checks if multiple search is enabled
	 */
	isSearchMultiple : function()
	{
		if ($defined(this._mstype)) return this._mstype;
		
		// Check search type
		if ($type(this.search.sql) != 'object') {
			this.search.sql	= {};
			this._mstype	= false;
			return this.setMsg('Invalid search type', false);
		}
		
		// Set multiple search
		this._mstype	= $pick(this.search.multiple, false);
		this._mstype	= this._mstype ? true : false;
		return this._mstype;
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
		if (txt.trim().length == 0) {
			return alert(Linkr.missingText);
		}
		
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
		
		// Add pagebreak page number
		var pb	= $('pageBreak');
		if (pb)
		{
			if (pb.getValue() == 'all')
				u += '&showall=1';
			else if (pb.getValue() > 0)
				u += '&limitstart='+ pb.getValue();
		}
		
		// Add page anchor
		var an	= $('anchor');
		if (an && an.getValue() != '')
			u += '#'+ an.getValue();
		
		Linkr.link(u, txt);
	},
	link : function() {
		return this.insert();
	},
	
	/*
	 * Build SQL search query
	 */
	_buildSQL : function(t, id, w, o)
	{
		// Database ID
		idt	= $type(id);
		if (idt != 'number' && (idt != 'string' || id.length < 1))
			id = false;
		
		// Search word
		if ($type(w) == 'string' && w.length > 0)
			w = Linkr.dbQuote('%'+ w.toLowerCase() +'%', true);
		else
			w = false;
		
		// Search options
		o	= $pick(o, {});
		
		// Get query
		var q	= false;
		var qo	= $pick(this.search.sql[t], false);
		var qot	= $type(qo);
		if (qot != 'object' && qot != 'function') {
			var e	= 'Invalid SQL query type "'+ t +'"';
			return this.setMsg(e, '');
		}
		
		// From function
		if (qot == 'function') {
			q	= qo.attempt([id, w, o], this);
		}
		
		// From string
		if (qot == 'object') {
			q	= $pick(qo.all, '');
			if (w) q = $pick(qo.query, q).replace(/\[query]/g, w);
			if (id) q = $pick(qo.id, q).replace(/\[id]/g, id);
		}
		
		// Check query
		if ($type(q) != 'string' || q.length < 1) {
			var e	= 'Invalid SQL query type "'+ t +'"';
			return this.setMsg(e, '');
		}
		
		return q;
	},
	
	/*
	 * Cookie shortcuts
	 */
	getCookie : function(rc) {
		return Linkr.getCookie(this.options.objName, this.options.cookieSettings, rc);
	},
	setCookie : function(v) {
		return Linkr.setCookie(this.options.objName, v, this.options.cookieSettings);
	},
	
	/*
	 * UTF8 shortcuts
	 */
	UTF8en : function(str) {
		return Linkr.UTF8.Encode(str);
	},
	UTF8de : function(str) {
		return Linkr.UTF8.Decode(str);
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



