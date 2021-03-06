<?php
/**
 * @version 1.9.6
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Venues View
 *
 * @package JEM
*/
class JEMViewVenues extends JViewLegacy
{
	/**
	 * Creates the Venuesview
	 *
	 */
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$document		= JFactory::getDocument();
		$jemsettings	= JEMHelper::config();
		$settings 		= JEMHelper::globalattribs();
		$user			= JFactory::getUser();
		$print			= JRequest::getBool('print');

		//get menu information
		$menu		= $app->getMenu();
		$menuitem	= $menu->getActive();
		$params 	= $app->getParams();

		// Load css
		JHtml::_('stylesheet', 'com_jem/jem.css', array(), true);
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #jem dd { height: 1%; }</style><![endif]-->');
		if ($print) {
			JHtml::_('stylesheet', 'com_jem/print.css', array(), true);
			$document->setMetaData('robots', 'noindex, nofollow');
		}

		// Request variables
		$task 	= JRequest::getWord('task');
		$rows 	= $this->get('Data');

		$pagetitle = $params->def('page_title', $menuitem->title);
		$pageheading = $params->def('page_heading', $params->get('page_title'));

		//pathway
		$pathway 	= $app->getPathWay();
		if($menuitem) $pathway->setItemName(1, $menuitem->title);

		if ($task == 'archive') {
			$pathway->addItem(JText::_('COM_JEM_ARCHIVE'), JRoute::_('index.php?view=venues&task=archive'));
			$print_link = JRoute::_('index.php?view=venues&task=archive&print=1&tmpl=component');
			$pagetitle   .= ' - '.JText::_('COM_JEM_ARCHIVE');
			$pageheading .= ' - '.JText::_('COM_JEM_ARCHIVE');
			$params->set('page_heading', $pageheading);
		} else {
			$print_link = JRoute::_('index.php?view=venues&print=1&tmpl=component');
		}

		// Add site name to title if param is set
		if ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$pagetitle = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $pagetitle);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$pagetitle = JText::sprintf('JPAGETITLE', $pagetitle, $app->getCfg('sitename'));
		}

		//Set Page title
		$document->setTitle($pagetitle);
		$document->setMetadata('title' , $pagetitle);
		$document->setMetadata('keywords', $pagetitle);

		// Check if the user has access to the add-eventform
		$maintainer = JEMUser::ismaintainer('add');
		$genaccess 	= JEMUser::validate_user($jemsettings->evdelrec, $jemsettings->delivereventsyes);

		if ($maintainer || $genaccess || $user->authorise('core.create','com_jem')) {
			$addeventlink = 1;
		} else {
			$addeventlink = 0;
		}

		//Check if the user has access to the add-venueform
		$maintainer2 = JEMUser::venuegroups('add');
		$genaccess2 	= JEMUser::validate_user($jemsettings->locdelrec, $jemsettings->deliverlocsyes);
		if ($maintainer2 || $genaccess2) {
			$addvenuelink = 1;
		} else {
			$addvenuelink = 0;
		}

		// Create the pagination object
		$pagination = $this->get('Pagination');

		$this->rows				= $rows;
		$this->print_link		= $print_link;
		$this->params			= $params;
		$this->addvenuelink		= $addvenuelink;
		$this->addeventlink		= $addeventlink;
		$this->pagination		= $pagination;
		$this->item				= $menuitem;
		$this->jemsettings		= $jemsettings;
		$this->settings			= $settings;
		$this->task				= $task;
		$this->pagetitle		= $pagetitle;

		parent::display($tpl);
	}
}
?>