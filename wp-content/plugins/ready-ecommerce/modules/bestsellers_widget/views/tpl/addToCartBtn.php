<?php echo html::formStart('bestSelldWidg'. $this->post->ID, array('attrs' => 'onsubmit="toeAddToCart(this, \'\', true); return false;"', 'method' => 'POST')) ?>
	<?php echo html::text('qty', array('value' => 1))?>
	<?php echo html::hidden('addQty', array('value' => 1))?>
	<?php echo html::hidden('mod', array('value' => 'user'))?>
	<?php echo html::hidden('action', array('value' => 'addToCart'))?>
	<?php echo html::hidden('pid', array('value' => $this->post->ID))?>
	<?php echo html::hidden('reqType', array('value' => 'ajax'))?>
	<?php echo html::submit('add', array('value' => lang::_('Add to Cart')))?>
	<div class="toeAddToCartMsg"></div>
<?php echo html::formEnd()?>
