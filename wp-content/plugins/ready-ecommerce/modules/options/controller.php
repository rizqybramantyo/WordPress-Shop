<?php
class optionsController extends controller {
    public function putModule() {
        $res = $this->getModel('modules')->put(req::get('post'));
        $res->ajaxExec();
    }
    public function putOption() {
        $res = $this->getModel('options')->put(req::get('post'));
        $res->ajaxExec();
    }
    public function getEditModule() {
        $res = new response();
        $res->html = $this->getView('editModule')->getAdminPage('', false);
        $res->ajaxExec();
    }
    public function getEditUserfields() {
        $res = new response();
        $res->html = $this->getView('userfields')->editUserfield('', false);
        $res->ajaxExec();
    }
    public function putUserfield() {
        $res = $this->getModel('userfields')->put(req::get('post'));
        $res->ajaxExec();
    }
    public function postUserfield() {
        $res = $this->getModel('userfields')->post(req::get('post'));
        $res->ajaxExec();
    }
    public function getAddUserfields() {
        $res = new response();
        $res->html = $this->getView('userfields')->addUserfields('', false);
        $res->ajaxExec();
    }
    public function deleteUserfield() {
        $res = new response();
        $res->html = $this->getModel('userfields')->delete(req::get('post'));
        $res->ajaxExec();
    }
    public function getEditProductfields() {
        $res = new response();
        $res->html = $this->getView('productfields')->editProductfield('', false);
        $res->ajaxExec();
    }
    public function putProductfield() {
        $res = $this->getModel('productfields')->put(req::get('post'));
        $res->ajaxExec();
    }
    public function postProductfield() {
        $res = $this->getModel('productfields')->post(req::get('post'));
        $res->ajaxExec();
    }
    public function getAddProductfields() {
        $res = new response();
        $res->html = $this->getView('productfields')->addProductfields('', false);
        $res->ajaxExec();
    }
    public function deleteProductfield() {
        $res = new response();
        $res->html = $this->getModel('productfields')->delete(req::get('post'));
        $res->ajaxExec();
    }
    public function deleteEfOption(){
        $res = new response();
        $res->html = $this->getModel('extraoptions')->deleteOption(req::get('post'));
        $res->ajaxExec();
    }
    public function sortExtraField(){
        $res = new response();
        $post = req::get('post');
        $fields = array();
        if (!empty($post)) {
            if (isset($post['fields']) && is_array($post['fields'])) {
                $fields = $post['fields'];
            } else {
                $res->html = new errors('Unknown extra field');
            }
            if (!empty($fields)) {
                $extrafields = frame::_()->getTable('extrafields');
                foreach ($fields as $key=>$value) {
                    $save = $extrafields->update(array('ordering'=>$key),array('id'=>$value));
                }
            }        
            if ($save) {
                $res->html = 'OK';
            } else {
                $res->html = new errors(db::getError(),'Database error');
            }
        }
        $res->ajaxExec();
    }

}
?>
