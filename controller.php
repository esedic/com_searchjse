<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_searchjse
 */

defined('_JEXEC') or die;

class SearchjseController extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = array())
	{
		$cachable = true;

		/*$vLayout = $this->input->get('layout', '');
		
		if (!file_exists(JPATH_COMPONENT.'/views/search/tmpl/') && $vLayout !='default') {
			$vLayout = 'default_part';
		}
		
		echo $vLayout;
		$this->input->set('layout', $vLayout);
		*/
		
		parent::display($cachable, $urlparams);

		return $this;
	}
}