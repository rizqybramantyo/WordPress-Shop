<style type="text/css">
    .toeGiftSelectProductRow:hover {
        background-color: #b9e3f0;
    }
</style>
<table width="450px;">
<?php foreach($this->products as $p) { ?>
    <tr class="toeGiftSelectProductRow">
        <td><?php echo $p['post_title']?></td>
        <td><a href="<?php echo uri::_(array('mod' => 'gifts', 'action' => 'selectProduct', 'gid' => $this->gift['id'], 'pid' => $p['post_id']))?>"><?php lang::_e('Select product')?></a></td>
        <td><a href="<?php echo frame::_()->getModule('products')->getLink($p['post_id'])?>" target="_blank"><?php lang::_e('View product')?></a></td>
    </tr>
<?php }?>
</table>