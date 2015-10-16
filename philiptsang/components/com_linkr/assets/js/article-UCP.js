/*
 * Linkr - Articles Plugin
 *
 */

/*
 * Object settings
 */
var ArtOpts	=
{
	// Plugin title
	title : 'Articles',
	
	// Object name
	objName : 'LinkrArticle',
	
	// Use slug
	slug : true,
	
	// Layout header format
	layoutHeaderDirection : 'vertical',
	
	// Initialize
	initialize : function()
	{
		// Plugin title
		this.options.title	= this._('ARTICLES');
		
		// Search options
		this.search.selectListText	= this._('SECTION');
		this.search.options.column	=
		{
			label : this._('SEARCH_IN'),
			values : {
				'-' : '-- '+ this._('PICK') +' --',
				title : this._('TITLE'),
				desc : this._('DESCRIPTION')
			}
		};
		
		// Query type names
		this.search.name	= {
			section : this._('SECTIONS'),
			category : this._('CATEGORIES'),
			article : this._('ARTICLES')
		};
	}
};

/*
 * Search settings
 */
var ArtSrch	=
{
	defaultType : 'section',
	selectedType : 'article',
	enableOptions : true,
	enableSorting : true,
	enableUncategorized : 1,
	multiple : true,
	options : {},
	result :
	{
		article : function(sr)
		{
			var n	= sr.title;
			
			// Add modified date
			if (sr.modified.length > 0 && sr.modified != '0000-00-00 00:00:00') {
				n	+= ' ('+ this._('MODIFIED') +': '+
				sr.modified.substr(0, 10).replace(/-/g, '.') +')';
			}
			
			// Add created date
			else if (sr.created.length > 0 && sr.created != '0000-00-00 00:00:00') {
				n	+= ' ('+ this._('CREATED') +': '+
				sr.created.substr(0, 10).replace(/-/g, '.') +')';
			}
			
			return n;
		}
	},
	sort : {
		section : ['id', 'title', 'ordering'],
		category : ['id', 'title', 'ordering'],
		article : ['id', 'title', 'created', 'modified', 'ordering']
	},
	sql :
	{
		// Section query
		section : function(id, q, o)
		{
			var sql	=
			'SELECT id, title, ordering'+
			' FROM #__sections'+
			' WHERE published = 1'+
			' AND access <= '+ Linkr.userAccessLevel +
			' AND scope = '+ Linkr.dbQuote('content');
			
			// Search query
			if (q)
			{
				if ($defined(o.column) && o.column.length > 1) {
					var c	= o.column == 'desc' ? 'description' : 'title';
					sql	+= ' AND LOWER('+ c +') LIKE '+ q;
				}
				else
				{
					sql	+=
					' AND (LOWER(title) LIKE '+ q +
					' OR LOWER(name) LIKE '+ q +
					' OR LOWER(alias) LIKE '+ q +
					' OR LOWER(description) LIKE '+ q +')';
				}
			}
			
			return sql +' ORDER BY ordering ASC, title ASC, name ASC';
		},
		
		category : function(id, q, o)
		{
			var sql	=
			'SELECT c.id, c.title, c.ordering'+
			' FROM #__categories AS c'+
			' LEFT JOIN #__sections AS s ON s.id = c.section'+
			' WHERE c.published = 1'+
			' AND c.access <= '+ Linkr.userAccessLevel +
			' AND CAST(s.id AS CHAR) IS NOT NULL';
			
			// Search query
			if (q)
			{
				if ($defined(o.column) && o.column.length > 1) {
					var c	= o.column == 'desc' ? 'description' : 'title';
					sql	+= ' AND LOWER(c.'+ c +') LIKE '+ q;
				}
				else
				{
					sql	+=
					' AND (LOWER(c.title) LIKE '+ q +
					' OR LOWER(c.name) LIKE '+ q +
					' OR LOWER(c.alias) LIKE '+ q +
					' OR LOWER(c.description) LIKE '+ q +')';
				}
			}
			
			// Section ID
			if ($type(id) == 'number')
				sql	+= ' AND s.id = '+ id;
			
			return sql +' ORDER BY c.ordering ASC, c.title ASC, c.name ASC';
		},
		
		article : function(id, q, o)
		{
			var sql	=
			'SELECT a.id, a.title, a.created, a.modified, a.ordering'+
			' FROM #__content AS a'+
			' LEFT JOIN #__categories AS c ON c.id = a.catid'+
			' WHERE a.state = 1'+
			' AND a.access <= '+ Linkr.userAccessLevel;
			
			// Search query
			if (q)
			{
				if ($defined(o.column) && o.column.length > 1)
				{
					if (o.column == 'desc')
					{
						sql	+=
						' AND ( LOWER(a.introtext) LIKE '+ q +
						' OR LOWER(a.fulltext) LIKE '+ q +' ) ';
					}
					else {
						sql	+= ' AND LOWER(a.title) LIKE '+ q;
					}
				}
				else
				{
					sql	+=
					' AND ( LOWER(a.title) LIKE '+ q +
					' OR LOWER(a.alias) LIKE '+ q +
					' OR LOWER(a.introtext) LIKE '+ q +
					' OR LOWER(a.fulltext) LIKE '+ q +' ) ';
				}
			}
			
			// Section ID
			if ($type(id) == 'number')
				sql	+= ' AND c.id = '+ id;
			
			return sql +' ORDER BY a.ordering ASC, a.title ASC';
		}
	}
};

