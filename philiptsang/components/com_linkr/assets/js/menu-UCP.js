/*
 * Linkr - Menu Items Plugin
 *
 */

/*
 * Object settings
 */
var MIOpts	=
{
	// Plugin title
	title : 'Menus',
	
	// Object name
	objName : 'LinkrMenu',
	
	// Initialize
	initialize : function()
	{
		// Plugin title
		this.options.title	= this._('MENUS');
	}
};

/*
 * Tree elements
 */
var MITree	= [];

/*
 * SQL queries
 */
var MISrch	=
{
	sql : {
		type : 'SELECT id, name, link, type, params FROM #__menu WHERE published = 1 AND parent = 0 AND menutype = [query] AND access <= [access]',
		menu : 'SELECT id, name, link, type, params FROM #__menu WHERE published = 1 AND parent = [query] AND access <= [access]'
	}
};

/*
 * Object methods
 */
var MITmpl	=
{
	setupTree : function(r, l)
	{
		// Checks
		if ($type(r) != 'array') return;
		
		// Tree items
		var is	= {};
		ty	= l == 0 ? 'type' : 'menu';
		r.each(function(i)
		{
			// Element title
			var t	= $pick(i.title, $pick(i.name, '??'));
			t		= Linkr.urldecode(Linkr.UTF8.Decode(t));
			
			// Description
			var d	= $pick(i.description, '');
			d		= Linkr.urldecode(Linkr.UTF8.Decode(d));
			
			// Link
			var u	= $pick(i.link, false);
			if (u)
			{
				// Secure link
				if (i.params.contains('secure=1')) u = Linkr.sslSiteRoot + u;
				
				// Add Itemid
				if (i.type != 'url') u += '&Itemid='+ i.id;
			}
			
			// Add item
			is[i.id]	= {
				title : t,
				description : d,
				url : u,
				level : l,
				children : [ty, i.id]
			};
		});
		
		// Return tree items
		return is;
	}
};


