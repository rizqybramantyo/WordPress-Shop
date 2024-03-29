<?php
class order extends module {
    protected $_currentOrder = array();
	/**
	 * This need to store tamp order data and receive some calculations, that need real order
	 */
	protected $_tempOrderData = array();
    public function getCurrent($updateFromDB = false) {
		if(!empty($this->_tempOrderData))
			return $this->_tempOrderData;
        $currentId = $this->getCurrentID();
        if(($updateFromDB || empty($this->_currentOrder)) && !empty($currentId)) {
            $this->_currentOrder = $this->getModel()->get( $currentId );
        }
        return $this->_currentOrder;
    }
	public function setTempOrder($d = array()) {
		$this->_tempOrderData = $d;
	}
    public function getLastID() {
        return $this->getModel()->getLastID();
    }
    public function getCurrentID() {
        return req::getVar('orderID', 'session');
    }
    public function setCurrentID($id) {
        if($id && is_numeric($id))
            req::setVar('orderID', $id, 'session');
    }
    public function unsetCurrentID() {
        req::clearVar('orderID', 'session');
    }
    public function clearCurrent() {
        $this->_currentOrder = array();
    }
    public function setToCurrent($key, $val) {
        $this->_currentOrder[$key] = $val;
    }
    public function userHaveAccess($order, $uid = 0) {
        if(empty($uid))     //Check for current login user in this case
            $uid = frame::_()->getModule('user')->getCurrentID();
        if(!empty($uid) && !empty($order)) {
            if(frame::_()->getModule('user')->isAdmin() || ($order['user_id'] == frame::_()->getModule('user')->getCurrentID())) {
                return true;
            }
        }
        return false;
    }
}