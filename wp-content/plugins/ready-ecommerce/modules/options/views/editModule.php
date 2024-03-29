<?php
class editModuleView extends view {
    public function getAdminPage($someWPVar = '', $show = true) {
        $moduleFields = frame::_()->getModule('options')->getModel('modules')->get(array('id' => req::getVar('id')));
        $type_id = $moduleFields['type_id']->value;
        foreach($moduleFields as $code => $mf) {
            if(utils::is($mf, 'field')) {
                if($code == 'params' || ($type_id != 1 && in_array($code, array('active', 'label', 'description')))) continue;
                if($code == 'has_tab') {
                    unset($moduleFields[$code]);
                    continue;
                }
                if($code == 'type_id') {
                    $mf->setValue($moduleFields['types'][$mf->value]);
                }
                if($code == 'active') {
                    $mf->setValue(((bool)$mf->value ? lang::_('Yes') : lang::_('No')));
                }
                $mf->setHtml('block');
            }
        }
        $this->assign('moduleFields', $moduleFields);
        $this->assign('id', req::getVar('id'));
        $tpl = 'editModule';
        if($show)
            parent::display($tpl);
        else
            return parent::getContent($tpl);
    }
}
?>
