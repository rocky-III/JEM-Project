<?php
/**
 * @version 1.9.6
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
?>

<?php if (!$this->params->get('show_page_heading', 1)) :
           /* hide this if page heading is shown */     ?>
<h2><?php echo JText::_('COM_JEM_MY_EVENTS'); ?></h2>
<?php endif; ?>

<script type="text/javascript">
	function tableOrdering(order, dir, view)
	{
		var form = document.getElementById("adminForm");

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		form.submit(view);
	}
</script>

<form action="<?php echo $this->action; ?>" method="post" name="adminForm" id="adminForm">
<?php if ($this->settings->get('global_show_filter',1) || $this->settings->get('global_display',1)) : ?>
<div id="jem_filter" class="floattext">
	<?php if ($this->settings->get('global_show_filter',1)) : ?>
	<div class="jem_fleft">
		<?php
		echo '<label for="filter">'.JText::_('COM_JEM_FILTER').'</label>&nbsp;';
		echo $this->lists['filter'].'&nbsp;';
		?>
		<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->lists['search'];?>" class="inputbox" onchange="document.adminForm.submit();" />
		<button class="" type="submit"><?php echo JText::_('COM_JEM_GO'); ?></button>
		<button class="" type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>
	<?php endif; ?>
	<?php if ($this->settings->get('global_display',1)) : ?>
	<div class="jem_fright">
		<?php
		echo '<label for="limit">'.JText::_('COM_JEM_DISPLAY_NUM').'</label>&nbsp;';
		echo $this->events_pagination->getLimitBox();
		?>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>


