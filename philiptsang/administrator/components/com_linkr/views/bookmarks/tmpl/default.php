<?php
defined('_JEXEC') or die;

// Bookmark count
$n	= count( $this->bookmarks );

// Popular icon
$yes	= JHTML::image( 'images/M_images/rating_star.png', '+' );
$no		= JHTML::image( 'images/M_images/rating_star_blank.png', '-' );

// Default bookmarks alert
?>
<script type="text/javascript">
function submitbutton(task)
{
	if (task != 'install') return submitform(task);
	
	// Confirm installation
	if (confirm('<?php echo JText::_('CONFIRM_DEFAULT_BMS', true); ?>')) return submitform(task);
}
</script>

<form action="index.php?option=com_linkr&view=bookmarks" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="40px" align="center">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->bookmarks ); ?>);"/>
			</th>
			<th width="40px" align="center">
				<?php echo JHTML::_( 'grid.sort', 'POPULAR', 'popular', $this->order['order_Dir'], $this->order['order'] ); ?>
			</th>
			<th width="75px">
				<?php echo JHTML::_( 'grid.sort', 'ORDER', 'ordering', $this->order['order_Dir'], $this->order['order'] ); ?>
			</th>
			<th width="18px">
				<?php echo JHTML::_( 'grid.order', $this->bookmarks, 'filesave.png', 'bookmark.saveorder' ); ?>
			</th>
			<th align="center">
				<?php echo JHTML::_( 'grid.sort', 'BOOKMARKS', 'name', $this->order['order_Dir'], $this->order['order'] ); ?>
			</th>
		</tr>
	</thead>
	<tbody>
<?php

$k	= 0;
for ($i = 0; $i < $n; $i++)
{
	$b			= & $this->bookmarks[$i];
	
	// Checkbox
	$checked 	= JHTML::_( 'grid.id', $i, $b->id, false, 'bid' );
	
	// Popular icon
	$popular	= ($b->popular) ? 'unpop' : 'makepop';
	$popular	= array( 'onclick' =>
					'return listItemTask(\'cb'. $i .'\',\''. $popular .'\')' );
	$popular	= JHTML::link( '#', ($b->popular) ? $yes : $no, $popular );
	
	// Ordering
	$up	= $this->page->orderUpIcon( $i, true, 'bookmark.orderup' );
	$down	= $this->page->orderDownIcon( $i, $n, true, 'bookmark.orderdown' );
	
	// Edit link
	$link 		= JRoute::_( index .'&controller=bookmark&task=edit&bid[]='. $b->id );
	$link		= JHTML::link( $link, $b->name .' - '. $b->text );
?>
		<tr class="row<?php echo $k; ?>">
			<td align="center">
				<?php echo $checked; ?>
			</td>
			<td align="center">
				<?php echo $popular; ?>
			</td>
			<td style="padding-left:30px;" class="order" colspan="2">
				<div style="float:left;margin-top:5px;">
					<input type="text" name="order[]" size="5"
						value="<?php echo $b->ordering; ?>"
						class="inputbox" style="text-align:center;" />
				</div>
				<div style="float:left;">
					<div><?php echo $up; ?></div>
					<div><?php echo $down; ?></div>
				</div>
			</td>
			<td>
				&nbsp;&nbsp;<?php echo $link; ?>
			</td>
		</tr>
<?php
	$k	= 1 - $k;
}
?>
	</tbody>
	</table>
</div>

	<input type="hidden" name="option" value="com_linkr"/>
	<input type="hidden" name="controller" value="bookmark"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->order['order']; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->order['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
