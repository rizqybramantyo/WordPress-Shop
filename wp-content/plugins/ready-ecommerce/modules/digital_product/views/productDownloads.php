<?php
class productDownloadsView extends view {
    public function display($tpl = '') {
        global $post;
        $downloads = frame::_()->getModule('digital_product')->getModel('downloads')->getProductDownloads($post);
        $params = frame::_()->getModule('digital_product')->getParamsObject();
        $this->assign('downloads', $downloads);
        $this->assign('params', $params);
        parent::display('downloads');
    }
}
?>
