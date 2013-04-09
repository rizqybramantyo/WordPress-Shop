<h1><?php lang::_e('Payment Info')?></h1>
<form method="post" action="<?php echo $this->gatewayUrl?>">
    <?php foreach($this->fields as $f) {
        $f->display();
    }?>
    <?php echo html::submit('pay', array('value' => lang::_('Pay')))?>
</form>