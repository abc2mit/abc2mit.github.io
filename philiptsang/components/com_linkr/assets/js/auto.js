/*
 * Linkr - Automatic Links Plugin
 *
 */

/*
 * Object settings
 */
var _xo	=
{
	// Plugin title
	title : 'Extensions',
	
	// Object name
	objName : 'LinkrX',
	
	// Initialize
	initialize : function()
	{
		// Plugin title
		this.options.title	= this._('EXTENSIONS');
	}
};

/*
 * Tree elements
 */
var _xr	= [];

/*
 * SQL queries
 */
var _xs	=
{
	sql : {}
};

/*
 * Object methods
 */
var _xt	=
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
			var t	= $pick(i.name, '??');
			t		= Linkr.urldecode(Linkr.UTF8.Decode(t));
			
			// Description
			var d	= '';
			
			// Link
			var u	= $pick(i.link, false);
			if (u && i.type != 'url') u += '&Itemid='+ i.id;
			
			// Add item
			is[i.id]	= {
				title : t,
				description : false,
				url : false,
				level : l,
				children : false
			};
		});
		
		// Return tree items
		return is;
	}
};


