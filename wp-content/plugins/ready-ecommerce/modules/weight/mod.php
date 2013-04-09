<?php
class weight extends unitsModule {
    protected $_units = array(
        'lb' => array('k' => array('kg' => 0.45359237,
                                    'oz' => 16)),
        'kg' => array('k' => array('lb' => 2.20462262,
                                    'oz' => 35.273944)),
        'oz' => array('k' => array('lb' => 0.0625,
                                    'kg' => 0.02834954)),
    );
}
?>
