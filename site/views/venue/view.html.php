<?php
/**
 * @version 1.9.6
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die ();

jimport('joomla.application.component.view');
require JPATH_COMPONENT_SITE.'/classes/view.class.php';

/**
 * HTML View class for the Venue View
 * @package JEM
 *
 */
class JEMViewVenue extends JEMView {

	/**
	 * Creates the Venue View
	 */
	function display($tpl = null) {
		if ($this->getLayout() == 'calendar') {
			$app = JFactory::getApplication();

			// Load tooltips behavior
			JHtml::_('behavior.tooltip');

			// initialize variables
			$document 		= JFactory::getDocument();
			$menu 			= $app->getMenu();
			$menuitem		= $menu->getActive();
			$jemsettings	= JEMHelper::config();
			$params 		= $app->getParams();
			$uri 			= JFactory::getURI();
			$pathway 		= $app->getPathWay();
			$jinput 		= $app->input;
			$print			= JRequest::getBool('print');

			// Load css
			JHtml::_('stylesheet', 'com_jem/jem.css', array(), true);
			JHtml::_('stylesheet', 'com_jem/calendar.css', array(), true);
			$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #jem dd { height: 1%; }</style><![endif]-->');
			if ($print) {
				JHtml::_('stylesheet', 'com_jem/print.css', array(), true);
				$document->setMetaData('robots', 'noindex, nofollow');
			}

			$evlinkcolor = $params->get('eventlinkcolor');
			$evbackgroundcolor = $params->get('eventbackgroundcolor');
			$currentdaycolor = $params->get('currentdaycolor');
			$eventandmorecolor = $params->get('eventandmorecolor');

			$style = '
			div[id^=\'venuez\'] a {color:' . $evlinkcolor . ';}
			div[id^=\'venuez\'] {background-color:' . $evbackgroundcolor . ';}
			.eventandmore {background-color:' . $eventandmorecolor . ';}
			.today .daynum {background-color:' . $currentdaycolor . ';}';
			$document->addStyleDeclaration ($style);

			// add javascript (using full path - see issue #590)
			JHtml::_('script', 'media/com_jem/js/calendar.js');

			// Retrieve year/month variables
			$year = $jinput->get('yearID', strftime("%Y"),'int');
			$month = $jinput->get('monthID', strftime("%m"),'int');

			// get data from model and set the month
			$model = $this->getModel('VenueCal');
			$model->setDate(mktime(0, 0, 1, $month, 1, $year));
			$rows = $this->get('Data','VenueCal');
			$venue = $this->get('Venuecal','VenueCal');

			// detect if there are venues to display
			if ($venue == null) {
				return false;
			}

			// Set Page title
			$pagetitle = $params->def('page_title', $menuitem->title);
			$params->def('page_heading', $params->get('page_title'));

			// Add site name to title if param is set
			if ($app->getCfg('sitename_pagetitles', 0) == 1) {
				$pagetitle = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $pagetitle);
			}
			elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
				$pagetitle = JText::sprintf('JPAGETITLE', $pagetitle, $app->getCfg('sitename'));
			}

			$document->setTitle($pagetitle);
			$document->setMetaData('title', $pagetitle);

			// init calendar
			$cal = new JEMCalendar($year, $month, 0, $app->getCfg('offset'));
			$cal->enableMonthNav(JEMHelperRoute::getVenueRoute($venue->slug) .'&layout=calendar');
			$cal->setFirstWeekDay($params->get('firstweekday',1));

			// map variables
			$this->rows 		= $rows;
			$this->params 		= $params;
			$this->jemsettings 	= $jemsettings;
			$this->cal 			= $cal;

		} else {

			// add templatepath
			$this->addTemplatePath(JPATH_COMPONENT.'/common/views/tmpl' );

			// initialize variables
			$app 			= JFactory::getApplication();
			$document 		= JFactory::getDocument();
			$menu 			= $app->getMenu();
			$menuitem		= $menu->getActive();
			$jemsettings 	= JEMHelper::config();
			$settings 		= JEMHelper::globalattribs();
			$db 			= JFactory::getDBO();
			$params 		= $app->getParams('com_jem');
			$pathway 		= $app->getPathWay ();
			$uri 			= JFactory::getURI();
			$task 			= JRequest::getWord('task');
			$user			= JFactory::getUser();

			// Load css
			JHtml::_('stylesheet', 'com_jem/jem.css', array(), true);
			$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #jem dd { height: 1%; }</style><![endif]-->');

			// get data from model
			$rows	= $this->get('Data');
			$venue	= $this->get('Venue');

			// are events available?
			if (! $rows) {
				$noevents = 1;
			} else {
				$noevents = 0;
			}

			// does the venue exist?
			if ($venue->id == 0) {
				// TODO Translation
				return JError::raiseError(404,JText::_(COM_JEM_VENUE_NOTFOUND));
			}

			// Decide which parameters should take priority
			$useMenuItemParams = ($menuitem && $menuitem->query['option'] == 'com_jem'
			                                && $menuitem->query['view']   == 'venue'
			                                && $menuitem->query['id']     == $venue->id);

			// get search & user-state variables
			$filter_order 		= $app->getUserStateFromRequest('com_jem.venue.filter_order', 'filter_order', 'a.dates', 'cmd');
			$filter_order_Dir 	= $app->getUserStateFromRequest('com_jem.venue.filter_order_Dir', 'filter_order_Dir', '', 'word');
			$filter 			= $app->getUserStateFromRequest('com_jem.venue.filter', 'filter', '', 'int');
			$search 			= $app->getUserStateFromRequest('com_jem.venue.filter_search', 'filter_search', '', 'string');
			$search 			= $db->escape(trim(JString::strtolower($search)));

			// table ordering
			$lists['order_Dir']	= $filter_order_Dir;
			$lists['order']		= $filter_order;

			// Get image
			$limage = JEMImage::flyercreator($venue->locimage,'venue');

			// Add feed links
			$link = '&format=feed&id='.$venue->id.'&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);

			// pathway, page title, page heading
			if ($useMenuItemParams) {
				$pagetitle   = $params->get('page_title', $menuitem->title ? $menuitem->title : $venue->venue);
				$pageheading = $params->get('page_heading', $pagetitle);
				$pathway->setItemName(1, $menuitem->title);
			} else {
				$pagetitle   = $venue->venue;
				$pageheading = $pagetitle;
				$params->set('show_page_heading', 1); // ensure page heading is shown
				$pathway->addItem($pagetitle, JRoute::_(JEMHelperRoute::getVenueRoute($venue->slug)));
			}

			// create the pathway
			if ($task == 'archive') {
				$pathway->addItem (JText::_('COM_JEM_ARCHIVE'), JRoute::_(JEMHelperRoute::getVenueRoute($venue->slug).'&task=archive'));
				$print_link = JRoute::_(JEMHelperRoute::getVenueRoute($venue->slug).'&task=archive&print=1&tmpl=component');
				$pagetitle   .= ' - ' . JText::_('COM_JEM_ARCHIVE');
				$pageheading .= ' - ' . JText::_('COM_JEM_ARCHIVE');
			} else {
				//$pathway->addItem($venue->venue, JRoute::_(JEMHelperRoute::getVenueRoute($venue->slug)));
				$print_link = JRoute::_(JEMHelperRoute::getVenueRoute($venue->slug).'&print=1&tmpl=component');
			}

			$params->set('page_heading', $pageheading);

			// Add site name to title if param is set
			if ($app->getCfg('sitename_pagetitles', 0) == 1) {
				$pagetitle = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $pagetitle);
			}
			elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
				$pagetitle = JText::sprintf('JPAGETITLE', $pagetitle, $app->getCfg('sitename'));
			}