/*
 * Layout methods
 */
var ArtLay	=
{
	// Section layout
	section : function(dbs)
	{
		// Get section information
		if ($type(dbs) == 'number')
		{
			// SQL query
			var q	=
			'SELECT id, title, description'+
			' FROM #__sections'+
			' WHERE published = 1 AND id = '+ dbs +
			' AND access <= '+ Linkr.userAccessLevel;
			
			// Get results
			return this.tmpl.dbObject(q, this.layout.section.bind(this));
		}
		
		// Check for errors
		var e	= this.tmpl.isError(dbs);
		if (e !== false) return this.display(e);
		
		// Section
		var d	= false;
		var s	= dbs.result;
		var id	= s.id.toInt();
		var t	= this.UTF8de(s.title);
		if (s.description.length > 0) d = {
			text : this.UTF8de(s.description)
		};
		
		// Fade in layout
		this.layout._genericLayoutDelayed({
			
			description : d,
			returnPage : ['section', id],
			
			// Toolbar settings
			toolbar : {
				back : this.home.bind(this),
				config : true,
				insert : this.insert.bind(this)
			},
			
			// Header settings
			header :
			{
				// Section title (key name not important)
				a : [this._('SECTION'), t],
				
				// Category list (load later)
				categoryList : {
					type : 'category',
					databaseID : id,
					label : this._('CATEGORIES') +': '
				}
			},
			
			// Link configuration settings
			config :
			{
				// Default text
				text : t,
				
				// URLs (Itemid)
				url :
				{
					query : ['view=section', 'id='+ id],
					options : {
						componentid : 20,
						defaultURL : this.tmpl.url('section', id)
					}
				},
				
				// Formats
				format : [
					['html', 'HTML', 'selected'],
					['rss', 'RSS'],
					['atom', 'Atom']
				]
			}
		});
	},
	
	// Category layout
	category : function(dbc)
	{
		// Get category information
		if ($type(dbc) == 'number')
		{
			// SQL query
			var q	=
			'SELECT id, title, description, section,'+
			' CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(":", id, alias)'+
			' ELSE id END AS slug'+
			' FROM #__categories'+
			' WHERE published = 1 AND id = '+ dbc +
			' AND access <= '+ Linkr.userAccessLevel;
			
			// Get results
			return this.tmpl.dbObject(q, this.layout.category.bind(this));
		}
		
		// Check for errors
		var e	= this.tmpl.isError(dbc);
		if (e !== false) return this.display(e);
		
		// Category details
		var d	= false;
		var c	= dbc.result;
		var id	= c.id.toInt();
		c.section	= c.section.toInt();
		var slug	= this.options.slug ? c.slug : id;
		var t	= this.UTF8de(c.title);
		if (c.description.length > 0) d = {
			text : this.UTF8de(c.description)
		};
		
		// Fade in layout
		this.layout._genericLayoutDelayed({
			
			description : d,
			returnPage : ['category', id],
			
			// Toolbar settings
			toolbar : {
				back : this.layout.section.bind(this, [c.section]),
				config : true,
				insert : this.insert.bind(this)
			},
			
			// Header settings
			header :
			{
				// Category title
				a : [this._('CATEGORY'), t],
				
				// Category list (load later)
				articleList : {
					type : 'article',
					databaseID : id,
					label : this._('ARTICLES') +': '
				}
			},
			
			// Link configuration settings
			config :
			{
				// Default text
				text : t,
				
				// URLs (Itemid)
				url :
				{
					query : {
						a : ['view=category', 'id='+ id],
						b : ['view=section', 'id='+ c.section]
					},
					options : {
						componentid : 20,
						defaultURL : this.tmpl.url('category', slug)
					}
				},
				
				// Formats
				format : [
					['html', 'HTML', 'selected'],
					['rss', 'RSS'],
					['atom', 'Atom']
				]
			}
		});
	},
	
	// Article layout
	article : function(dba)
	{
		// Get article information
		if ($type(dba) == 'number')
		{
			// Build SQL query
			var q	=
			'SELECT a.id, a.title, a.introtext AS description, a.catid, a.sectionid,'+
			' CONCAT_WS("", a.introtext, a.fulltext) AS text,'+
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias)'+
			' ELSE a.id END as slug,'+
			' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias)'+
			' ELSE c.id END as cslug'+
			' FROM #__content AS a'+
			' LEFT JOIN #__categories AS c ON c.id = a.catid'+
			' WHERE a.state = 1 AND a.id = '+ dba +
			' AND a.access <= '+ Linkr.userAccessLevel;
			
			// Get results
			return this.tmpl.dbObject(q, this.layout.article.bind(this));
		}
		
		// Check for errors
		var e	= this.tmpl.isError(dba);
		if (e !== false) return this.display(e);
		
		// Article details
		var d	= false;
		var a	= dba.result;
		var id	= a.id.toInt();
		a.catid	= a.catid.toInt();
		a.sectionid	= a.sectionid.toInt();
		var txt	= this.UTF8de(a.text);
		var slug	= this.options.slug ? a.slug : id;
		var cslug	= this.options.slug ? a.cslug : a.catid;
		var t	= this.UTF8de(a.title);
		if (a.description.length > 0) d = {
			text : this.UTF8de(a.description)
		};
		
		// "Back" link
		var bck	= this.home.bind(this);
		if (a.catid > 0)
			bck	= this.layout.category.bind(this, a.catid);
		
		// Fade in layout
		this.layout._genericLayoutDelayed({
			
			description : d,
			returnPage : ['article', id],
			
			// Toolbar settings
			toolbar : {
				back : bck,
				config : true,
				insert : this.insert.bind(this)
			},
			
			// Header settings
			header :
			{
				// Article title
				a : [this._('ARTICLE'), t]
			},
			
			// Link configuration settings
			config :
			{
				// Default text
				text : t,
				
				// URLs (Itemid)
				url :
				{
					query : {
						a : ['view=article', 'id='+ id],
						b : ['view=category', 'id='+ a.catid],
						c : ['view=section', 'id='+ a.sectionid]
					},
					options : {
						componentid : 20,
						defaultURL : this.tmpl.url('article', slug, cslug)
					}
				},
				
				// Formats
				format : [
					['html', 'HTML', 'selected'],
					['pdf', 'PDF']
				],
				
				// Anchors
				anchor : this.tmpl.htmlAnchors(txt),
				
				// Pagebreaks
				pageBreak : this.tmpl.pageBreaks(txt)
			}
		});
	},
	
	// Uncatgorized layout
	uncategorized : function(dbr)
	{
		// Query database
		if (dbr === true)
		{
			// Build query
			var q	=
			'SELECT id, title, created, modified, ordering'+
			' FROM #__content'+
			' WHERE state = 1 AND sectionid = 0'+
			' AND access <= '+ Linkr.userAccessLevel +
			' ORDER BY ordering ASC, title ASC ';
			
			// Hide options
			if (this.sliders.options) this.sliders.options.slideOut();
			
			// Get results
			return this.tmpl.dbList(q, 0, 0, this.layout.uncategorized.bind(this));
		}
		
		// Display results
		Linkr.delayDisplay('results', 70, this.listResults.bind(this, [dbr, 'article', this._('UNCATEGORIZED')]));
	}
};

// Template methods
var ArtTmpl	=
{
	// Returns default URL
	url : function(t, id, cid)
	{
		// Base URL
		var u	= 'index.php?option=com_content';
		
		switch (t)
		{
			case 'section':
			case 'category':
				u	+= '&view='+ t +'&id='+ id;
				break;
			
			case 'article':
				if (cid && cid != 'null')
					u += '&view=article&catid='+ cid +'&id='+ id;
				else
					u += '&view=article&id='+ id;
				break;
		}
		
		return u;
	}
};


