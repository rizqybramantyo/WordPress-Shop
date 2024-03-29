<h1><?php lang::_e(array(($this->method == 'post' ? 'Add' : 'Edit'), 'Product Parameter'))?></h1>
<form action="<?php echo uri::mod('options', '', '', array('id' => $this->id))?>" method="post" id="editProductfieldForm">
	<table width="100%">
		<tr>
			<td valign="top">
				<table width="100%">
    <?php
        $tag = $this->productfields['htmltype_id']->value;
        foreach($this->productfields as $k => $f) {
            if(in_array($k, array('types', 'type_labels'))) continue; ?>
			<?php if($k == 'destination') { ?>
				</table>
			</td>
			<td valign="top">
				<table width="100%">
			<?php }?>
					<tr>
						<td class="field_label">
							<?php if ($f->html != "hidden") {
									echo $f->label.':';
								} else {
									echo '&nbsp;';
								}?>
							<?php if ($f->description != '') {?>
								<a href="#" class="toeOptTip" tip="<?php echo $f->description;?>"></a>
							<?php }?>
						</td>
						<td>
							<?php 
								$displayed = false;
								switch($f->getName()) {
									case 'htmltype_id':
										$f->addHtmlParam('options', $this->productfields['type_labels']);
										$f->addHtmlParam('attrs', 'class="extrafield_type" rel="editProductfieldForm"');
										break;
									case 'destination':
										$destValue = $f->getValue();
										if(empty($destValue) && !is_array($destValue)) {
											$destValue = array('pids' => array(), 'categories' => array());
										}
										$f->addHtmlParam('attrs', 'class="toeMultipleSelectWithSelectAll"');
							?>
								<div style="float: left;"><?php lang::_e('For Categories')?>:<br />
							<?php
										$f->setName('destination[categories]');
										$f->addHtmlParam('options', $this->destination['categories']);
										$f->setValue($destValue['categories']);
										$f->display();
							?>
								</div><div style="float: left;"><?php lang::_e('For Products')?>:<br />
							<?php            
										$f->setName('destination[pids]');
										$f->addHtmlParam('options', $this->destination['pids']);
										$f->setValue($destValue['pids']);
										$f->display();
							?>
								</div>
							<?php
										$displayed = true;
										break;
									case 'params':
										echo $this->paramsHtml;
										$displayed = true;
										break;
								}
								if(!$displayed)
									$f->display($tag, $this->id);
							?>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td>
							<?php echo html::hidden('id', array('value' => $this->id))?>
							<?php echo html::hidden('action', array('value' => $this->method. 'Productfield'))?>
							<?php echo html::hidden('page', array('value' => 'options'))?>
							<?php echo html::hidden('reqType', array('value' => 'ajax'))?>
							<?php echo html::hidden('show_field', array('value' => $this->show_field))?>
							<?php echo html::button(array('attrs' => 'class="button"', 'value' => lang::_('Save')))?>
						</td>
						<td id="mod_msg_prod" class="mod_msg"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>