			// set Page title & Meta data
			$document->setTitle($pagetitle);
			$document->setMetaData('title', $pagetitle);
			$document->setMetadata('keywords', $venue->meta_keywords);
			$document->setDescription(strip_tags($venue->meta_description));

			// Check if the user has access to the add-eventform
			$maintainer = JEMUser::ismaintainer('add');
			$genaccess = JEMUser::validate_user($jemsettings->evdelrec, $jemsettings->delivereventsyes);

			if ($maintainer || $genaccess || $user->authorise('core.create','com_jem')) {
				$addeventlink = 1;
			} else {
				$addeventlink = 0;
			}

			// Check if the user has access to the add-venueform
			$maintainer2 = JEMUser::venuegroups('add');
			$genaccess2 = JEMUser::validate_user($jemsettings->locdelrec, $jemsettings->deliverlocsyes);
			if ($maintainer2 || $genaccess2) {
				$addvenuelink = 1;
			} else {
				$addvenuelink = 0;
			}

			// Check if the user has access to the edit-venueform
			$maintainer3 = JEMUser::venuegroups('edit');
			$genaccess3 = JEMUser::editaccess($jemsettings->venueowner, $venue->created, $jemsettings->venueeditrec, $jemsettings->venueedit);
			if ($maintainer3 || $genaccess3) {
				$allowedtoeditvenue = 1;
			} else {
				$allowedtoeditvenue = 0;
			}

