<div id="toeOrdersTabs">
    <ul>
        <li><a href="#toe_orders_all"><?php lang::_e('All')?></a></li>
        <?php foreach($this->fields['status']->getHtmlParam('options') as $sKey => $sName) { //for now $sKey and $sName are equal, but who know what will be next?... ?> 
        <li><a href="#toe_<?php echo $sKey?>"><?php echo strFirstUp($sKey)?></a></li>
        <?php }?>
    </ul>
    <div id="toe_orders_all">
        <?php echo $this->listsByStatusHtml['all']?>
    </div>
    <?php foreach($this->fields['status']->getHtmlParam('options') as $sKey => $sName) { //for now $sKey and $sName are equal, but who know what will be next?... ?> 
    <div id="toe_<?php echo $sKey?>">
        <?php echo $this->listsByStatusHtml[ $sKey ]?>
    </div>
    <?php }?>
    <?php if(!empty($this->ordersListAdminBottom)) { ?>
    <div><?php echo $this->ordersListAdminBottom?></div>
    <?php }?>
</div>
