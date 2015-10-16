<?php
defined('_JEXEC') or die;

class LinkrControllerExport extends JController
{
	function LinkrControllerExport() {
		parent::__construct();
	}
	
	function import()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$source	= JRequest::getWord('source', 'file');
		$model	= & $this->getModel('export');
		if ($new = $model->import($source)) {
			$msg = JText::sprintf('X_BMS', $new);
		} else {
			$msg = $model->getError();
		}
		
		$this->setRedirect(index .'&view=export', $msg);
	}
}
