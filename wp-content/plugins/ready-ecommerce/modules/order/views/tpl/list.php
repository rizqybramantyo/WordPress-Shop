<table width="100%">
    <tr class="toe_admin_row_header">
        <td><?php lang::_e('Order ID')?></td>
        <td><?php lang::_e('Cost')?></td>
        <td><?php lang::_e('Status')?></td>
		<?php if(frame::_()->getModule('user')->isAdmin()) {?>
			<td><?php lang::_e('User Email')?></td>
			<td><?php lang::_e('Remove')?></td>
		<?php }?>
    </tr>
	
<?php if(!empty($this->orders)) { ?>
	<?php foreach($this->orders as $o) { ?>
    <tr class="toe_admin_row toe_order_row">
        <td class="toeOrderListCell id"><?php echo $o['order_id']?></td>
        <td class="toeOrderListCell"><?php echo frame::_()->getModule('currency')->display($o['total'], $o['currency'])?></td>
        <td class="toeOrderListCell"><?php echo $o['status']?></td>
		<?php if(frame::_()->getModule('user')->isAdmin()) {?>
			<td>
			<?php if(!empty($o['user_email'])) { ?>
				<a href="<?php uri::_e(array('baseUrl' => admin_url('user-edit.php'), 'user_id' => $o['user_id']))?>" target="_blank"><?php echo $o['user_email']?></a>
			<?php } else { ?>
				&nbsp;
			<?php }?>
			</td>
			<td class="toeOrderListCellRemove"><a href="" onclick="return false;"><?php echo html::img('cross.gif')?></a></td>
		<?php }?>
    </tr>
	<?php } ?>
<?php }?>
</table>