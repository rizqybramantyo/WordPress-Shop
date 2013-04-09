<?php
class special_productsController extends controller {
    public function storeSpecialProduct() {
        $res = new response();
        if($id = $this->getModel()->store(req::get('post'))) {
            $res->addData( $this->getModel()->get(array('id' => $id)) );
            $res->addMessage(lang::_('Done'));
        } else 
            $res->addError ( $this->getModel()->getErrors() );
        //var_dump(mysql_error());
        return $res->ajaxExec();
    }
    public function get() {
        $res = new response();
        $data = $this->getModel()->get(req::get('post'));
        if(empty($data)) {
            $res->addError(lang::_('No data found'));
        } else {
            $res->addMessage(lang::_('Load compleate'));
            $res->addData($data);
        }
        return $res->ajaxExec();
    }
    public function deleteSpecialProduct() {
        $res = new response();
        if($this->getModel()->delete(req::get('post'))) {
            $res->addMessage(lang::_('Deleted'));
            $res->addData('id', req::getVar('id'));
        } else
            $res->addError( $this->getModel()->getErrors() );
        return $res->ajaxExec();
    }
}
?>