			// Generate Venuedescription
			if (!$venue->locdescription == '' || !$venue->locdescription == '<br />') {
				// execute plugins
				$venue->text = $venue->locdescription;
				$venue->title = $venue->venue;
				JPluginHelper::importPlugin ('content');
				$app->triggerEvent ('onContentPrepare', array (
						'com_jem.venue',
						&$venue,
						&$params,
						0
				));
				$venuedescription = $venue->text;
			}

			// build the url
			// TODO: What's about "https://"?
			if (!empty($venue->url) && strtolower (substr($venue->url, 0, 7)) != "http://") {
				$venue->url = 'http://' . $venue->url;
			}

			// prepare the url for output
			if (strlen($venue->url) > 35) {
				$venue->urlclean = $this->escape(substr($venue->url, 0, 35 )) . '...';
			} else {
				$venue->urlclean = $this->escape($venue->url);
			}

			// create flag
			if ($venue->country) {
				$venue->countryimg = JemHelperCountries::getCountryFlag($venue->country);
			}

			// Create the pagination object
			$pagination = $this->get('Pagination');

			// filters
			$filters = array ();

			if ($jemsettings->showtitle == 1) {
				$filters[] = JHtml::_('select.option', '1', JText::_('COM_JEM_TITLE'));
			}
			if ($jemsettings->showlocate == 1) {
				$filters[] = JHtml::_('select.option', '2', JText::_('COM_JEM_VENUE'));
			}
			if ($jemsettings->showcity == 1) {
				$filters[] = JHtml::_('select.option', '3', JText::_('COM_JEM_CITY'));
			}
			if ($jemsettings->showcat == 1) {
				$filters[] = JHtml::_('select.option', '4', JText::_('COM_JEM_CATEGORY'));
			}
			if ($jemsettings->showstate == 1) {
				$filters[] = JHtml::_('select.option', '5', JText::_('COM_JEM_STATE'));
			}
			$lists['filter'] = JHtml::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $filter);
			$lists['search'] = $search;

			// mapping variables
			$this->lists 				= $lists;
			$this->action 				= $uri->toString ();
			$this->rows 				= $rows;
			$this->noevents 			= $noevents;
			$this->venue 				= $venue;
			$this->print_link 			= $print_link;
			$this->params 				= $params;
			$this->addvenuelink 		= $addvenuelink;
			$this->addeventlink 		= $addeventlink;
			$this->limage 				= $limage;
			$this->venuedescription		= $venuedescription;
			$this->pagination 			= $pagination;
			$this->jemsettings 			= $jemsettings;
			$this->settings				= $settings;
			$this->item					= $menuitem;
			$this->pagetitle			= $pagetitle;
			$this->task					= $task;
			$this->allowedtoeditvenue 	= $allowedtoeditvenue;

		}

		parent::display($tpl);
	}
}
?>