<table class="eventtable" style="width:<?php echo $this->jemsettings->tablewidth; ?>;" summary="jem">
	<colgroup>
			<col width="1%" class="jem_col_num" />
			<col width="1%" class="jem_col_checkall" />
			<col width="<?php echo $this->jemsettings->datewidth; ?>" class="jem_col_date" />
		<?php if ($this->jemsettings->showtitle == 1) : ?>
			<col width="<?php echo $this->jemsettings->titlewidth; ?>" class="jem_col_title" />
		<?php endif; ?>
		<?php if ($this->jemsettings->showlocate == 1) :	?>
			<col width="<?php echo $this->jemsettings->locationwidth; ?>" class="jem_col_venue" />
		<?php endif; ?>
		<?php if ($this->jemsettings->showcity == 1) :	?>
			<col width="<?php echo $this->jemsettings->citywidth; ?>" class="jem_col_city" />
		<?php endif; ?>
		<?php if ($this->jemsettings->showstate == 1) :	?>
			<col width="<?php echo $this->jemsettings->statewidth; ?>" class="jem_col_state" />
		<?php endif; ?>
		<?php if ($this->jemsettings->showcat == 1) :	?>
			<col width="<?php echo $this->jemsettings->catfrowidth; ?>" class="jem_col_category" />
		<?php endif; ?>
		<?php if ($this->params->get('displayattendeecolumn') == 1) :	?>
			<col width="<?php echo $this->jemsettings->attewidth; ?>" class="jem_col_atte" />
		<?php endif; ?>
			<col width="1%" class="jem_col_status" />
	</colgroup>

	<thead>
		<tr>
			<th ><?php echo JText::_('COM_JEM_NUM'); ?></th>
			<th><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
			<th id="jem_date" class="sectiontableheader" align="left"><?php echo JHtml::_('grid.sort', 'COM_JEM_TABLE_DATE', 'a.dates', $this->lists['order_Dir'], $this->lists['order']); ?></th>
			<?php if ($this->jemsettings->showtitle == 1) : ?>
			<th id="jem_title" class="sectiontableheader" align="left"><?php echo JHtml::_('grid.sort', 'COM_JEM_TABLE_TITLE', 'a.title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
			<?php endif; ?>
			<?php if ($this->jemsettings->showlocate == 1) : ?>
			<th id="jem_location" class="sectiontableheader" align="left"><?php echo JHtml::_('grid.sort', 'COM_JEM_TABLE_LOCATION', 'l.venue', $this->lists['order_Dir'], $this->lists['order']); ?></th>
			<?php endif; ?>
			<?php if ($this->jemsettings->showcity == 1) : ?>
			<th id="jem_city" class="sectiontableheader" align="left"><?php echo JHtml::_('grid.sort', 'COM_JEM_TABLE_CITY', 'l.city', $this->lists['order_Dir'], $this->lists['order']); ?></th>
			<?php endif; ?>
			<?php if ($this->jemsettings->showstate == 1) : ?>
			<th id="jem_state" class="sectiontableheader" align="left"><?php echo JHtml::_('grid.sort', 'COM_JEM_TABLE_STATE', 'l.state', $this->lists['order_Dir'], $this->lists['order']); ?></th>
			<?php endif; ?>
			<?php if ($this->jemsettings->showcat == 1) : ?>
			<th id="jem_category" class="sectiontableheader" align="left"><?php echo JHtml::_('grid.sort', 'COM_JEM_TABLE_CATEGORY', 'c.catname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
			<?php endif; ?>
			<?php if ($this->params->get('displayattendeecolumn') == 1) : ?>
			<th id="jem_atte" class="sectiontableheader" align="center"><?php echo JText::_('COM_JEM_TABLE_ATTENDEES'); ?></th>
			<?php endif; ?>
			<th width="1%" class="center" nowrap="nowrap"><?php echo JText::_('JSTATUS'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php if (count((array)$this->events) == 0) : ?>
		<tr align="center"><td colspan="0"><?php echo JText::_('COM_JEM_NO_EVENTS'); ?></td></tr>
	<?php else : ?>
		<?php foreach ($this->events as $i => $row) : ?>
			<tr class="row<?php echo $i % 2; ?>">

				<td><?php echo $this->events_pagination->getRowOffset( $i ); ?></td>
				<td><?php echo JHtml::_('grid.id', $i, $row->eventid); ?></td>

				<td headers="jem_date" align="left">
					<?php echo JEMOutput::formatShortDateTime($row->dates, $row->times,
						$row->enddates, $row->endtimes); ?>
				</td>

				<?php if (($this->jemsettings->showtitle == 1) && ($this->jemsettings->showdetails == 1)) : ?>
					<td headers="jem_title" align="left" valign="top">
						<a href="<?php echo JRoute::_(JEMHelperRoute::getEventRoute($row->slug)); ?>">
							<?php echo $this->escape($row->title); ?>
						</a>
					</td>
				<?php endif; ?>

				<?php if (($this->jemsettings->showtitle == 1) && ($this->jemsettings->showdetails == 0)) : ?>
					<td headers="jem_title" align="left" valign="top"><?php echo $this->escape($row->title); ?></td>
				<?php endif; ?>

				<?php if ($this->jemsettings->showlocate == 1) : ?>
					<td headers="jem_location" align="left" valign="top">
						<?php if ($this->jemsettings->showlinkvenue == 1) :  ?>
							<?php echo $row->locid != 0 ? "<a href='".JRoute::_(JEMHelperRoute::getVenueRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>" : '-'; ?>
						<?php else : ?>
							<?php echo $row->locid ? $this->escape($row->venue) : '-'; ?>
						<?php endif; ?>
					</td>
				<?php endif; ?>

				<?php if ($this->jemsettings->showcity == 1) : ?>
					<td headers="jem_city" align="left" valign="top"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>
				<?php endif; ?>

				<?php if ($this->jemsettings->showstate == 1) : ?>
					<td headers="jem_state" align="left" valign="top"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>
				<?php endif; ?>

				<?php if ($this->jemsettings->showcat == 1) : ?>
					<td headers="jem_category" align="left" valign="top">
					<?php echo implode(", ",
							JEMOutput::getCategoryList($row->categories, $this->jemsettings->catlinklist)); ?>
					</td>
				<?php endif; ?>

				<?php if ($this->params->get('displayattendeecolumn') == 1) : ?>
					<td headers="jem_atte" align="center" valign="top">
					<?php if ($row->registra == 1) {
						if ($this->enableemailaddress == 1) {
							$emailaddress = '&em='.$this->enableemailaddress;
						} else {
							$emailaddress = '';
						}

						$app = JFactory::getApplication();
						$menuitem = $app->getMenu()->getActive()->id;
						$linkreg 	= 'index.php?option=com_jem&amp;view=attendees&amp;id='.$row->id.'&Itemid='.$menuitem.$emailaddress;
						$count = $row->regCount;
						if ($row->maxplaces)
						{
							$count .= '/'.$row->maxplaces;
							if ($row->waitinglist && $row->waiting) {
								$count .= ' +'.$row->waiting;
							}
						}

						if ($count > 0 && $row->published == 1) {
							?>
							<a href="<?php echo $linkreg; ?>" title="<?php echo JText::_('COM_JEM_MYEVENT_MANAGEATTENDEES'); ?>">
							<?php echo $count; ?>
							</a>
							<?php
						}

						if ($row->published == 0) {
							echo $count;
						}
						if ($count == 0  && $row->published == 1) {
							echo $count;
						}
					} else {
						echo JHtml::_('image', 'com_jem/publish_r.png',NULL,NULL,true);
					}
					?>
				</td>
				<?php endif; ?>
				<td class="center"><?php echo JHtml::_('jgrid.published', $row->published, $i,'myevents.'); ?></td>
			</tr>

		<?php
			$i = 1 - $i;
		?>
		<?php endforeach;?>
	<?php endif;?>
	</tbody>
</table>

<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="enableemailaddress" value="<?php echo $this->enableemailaddress; ?>" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_jem" />
<?php echo JHtml::_('form.token'); ?>
</form>

<div class="pagination">
	<?php echo $this->events_pagination->getPagesLinks(); ?>
</div>