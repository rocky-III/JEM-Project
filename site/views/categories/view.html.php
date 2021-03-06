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
require JPATH_COMPONENT_SITE.'/classes/view.class.php';

/**
 * HTML View class for the Categories View
 *
 * @package JEM
 *
 */
class JEMViewCategories extends JEMView
{
	/**
	 * Creates the Categories View
	 */
	function display($tpl=null)
	{
		$app = JFactory::getApplication();

		$document 		= JFactory::getDocument();
		$jemsettings 	= JEMHelper::config();
		$user			= JFactory::getUser();
		$print			= JRequest::getBool('print');
		$task			= JRequest::getWord('task');
		$model 			= $this->getModel();
		$id 			= JRequest::getInt('id', 1);

		$rows 		= $this->get('Data');
		$pagination = $this->get('Pagination');

		// Load css
		JHtml::_('stylesheet', 'com_jem/jem.css', array(), true);
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #jem dd { height: 1%; }</style><![endif]-->');
		if ($print) {
			JHtml::_('stylesheet', 'com_jem/print.css', array(), true);
			$document->setMetaData('robots', 'noindex, nofollow');
		}

		//get menu information
		$menu		= $app->getMenu();
		$menuitem	= $menu->getActive();
		$params 	= $app->getParams('com_jem');

		$pagetitle = $params->def('page_title', $menuitem->title);
		$pageheading = $params->def('page_heading', $params->get('page_title'));

		//pathway
		$pathway = $app->getPathWay();
		if($menuitem) {
			$pathway->setItemName(1, $menuitem->title);
		}

		if ($task == 'archive') {
			$pathway->addItem(JText::_('COM_JEM_ARCHIVE'), JRoute::_('index.php?view=categories&id='.$id.'&task=archive'));
			$print_link = JRoute::_('index.php?option=com_jem&view=categories&id='.$id.'&task=archive&print=1&tmpl=component');
			$pagetitle   .= ' - '.JText::_('COM_JEM_ARCHIVE');
			$pageheading .= ' - '.JText::_('COM_JEM_ARCHIVE');
			$params->set('page_heading', $pageheading);
		} else {
			$print_link = JRoute::_('index.php?option=com_jem&view=categories&id='.$id.'&print=1&tmpl=component');
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
		$document->setMetaData('title' , $pagetitle);

		//Check if the user has access to the form
		$maintainer = JEMUser::ismaintainer('add');
		$genaccess 	= JEMUser::validate_user($jemsettings->evdelrec, $jemsettings->delivereventsyes);

		if ($maintainer || $genaccess || $user->authorise('core.create','com_jem')) {
			$dellink = 1;
		} else {
			$dellink = 0;
		}

		// Get events if requested
		if ($params->get('detcat_nr', 0) > 0) {
			foreach($rows as $row) {
				$row->events = $model->getEventdata($row->id);
			}
		}

		$this->rows				= $rows;
		$this->task				= $task;
		$this->params			= $params;
		$this->dellink			= $dellink;
		$this->pagination		= $pagination;
		$this->item				= $menuitem;
		$this->jemsettings		= $jemsettings;
		$this->pagetitle		= $pagetitle;
		$this->print_link		= $print_link;
		$this->model			= $model;
		$this->id				= $id;

		parent::display($tpl);
	}
}
?>
