<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrViewMm extends LinkrView
{
	function display($tpl = null)
	{
		// Assign references
		$this->assignRef('form', $this->get('FormData'));
		
		// Set vars
		JRequest::setVar('tmpl', 'component');
		
		// Load styles
		$this->loadScripts();
		
		parent::display($tpl);
	}
	
	function loadScripts()
	{
		$d	= & JFactory::getDocument();
		
		// CSS styles
		$d->addStyleDeclaration(
			'#fileList{'.
				'width:670px;'.
				'height:220px;'.
				'overflow:auto;'.
				'clear:both;'.
			'}'.
			'#fileList div.item{'.
				'float:left;'.
				'width:70px;'.
				'border:1px solid #fff;'.
				'background-color:#fff;'.
				'text-align:center;'.
			'}'.
			'#fileList div.item div.name{'.
				'height:15px;'.
			'}'.
			'#fileList div.item div.icon div.border{'.
				'margin-top:10px;'.
				'height:45px;'.
				'width:68px;'.
				'vertical-align:middle;'.
				'overflow:hidden;'.
			'}'.
			'#fileList div.item div.icon div.border a{'.
				'height:45px;'.
				'width:68px;'.
				'display:block;'.
			'}'.
			'#fileList div.item:hover{'.
				'border:1px solid #0B55C4;'.
				'background-color:#d2d7e0;'.
				'cursor:pointer;'.
			'}'
		);
		
		// Javascript
		if (LinkrHelper::getMediaParam('enable_flash', 1)) {
			JHTML::_('behavior.uploader', 'file-upload', array(
				'targetURL'		=> $this->form->uploadURL,
				'types'			=> '{\'Pictures (*.bmp, *.gif, *.jpg, *.jpeg, *.png)\':\'*.bmp;*.gif;*.jpg;*.jpeg;*.png\'}',
				'onAllComplete'	=> 'function(){ window.location.reload(true); }'
			));
		}
		$d->addScriptDeclaration(
			'var selectIcon	= function(u, f)'.
			'{'.
				'if (document.uploadForm.deli.checked == true) {'.
					'if (confirm("'. JText::_('VALIDDELETEITEMS', true) .'")) {'.
						'window.location.href = "'. $this->form->deleteURL .'"+ f;'.
					'}'.
				'} else {'.
					'window.parent.document.adminForm.icon.value=u;'.
					'window.parent.document.getElementById("sbox-window").close();'.
				'}'.
			'}'
		);
	}
}
