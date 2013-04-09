<?php
class size extends unitsModule {
    protected $_units = array(
        'inch' => array('k' => array('m' => 0.0254)),
        'm' => array('k' => array('inch' => 39.3701)),
    );
}