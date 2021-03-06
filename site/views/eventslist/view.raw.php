<?php
/**
 * @version 1.9.6
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_SITE.'/components/com_jem/classes/iCalcreator.class.php';

jimport('joomla.application.component.view');

/**
 * ICS-View
 *
 * @package JEM
 *
 */
class JemViewEventslist extends JViewLegacy
{
	/**
	 * Creates the output for the Eventslist view
	 *
 	 *
	 */
	function display($tpl = null)
	{
		$settings 	= JemHelper::config();
		$settings2	= JemHelper::globalattribs();

		if ($settings2->get('global_show_ical_icon','0')==1) {

			// Get data from the model
			$model = $this->getModel();
			$model->setLimit($settings->ical_max_items);
			$model->setLimitstart(0);
			$rows = $model->getData();

			// initiate new CALENDAR
			$vcal = JemHelper::getCalendarTool();
			$vcal->setConfig("filename", "events.ics");

			foreach ($rows as $row) {
				JemHelper::icalAddEvent($vcal, $row);
			}
			// generate and redirect output to user browser
			$vcal->returnCalendar();
			echo $vcal->createCalendar(); // debug

		} else {
			return;
		}
	}
}
?>