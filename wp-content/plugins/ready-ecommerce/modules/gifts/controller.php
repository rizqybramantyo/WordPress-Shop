<?php
class giftsController extends controller {
    public function storeGift() {
        $res = new response();
        if(($id = $this->getModel()->store(req::get('post')))) {
            $res->addData( $this->getModel()->get(array('id' => $id)) );
            $res->addMessage(lang::_('Done'));
        } else {
            $res->addError( $this->getModel()->getErrors() );
        }
        $res->ajaxExec();
    }
    public function deleteGift() {
        $res = new response();
        if($this->getModel()->delete(req::get('post'))) {
            $res->addMessage(lang::_('Deleted'));
            $res->addData('id', req::getVar('id'));
        } else
            $res->addError( $this->getModel()->getErrors() );
        return $res->ajaxExec();
    }
    public function getSelectProductHtml() {
        $res = new response();
        $id = req::getVar('id');
        if($id) {
            $res->setHtml( $this->getView()->selectProduct($id) );
        }
        return $res->ajaxExec();
    }
    public function selectProduct() {
        if(!$this->getModel()->selectProduct(req::get('get'))) {
            $errors = $this->getModel()->getErrors();
        }
        redirect(frame::_()->getModule('pages')->getLink(array('mod' => 'checkout', 'action' => 'getAllHtml')));
    }
}
?>
