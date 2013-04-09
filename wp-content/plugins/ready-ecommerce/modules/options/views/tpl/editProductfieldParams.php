<?php 
    $optionsDisplay = 'display: none;';
    $options = array();
    switch($this->productfields['htmltype_id']->getValue()) {
        case 5: case 9: case 10: case 12:       //Checkboxes, Drop Down, Radio Buttons, List
            $options = frame::_()->getModule('options')->getModel('extraoptions')->get(array('ef_id' => (int) $this->productfields['id']));
            $optionsDisplay = '';
            break;
        default:
            break;
    }
?>
<div class="options options_tag" style="<?php echo $optionsDisplay?>">
    <span class="add_option"><?php lang::_e('Add Option')?></span>
    <?php foreach($options as $o) { ?>
        <p>
            <?php lang::_e('Value')?>:  <?php echo html::text('params[options][value][]', array('value' => $o['value']))?>
        <?php /*?>    
            <?php lang::_e('Price')?>:  <?php echo html::text('params[options][data][price][]', array('value' => $o['data']['price']))?>%
            <?php lang::_e('Weight')?>: <?php echo html::text('params[options][data][weight][]', array('value' => $o['data']['weight']))?>%
         <?php */?> 
         
            <span class="delete_option"></span>
        </p>
    <?php }?>
</div>
