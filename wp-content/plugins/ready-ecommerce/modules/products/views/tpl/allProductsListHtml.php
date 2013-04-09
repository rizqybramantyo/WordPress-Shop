<?php if(class_exists('frame') && frame::_()->getModule('pagination')) {
	frame::_()->getModule('pagination')->getView()->display(array('nav_id' => 'pagination', 'show' => array('navigation', 'perPage', 'ordering')));
}?>
<?php foreach($this->productsContentParts as $pHtml) {
	echo $pHtml;
}?>
<?php if(class_exists('frame') && frame::_()->getModule('pagination')) {
	frame::_()->getModule('pagination')->getView()->display(array('nav_id' => 'pagination', 'show' => array('navigation', 'perPage', 'ordering')));
}?>
