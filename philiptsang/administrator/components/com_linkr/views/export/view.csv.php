<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrViewExport extends JView
{
	function display($tpl = null)
	{
		if (!$csv = $this->get('CSV')) {
			JError::raiseError(500, 'Could not create bookmarks file');
			return;
		}
		
		$name	= 'LinkrBookmarks-'. date('Y-m-d');
		$doc	= & JFactory::getDocument();
		$doc->setMimeEncoding('application/csv');
		JResponse::setHeader('Content-Disposition', 'inline; filename="'. $name .'.csv"');
		echo $csv;
	}
}
