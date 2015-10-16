/*
 * Linkr - Files Plugin
 *
 */

var LinkrFile	=
{
	/*
	 * Landing page
	 */
	landing : function() {
		Linkr.delayIdleDiv('layout', this.display.bind(this));
	},
	
	/*
	 * Displays the layout
	 */
	display : function()
	{
		// Set Linkr return page
		Linkr.setReturnPage(['LinkrFile', 'display']);
		
		// Layout
		var html	= [];
		
		// Messages
		if ($type(this.msg) == 'string')
		{
			var msg	= Linkr.UTF8.Decode(this.msg);
			var md	= new Element('div');
			Linkr.insertMsgDiv(msg, md);
			this.msg	= false;
			html.include(md);
		}
		
		// Links
		var ls	= [];
		ls.include(['', this._('GET_LINK'), [this, 'insert']]);
		ls.include(['', this._('CANCEL'), [this, 'closeConfig']]);
		var tb	= Linkr.htmlTBLinks(ls);
		
		// Link configuration
		var cfg	= new Element('div', {id : 'settings'});
		new Element('div', {id : 'config'}).injectInside(cfg);
		tb.injectTop(cfg);
		html.include(cfg);
		
		// Files
		var fs	= new Element('div', {id : 'filedirlist'});
		html.include(Linkr.idleDiv(fs, true));
		
		// Resize
		var res	= new Element('div', {id : 'fileresize'});
		new Element('img', {
			src : Linkr.siteRoot +'components/com_linkr/assets/img/arrow.down.png',
			alt : '(drag to resize)'
		}).inject(res);
		new Element('img', {
			src : Linkr.siteRoot +'components/com_linkr/assets/img/arrow.up.png',
			alt : '(drag up)'
		}).inject(res);
		html.include(res);
		
		// Directories
		var dirs	= new Element('fieldset', {id : 'dirlist'});
		html.include(Linkr.idleDiv(dirs, false));
		
		// Upload form
		if (this.uploadURL)
		{
			var frm	= new Element('form', {
				id : 'uploadForm',
				name : 'uploadForm',
				method : 'post',
				enctype : 'multipart/form-data',
				action : this.uploadURL,
				events : {submit : this.uli.bind(this)}
			});
			html.include(Linkr.idleDiv(frm, false));
		}
		
		// Load layout
		Linkr.htmlLayout(this._('LINK_FILES'), html);
		
		// Link config slider
		this.fileConfig	= new Fx.Slide('settings', {duration : 300}).hide();
		
		// Load data
		this.setDirectoryList();
		if (this.uploadURL) {
			this.loadUploadForm();
		}
	},
	
	/*
	 * Loads directories
	 */
	setDirectoryList : function(i)
	{
		if (i)
		{
			// Check for errors
			if ($type(i) != 'object')
				return $('dirlist').setHTML('An error occured');
			if (Linkr.isError(i))
				return $('dirlist').setHTML('Error: '+ i.msg);
			if ($type(i.folders) != 'array')
				return $('dirlist').setHTML('An error occured');
			
			// Save data
			this.directoryList	= i.folders;
			this.baseDirectory	= i.info['base.64'];
			this.currentPath	= i.info['path.64'];
		}
		
		// Get file directories
		else if ($type(this.directoryList) === false)
		{
			// Get request URL
			var u	= Linkr.createRequest('dirs');
			
			// Set defaults
			this.setImageDefaults();
			
			return Linkr.json(u, this.setDirectoryList.bind(this));
		}
		
		// Prepare list
		var div	= new Element('div', {
			styles : {'float' : 'left'}
		}).injectInside($('dirlist').setHTML(''));
		new Element('span').setHTML(this._('DIRECTORY') +' ').injectInside(div);
		
		var li	= new Element('select', {
			id : 'folder',
			events : {
				change : function(e) {
					new Event(e).stop();
					LinkrFile.uFiles(this.getValue());
				}
			}
		}).injectInside(div);
		
		// Load list
		this.directoryList.each(function(f, i) {
			new Element('option', {
				id : f.value,
				value : f.value
			}).setHTML(unescape(f.text)).injectInside(li);
		});
		
		// Load files
		this.uFiles();
	},
	
	/*
	 * Set default image attribute values
	 */
	setImageDefaults : function()
	{
		var html	= Linkr.getSelectedText(true);
		if (!html.contains('<img') || !html.contains('/>'))
			return;
		
		// Image attributes
		this.img	= {};
		
		// Width
		var w	= html.match(/width="(\d+?)"/i);
		if (w) this.img.width	= w[1].toInt();
		
		// Height
		var h	= html.match(/height="(\d+?)"/i);
		if (h) this.img.height	= h[1].toInt();
		
		// Align
		var l	= html.match(/align="([a-z]+)"/i);
		if (l) this.img.align	= l[1];
		
		// Title
		var t	= html.match(/title="([^"]+)"/i);
		if (t) this.img.title	= t[1];
		
		// Alternate text
		var a	= html.match(/alt="([^"]+)"/i);
		if (a) this.img.alt	= a[1];
		
		// CSS class
		var c	= html.match(/class="([^"]+)"/i);
		if (c) this.img.className	= c[1];
	},
	
	/*
	 * Loads upload form
	 */
	loadUploadForm : function()
	{
		var frm	= $('uploadForm');
		if ($type(frm) != 'element' || !this.uploadURL.contains(Linkr.siteRoot))
			return this.setMsg('Upload form error...');
		
		// IE fix
		if (window.ie)
		{
			var ieForm	= new Element('div').setHTML(
				'<form id="uploadForm" '+
					'name="uploadForm" '+
					'method="POST" '+
					'enctype="multipart/form-data" '+
					'action="'+ this.uploadURL +'" '+
					'onsubmit="LinkrFile.uli();">'+
					
					'<fieldset style="padding-left:10px">'+
						'<legend>'+ this._('UPLOAD') +'</legend>'+
						'<input type="file" '+
							'id="file-upload" '+
							'name="Filedata" />'+
						'<input type="submit" '+
							'id="file-upload-submit" '+
							'value="'+ this._('UPLOAD') +'" '+
							'styles="margin:0 2px;" />'+
					'</fieldset>'+
				'</form>'
			);
			return frm.replaceWith(ieForm);
		}
		
		// Load form elements
		frm.empty();
		var fs	= new Element('fieldset', {
			styles : {'padding-left' : 10}
		}).injectInside(frm);
		new Element('legend').setHTML(this._('UPLOAD')).injectInside(fs);
		new Element('input', {
			id : 'file-upload',
			type : 'file',
			name : 'Filedata'
		}).injectInside(fs);
		new Element('input', {
			id : 'file-upload-submit',
			type : 'submit',
			value : this._('UPLOAD'),
			styles : {margin : '0 2px'}
		}).injectInside(fs);
	},
	
	/*
	 * Displays loading icon
	 */
	uli : function(e) {
		Linkr.idleDiv('filedirlist', true);
		$('folder').setProperty('disabled', true);
		$('dirlist').setStyle('background-color', '#ddd');
	},
	
	/*
	 * Loads file data
	 */
	uFiles : function(d) {
		Linkr.delayIdleDiv('filedirlist', true, 70);
		this._ufsc.delay(100, this, d);
	},
	_ufsc : function(d) {
		var u	= Linkr.createRequest('files') +'&f='+ (d || this.currentPath);
		Linkr.json(u, this._files.bind(this));
	},
	_files : function(i)
	{
		// Check for errors
		var div	= $('filedirlist');
		if ($type(i) != 'object')
			return div.setHTML('An error occured').setStyle('opacity', 1.0);
		if (Linkr.isError(i))
			return div.setHTML('Error: '+ i.msg).setStyle('opacity', 1.0);
		
		// Display list function
		switch (i.mode)
		{
			case 'simple':
				var func	= '_simpleFileList';
				break;
			
			default:
				var func	= '_normalFileList';
		}
		
		// Display list
		Linkr.delayDisplay(div, 70, [this, func], [div, i]);
		
		// Update information
		$(i.current).selected	= true;
		this.currentPath	= i.current;
		
		// Make files window resizable
		$('filedirlist').makeResizable({
			handle : 'fileresize',
			modifiers: {x: false, y: 'height'},
			limit: {y: [50, 250]}
		});
	},
	_simpleFileList : function(div, i)
	{
		// Empty contents of file list div
		div.setHTML('');
		new Element('div', {styles : {clear : 'both'}}).injectInside(div);
		
		var _d	= new Element('div', {'class' : 's-item'});
		var _i	= new Element('img', {width : 12, height : 12});
		var _n	= new Element('span', {styles : {margin : '1px 5px'}});
		
		// Parent directory
		if (i.parent)
		{
			var item	= _d.clone().injectInside(div);
			var icon	= _i.clone().injectInside(item);
			var name	= _n.clone().setHTML(this._('UP')).injectInside(item);
			
			item.set({
				events : {
					click : function(e) {
						new Event(e).stop();
						LinkrFile.uFiles(i.parent);
					}
				}
			});
			icon.setProperty('src', Linkr.siteRoot +'administrator/components/com_media/images/folderup_16.png');
		}
		
		// Folders
		if (i.folders.length > 0)
		{
			i.folders.each(function(f)
			{
				var item	= _d.clone().injectInside(div);
				var icon	= _i.clone().injectInside(item);
				var name	= _n.clone().setHTML(f.name).injectInside(item);
				
				item.set({
					title : f.name,
					events : {
						click : function(e) {
							new Event(e).stop();
							LinkrFile.uFiles(f['path.64']);
						}
					}
				});
				icon.setProperty('src', Linkr.siteRoot +'administrator/components/com_media/images/folder_sm.png');
			});
		}
		
		// Files
		if (i.files.length > 0)
		{
			var _l	= new Element('a', {href : 'javascript:void(0);'});
			var ix	= ['bmp', 'gif', 'jpg', 'jpeg', 'odg', 'png', 'xcf'];
			var ig	= {width : 12, height : 12, 'class' : 'link'};
			var igi	= {alt : 'i', title : this._('GET_IMAGE_LINK'), src : Linkr.siteRoot +'components/com_linkr/assets/img/files.link.img.png'};
			
			i.files.each(function(f)
			{
				var li	= false;
				var src	= f.src.replace(Linkr.siteRoot, '');
				var ext	= f.name.substr(f.name.lastIndexOf('.') + 1).toLowerCase();
				
				var item	= _d.clone().injectInside(div);
				var icon	= _i.clone().setProperty('src').injectInside(item);
				var name	= _n.clone().setHTML(f.name).injectInside(item);
				
				if (ix.contains(ext)) {
					li	= _l.clone().setHTML('('+ this._('GET_IMAGE_LINK') +')').injectInside(item);
				}
				
				icon.setProperty('src', Linkr.siteRoot + this.getIcon(ext, true));
				name.set({
					title : this._('GET_LINK'),
					events : {
						click : function(e) {
							new Event(e).stop();
							LinkrFile.link('text', src, ext);
						}
					}
				});
				if (li !== false) {
					li.set({
						events : {
							click : function(e) {
								new Event(e).stop();
								LinkrFile.link('image', src);
							}
						}
					});
				}
			}, this);
		}
	},
	_normalFileList : function(div, i)
	{
		// Empty contents of file list div
		div.setHTML('');
		new Element('div', {styles : {clear : 'both'}}).injectInside(div);
		
		var _a	= new Element('div', {'class' : 'item'});
		var _b	= new Element('div', {'class' : 'icon linkr-tip'});
		var _c	= new Element('div', {'class' : 'border'});
		var _d	= new Element('a', {href : 'javascript:void(0);'});
		var _e	= new Element('img', {width : 32, height : 32});
		var _f	= new Element('div', {'class' : 'name'});
		
		// Parent directory
		if (i.parent)
		{
			var item	= _a.clone().addClass('up').injectInside(div);
			var icon	= _b.clone().injectInside(item);
			var border	= _c.clone().injectInside(icon);
			var link	= _d.clone().injectInside(border);
			var img		= _e.clone().injectInside(link);
			var name	= _f.clone().setHTML(this._('UP')).injectInside(item);
			
			icon.set({
				title : this._('UP'),
				events : {
					click : function(e) {
						new Event(e).stop();
						LinkrFile.uFiles(i.parent);
					}
				}
			});
			img.setProperty('src', Linkr.siteRoot +'administrator/components/com_media/images/folderup_32.png');
		}
		
		// Folders
		if (i.folders.length > 0)
		{
			var folder	= _a.clone().addClass('folder');
			var fimg	= _e.clone().setProperty('src', Linkr.siteRoot +'administrator/components/com_media/images/folder.png');
			i.folders.each(function(f)
			{
				var item	= folder.clone().injectInside(div);
				var icon	= _b.clone().injectInside(item);
				var border	= _c.clone().injectInside(icon);
				var link	= _d.clone().injectInside(border);
				var name	= f.name.length > 9 ? f.name.substr(0, 9) +'...' : f.name;
				fimg.clone().injectInside(link);
				_f.clone().setHTML(name).injectInside(item);
				
				icon.set({
					title : f.name,
					events : {
						click : function(e) {
							new Event(e).stop();
							LinkrFile.uFiles(f['path.64']);
						}
					}
				});
			});
		}
		
		// Files
		if (i.files.length > 0)
		{
			var file	= _a.clone().addClass('file');
			var ig	= {width : 16, height : 16, 'class' : 'link'};
			var igt	= {alt : 't', title : this._('GET_LINK'), src : Linkr.siteRoot +'components/com_linkr/assets/img/files.link.png'};
			var igi	= {alt : 'i', title : this._('GET_IMAGE_LINK'), src : Linkr.siteRoot +'components/com_linkr/assets/img/files.link.img.png'};
			
			i.files.each(function(f)
			{
				var src	= f.src.replace(Linkr.siteRoot, '');
				var item	= file.clone().injectInside(div);
				var icon	= _b.clone().injectInside(item);
				var border	= _c.clone().injectInside(icon);
				var img		= _e.clone().injectInside(border);
				var name	= _f.clone().injectInside(item);
				var ltxt	= _d.clone().injectInside(name);
				new Element('img', ig).setProperties(igt).injectInside(ltxt);
				if (f.type == 'Image') {
					var limg	= _d.clone().injectInside(name);
					new Element('img', ig).setProperties(igi).injectInside(limg);
				}
				
				icon.setProperty('title', f.name +' ('+ f.size +')');
				img.setProperties({width : f.width, height : f.height, src : f.icon});
				ltxt.set({
					events : {
						click : function(e) {
							new Event(e).stop();
							LinkrFile.link('text', src, f.type);
						}
					}
				});
				
				if (f.type == 'Image')
				{
					limg.set({
						events : {
							click : function(e) {
								new Event(e).stop();
								LinkrFile.link('image', src);
							}
						}
					});
				}
			});
		}
		
		// Add tooltips
		Linkr.htmlToolTips($$('.icon'));
	},
	
	_ : function(t, a) {
		return Linkr.getL18N(t, a);
	},
	
	/*
	 * Links a file
	 */
	link : function(t, src, ft)
	{
		switch (t)
		{
			case 'image':
				var cfg	= this._loadImageConfig(src);
				break;
			
			default:
				var cfg	= this._loadTextConfig(src, ft);
		}
		
		this.fileConfig.slideIn();
	},
	
	/*
	 * HTML config shortcuts
	 */
	_loadTextConfig : function(src, ft) 
	{
		var cfg	= $('config').empty();
		
		//Title
		this._getTitle(this._('LC_ATTRIBUTES')).injectInside(cfg);
		
		// Link text
		this._getLabel('linkText', this._('LC_TEXT')).injectInside(cfg);
		this._getInput('linkText', Linkr.getDefaultText(src)).injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link target
		this._getLabel('target', this._('LC_TARGET')).injectInside(cfg);
		Linkr.htmlSelectCustom([
			['_self', this._('LC_TARGET_SELF'), 'selected'],
			['_blank', this._('LC_TARGET_BLANK')]
		], {id : 'target'}).injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link icon
		this._getLabel('imgIcon', this._('ADD_TYPE_ICON')).injectInside(cfg);
		Linkr.htmlSelectCustom([
			['no', this._('NO'), 'selected'],
			['left', this._('TEXT_LEFT')],
			['right', this._('TEXT_RIGHT')]
		], {id : 'imgIcon'}).injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link title
		this._getLabel('linkTitle', this._('LC_TITLE')).injectInside(cfg);
		this._getInput('linkTitle', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link class
		this._getLabel('linkClass', this._('LC_CLASS')).injectInside(cfg);
		this._getInput('linkClass', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link relation
		this._getLabel('linkRelation', this._('LC_RELATION')).injectInside(cfg);
		this._getInput('linkRelation', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Info
		this._getInput('source', src, true).injectInside(cfg);
		this._getInput('filetype', ft.toLowerCase(), true).injectInside(cfg);
		this._getInput('type', 'text', true).injectInside(cfg);
	},
	_loadImageConfig : function(src)
	{
		var cfg	= $('config').empty();
		
		// Image attributes
		this._getTitle(this._('IMAGE')).injectInside(cfg);
		
		// Image align
		this._getLabel('imgAlign', this._('ALIGN')).injectInside(cfg);
		Linkr.htmlSelectCustom([
			['none', this._('NONE'), 'selected'],
			['top', this._('TOP')],
			['bottom', this._('BOTTOM')],
			['middle', this._('MIDDLE')],
			['left', this._('LEFT')],
			['right', this._('RIGHT')]
		], {id : 'imgAlign'}).injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Image size
		var sp	= {margin : '0 10px 0 0', width : 70};
		this._getLabel('imgSizeW', this._('LC_IMG_SIZE')).injectInside(cfg);
		this._getInput('imgSizeW', '').setStyles(sp).injectInside(cfg);
		this._getInput('imgSizeH', '').setStyles(sp).injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Image title
		this._getLabel('imgTitle', this._('LC_TITLE')).injectInside(cfg);
		this._getInput('imgTitle', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Image description
		this._getLabel('imgDesc', this._('DESCRIPTION')).injectInside(cfg);
		this._getInput('imgDesc', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Image class
		this._getLabel('imgClass', this._('LC_CLASS')).injectInside(cfg);
		this._getInput('imgClass', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Fill in image attributes
		if ($type(this.img) == 'object')
		{
			if ($type(this.img.align) == 'string' && $type($(this.img.align)) == 'element')
				$(this.img.align).selected	= true;
			if ($type(this.img.width) == 'number' && this.img.width > 0)
				$('imgSizeW').value	= this.img.width;
			if ($type(this.img.height) == 'number' && this.img.height > 0)
				$('imgSizeH').value	= this.img.height;
			if ($type(this.img.title) == 'string')
				$('imgTitle').value	= this.img.title;
			if ($type(this.img.alt) == 'string')
				$('imgDesc').value	= this.img.alt;
			if ($type(this.img.className) == 'string')
				$('imgClass').value	= this.img.className;
		}
		
		// Link attribtues
		this._getTitle(this._('LC_ATTRIBUTES')).injectInside(cfg);
		
		// Link text
		this._getLabel('linkText', this._('LC_TEXT')).injectInside(cfg);
		this._getInput('linkText', Linkr.imgAnchor).setProperty('disabled', true).injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link target
		this._getLabel('target', this._('LC_TARGET')).injectInside(cfg);
		Linkr.htmlSelectCustom([
			['_self', this._('LC_TARGET_SELF'), 'selected'],
			['_blank', this._('LC_TARGET_BLANK')]
		], {id : 'target'}).injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link title
		this._getLabel('linkTitle', this._('LC_TITLE')).injectInside(cfg);
		this._getInput('linkTitle', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link class
		this._getLabel('linkClass', this._('LC_CLASS')).injectInside(cfg);
		this._getInput('linkClass', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Link relation
		this._getLabel('linkRelation', this._('LC_RELATION')).injectInside(cfg);
		this._getInput('linkRelation', '').injectInside(cfg);
		new Element('div', {styles : {clear : 'both'}}).injectInside(cfg);
		
		// Info
		this._getInput('source', src, true).injectInside(cfg);
		this._getInput('type', 'image', true).injectInside(cfg);
	},
	_getLabel : function(id, t) {
		return new Element('label', {'for' : id}).setHTML(t);
	},
	_getInput : function(a, b, c) {
		return new Element('input', {
			id : a,
			name : a,
			value : b,
			'class':'inputbox value',
			type : (c ? 'hidden' : 'text')
		});
	},
	_getTitle : function(t) {
		return new Element('div', {
			styles : {
				margin : '5px 0',
				'text-align' : 'center',
				'font-weight' : 'bold'
			}
		}).setHTML(t);
	},
	
	/*
	 * Inserts link
	 */
	insert : function() {
		if ($('type').value == 'image') this._insertImage();
		else this._insertText();
	},
	_insertText : function()
	{
		// Check text
		var txt	= $('linkText').getValue();
		if (txt.trim().length == 0) {
			alert(Linkr.missingText);
		}
		
		// Get URL
		else
		{
			var ui	= $('imgIcon').getValue();
			if (ui == 'left' || ui == 'right')
			{
				var i	= this.getIcon($('filetype').value);
				if (ui == 'left') {
					txt	= i +' '+ txt;
				} else {
					txt	+= ' '+ i;
				}
			}
			
			Linkr.link($('source').value, txt);
		}
	},
	_insertImage : function()
	{
		// Image
		var img	= '<img src="'+ $('source').value +'" border="0"';
		
		// Description
		var d	= $('imgDesc').value;
		if (d.length > 0)
			img	+= ' alt="'+ d +'"';
		
		// Title
		var t	= $('imgTitle').value;
		if (t.length > 0)
			img	+= ' title="'+ t +'"';
		
		// Align
		var l	= $('imgAlign').getValue();
		if (l != 'none')
			img	+= ' align="'+ l +'"';
		
		// Class
		var c	= $('imgClass').value;
		if (c.length > 0)
			img	+= ' class="'+ c +'"';
		
		// Width
		var w	= $('imgSizeW').value.toInt();
		if (w > 0)
			img	+= ' width="'+ w +'"';
		
		// Height
		var h	= $('imgSizeH').value.toInt();
		if (h > 0)
			img	+= ' height="'+ h +'"';
		
		// Insert link
		Linkr.link($('source').value, img += ' />');
	},
	
	/*
	 * Filetype icons
	 */
	getIcon : function(ty, src)
	{
		switch (ty)
		{
			// Documents
			case 'doc':
			case 'docx':
			case 'odt':
			case 'rtf':
			case 'txt':
			case 'wps':
			case 'wpt':
			case 'txt':
				i	= 'components/com_linkr/assets/img/files.icon.doc.png';
				break;
			
			case 'pdf':
				i	= 'images/M_images/pdf_button.png';
				break;
			
			// Spreadsheets, tables
			case 'csv':
			case 'ods':
			case 'odb':
			case 'wks':
			case 'xlr':
			case 'xls':
			case 'xlsx':
			case 'xlt':
			case 'xltx':
				i	= 'components/com_linkr/assets/img/files.icon.table.png';
				break;
			
			// Media
			case 'aiff':
			case 'asf':
			case 'avi':
			case 'm3u':
			case 'mid':
			case 'mov':
			case 'mp3':
			case 'mp4':
			case 'mpg':
			case 'mpeg':
			case 'odg':
			case 'odp':
			case 'ogg':
			case 'ppt':
			case 'qt':
			case 'rm':
			case 'vqa':
			case 'wav':
				i	= 'components/com_linkr/assets/img/files.icon.media.png';
				break;
			
			// Image
			case 'bmp':
			case 'gif':
			case 'jpg':
			case 'jpeg':
			case 'odg':
			case 'png':
			case 'xcf':
			case 'image':
				i	= 'components/com_linkr/assets/img/files.icon.image.png';
				break;
			
			case 'swf':
				i	= 'components/com_linkr/assets/img/files.icon.swf.png';
				break;
			
			// Archives
			case 'jar':
			case 'rar':
			case 'tar':
			case 'zip':
			case 'gz':
			case 'bz2':
				i	= 'components/com_linkr/assets/img/files.icon.archive.png';
				break;
			
			case 'css':
			case 'js':
			case 'xml':
				i	= 'components/com_linkr/assets/img/files.icon.script.png';
				break;
			
			default:
				i	= 'components/com_linkr/assets/img/files.icon.png';
		}
		
		return src ? i : '<img src="'+ i +'" border="0" />';
	},
	
	/*
	 * Closes link configuration box
	 */
	closeConfig : function() {
		this.fileConfig.slideOut();
	},
	
	/*
	 * Debugging
	 */
	dump : function(a) {
		var dump	= Linkr.dump(a, false, true);
		dump		= dump.replace(/\n/g, '<br/>');
		dump		= dump.replace(/\s/g, '&nbsp;');
		var debug	= new Element('div').setHTML(dump);
		Linkr.htmlLayout(this._('FILES'), debug);
		return false;
	}
};

