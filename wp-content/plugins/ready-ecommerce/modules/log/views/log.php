<?php
class logView extends view {
    public function getList() {
        $this->assign('logs', frame::_()->getModule('log')->getModel()->getSorted());
        $this->assign('logTypes', frame::_()->getModule('log')->getModel()->getTypes());
        parent::display('logList');
    }
}