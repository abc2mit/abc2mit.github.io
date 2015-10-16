/*
 * Linkr - Debug Plugin
 */
var LinkrDebug	=
{
	load : function(p)
	{
		if (typeof MooTools != 'object') return alert('MooTools is not loaded!');
		if (MooTools.version >= 1.2) return alert('You\'re running MooTools 1.2!');
		if (typeof Linkr != 'object') return alert('There\'s an error in the Linkr script!');
		if (!$defined(this.s)) this._s();
		p	= typeof p == 'string' ? p : 'home';
		Linkr.delayIdleDiv('layout', this[p].bind(this));
	},
	_s : function()
	{
		this.s	= {img : Linkr.siteRoot +'components/com_linkr/assets/img/'};
		Linkr.onJsonFailure	= this.jsf.bind(this);
	},
	jsf : function(r) {
		alert(r.status +':'+ responseText);
	},
	
	home : function()
	{
		var h	= this._('DEBUG_ABOUT') +'<br />'
					+ this._('PLEASE SELECT A CATEGORY.') +'<br /><br />'
					+ this._plk('Database Tests', 'db')
					+ this._plk('Miscellaneous Tests', 'm');
		Linkr.htmlLayout('Linkr Debug', this.hd(h));
	},
	_plk : function(t, c) {
		return '<div style="margin:5px;"><a href="javascript:LinkrDebug.load(\''+ c +'\')">'+ t +'</a> <img src="'+ this.s.img +'arrow.right.sel.png" alt="&#187;" /></div>';
	},
	
	// Renders test page
	_dp : function(c, b)
	{
		var d	= new Element('div', {
			styles : {
				margin : 5,
				padding : 5,
				'text-align' : 'right'
			}
		});
		new Element('img', {
			src : this.s.img +'arrow.left.sel.png',
			styles : {margin : '0 2px'}
		}).inject(d);
		b	= typeof b == 'string' ? b : 'LinkrDebug.load()';
		new Element('a', {
			href : 'javascript:'+ b
		}).setHTML('back').inject(d);
		c.include(d);
		Linkr.htmlLayout('Linkr Debug', c);
	},
	
	// Database page
	db : function()
	{
		var c	= [];
		c.include(this.hd('Database Test'));
		c.include(this.td('dbl', 'Database list'));
		c.include(this.td('dbo', 'Single database object'));
		c.include(this.td('dboid', 'Fetch article with this ID:', '99'));
		c.include(this.td('dbids', 'Itemid fetching'));
		c.include(this.td('dbe', 'Generate a database error'));
		
		this._dp(c);
	},
	dbe : function(r)
	{
		if (r !== 1) return this.udb('dbe', r);
		
		this.itd('dbe');
		Linkr.dbObject('SELECT * FROM ?', this.dbe.bind(this));
	},
	dbl : function(r)
	{
		if (r !== 1) return this.udb('dbl', r);
		
		this.itd('dbl');
		Linkr.dbList('SELECT id FROM #__content', 0, 1, this.dbl.bind(this));
	},
	dbo : function(r)
	{
		if (r !== 1) return this.udb('dbo', r);
		
		this.itd('dbo');
		Linkr.dbObject('SELECT * FROM #__components WHERE id = 20', this.dbo.bind(this));
	},
	dboid : function(r)
	{
		if (r !== 1) return this.udb('dboid', r);
		
		this.itd('dboid');
		var id	= $('dboid-i').getValue().replace(/[^0-9]/g, '');
		if (id.length < 1) id = 1;
		$('dboid-i').value	= id;
		Linkr.dbObject('SELECT * FROM #__content WHERE id = '+ id, this.dboid.bind(this));
	},
	dbids : function(r)
	{
		if (r !== 1) return this.udb('dbids', r);
		
		this.itd('dbids');
		Linkr.getItemids(0, {parent : 0}, this.dbids.bind(this));
	},
	
	// Misc page
	m : function()
	{
		var c	= [];
		
		c.include(this.hd('Files, Folders, Other'));
		c.include(this.td('mjs', 'AJAX Test'));
		c.include(this.td('md', 'Directory list'));
		c.include(this.td('mf', 'Get files in this directory:', 'images/stories'));
		
		this._dp(c);
	},
	md : function(r)
	{
		if (r === 1)
		{
			this.itd('md');
			new Json.Remote(Linkr.createRequest('dirs'), {
				onComplete : this.md.bind(this)
			}).send();
			return;
		}
		
		var s	= $('md-s');
		var m	= $('md-m');
		
		if (typeof r != 'object') {
			s.setProperty('src', this.s.img +'disabled.png');
			m.setStyle('background-color', '#990000');
			return m.setHTML('Could not receive results');
		}
		
		if (Linkr.isError(r)) {
			s.setProperty('src', this.s.img +'disabled.png');
			m.setStyle('background-color', '#990000');
			return m.setHTML('Error: '+ r.msg);
		}
		
		if (typeof r.folders != 'object') {
			s.setProperty('src', this.s.img +'disabled.png');
			m.setStyle('background-color', '#990000');
			return m.setHTML('Could not retrieve directory list');
		}
		
		if (r.folders.length < 1) {
			s.setProperty('src', this.s.img +'tick.png');
			m.setStyle('background-color', '#697c9d');
			return m.setHTML('Received empty list!');
		}
		
		s.setProperty('src', this.s.img +'tick.png');
	},
	mf : function(r)
	{
		if (r !== 1) return this.udb('mf', r);
		
		this.itd('mf');
		var f	= $('mf-i').getValue().replace(/[^0-9a-z_\.\-\/]/ig, '');
		if (f.length < 1) f = 'images/stories';
		$('mf-i').value	= f;
		Linkr.listFiles(f, 'all', this.mf.bind(this));
	},
	mjs : function(r)
	{
		if (r !== 1) return this.udb('mjs', r);
		
		this.itd('mjs');
		Linkr.json(Linkr.createRequest('mjs'), this.mjs.bind(this));
	},
	
	// Info DIV
	hd : function(txt)
	{
		return new Element('div', {
			styles : {
				margin : 5,
				padding : 5,
				'background-color' : '#eee'
			}
		}).setHTML(txt);
	},
	
	// Test DIV
	td : function(id, txt, x)
	{
		var div	= new Element('div', {
			styles : {
				margin : 5,
				'border-bottom' : '1px dotted #aaa'
			}
		});
		
		var lnk	= new Element('div', {
			styles : {
				'float' : 'left',
				padding : '0 10px',
				width : 350,
				'text-align' : 'right'
			}
		}).inject(div);
		
		var img	= new Element('div', {
			styles : {
				'float' : 'left', width : 200
			}
		}).inject(div);
		new Element('img', {
			id : id +'-s',
			src : this.s.img +'blank.png'
		}).inject(img);
		
		new Element('div', {
			id : id +'-m',
			styles : {
				'float' : 'none',
				padding : 5,
				clear : 'both',
				width : 560,
				color : '#fff'
			}
		}).inject(div);
		
		new Element('a', {
			href : 'javascript:LinkrDebug.'+ id +'(1)'
		}).setHTML(txt).inject(lnk);
		
		if (x)
		{
			new Element('input', {
				id : id +'-i',
				type : 'text',
				value : x,
				styles : {
					margin : '0 0 0 5px'
				}
			}).inject(lnk);
		}
		
		return div;
	},
	
	// Idle DIV
	itd : function (id) {
		$(id +'-s').setProperty('src', this.s.img +'loading.gif');
		$(id +'-m').setHTML('').setStyle('background-color', 'transparent');
	},
	
	// Updates test DIV with result
	udb : function(id, r)
	{
		var s	= $(id +'-s');
		var m	= $(id +'-m');
		
		if (!Linkr.isDBRO(r)) {
			s.setProperty('src', this.s.img +'disabled.png');
			m.setStyle('background-color', '#990000');
			return m.setHTML('Could not receive results');
		}
		
		if (Linkr.isError(r)) {
			s.setProperty('src', this.s.img +'disabled.png');
			m.setStyle('background-color', '#990000');
			return m.setHTML('Error: '+ r.msg);
		}
		
		if (!r.result || r.result.length < 1) {
			s.setProperty('src', this.s.img +'tick.png');
			m.setStyle('background-color', '#697c9d');
			return m.setHTML('Received an empty object (no results)');
		}
		
		s.setProperty('src', this.s.img +'tick.png');
	},
	
	_ : function(t, a) {
		return Linkr.getL18N(t, a);
	}
};
