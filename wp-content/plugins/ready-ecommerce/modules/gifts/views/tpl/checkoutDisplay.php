<tr>
    <td><?php lang::_e('You gifts')?></td>
    <td>
        <table>
        <?php foreach($this->gifts['data'] as $g) { ?>
            <tr>
                <td><?php lang::_e($g['label'])?></td>
                <td>
                <?php 
                    $giftContent = '';
                    switch($g['type']) {
                        case 'product':
                            echo $g['freeProductLink'];
                            break;
                    }
                    echo $giftContent;
                ?>
                </td>
            </tr>
        <?php }?>
        </table>
    </td>
</tr>
<?php if(!empty($this->gifts['total'])) { ?>
<tr>
    <td><?php lang::_e('Total gift bonus')?></td>
    <td><?php echo frame::_()->getModule('currency')->display($this->gifts['total'])?></td>
</tr>
<?php }?>
