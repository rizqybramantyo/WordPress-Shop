<?php
class orderModel extends model {
    public function post($d = array()) {
        $id = 0;
        $cart = frame::_()->getModule('user')->getModel('cart')->get();
        if($cart) {
            frame::_()->getModule('checkout')->clearCurrent();
            frame::_()->getModule('order')->clearCurrent();
            $do = 'insert';
            $currentID = frame::_()->getModule('order')->getCurrentID();
            if($currentID) 
                $do = 'update';
            
            $address = $this->_getAddresses($d);
            frame::_()->getModule('order')->setToCurrent('shipping_address', $address['shipping']);
            frame::_()->getModule('order')->setToCurrent('billing_address', $address['billing']);
            $totalData = frame::_()->getModule('checkout')->calculate(array('all'), array('shippingMod' => $d['shipping_module']));
            $taxes = empty($totalData['taxrate']) ? '' : utils::jsonEncode($totalData['taxrate']);
            $user = wp_get_current_user();
            if(!empty($user) && !empty($user->ID))
                $uid = $user->ID;
            else
                $uid = 0;
            $shippingModuleData = frame::_()->getModule('shipping')->getModel()->get($d['shipping_module']);
            if(empty($shippingModuleData))
                $shippingModuleData = '';
            else {
                $shippingModuleData['cost'] = $totalData['shipping'];
                $shippingModuleData = mysql_real_escape_string(utils::jsonEncode($shippingModuleData));
            }
            $sqlData = array(
                'shipping_address' => utils::jsonEncode( $address['shipping'] ),
                'billing_address' => utils::jsonEncode( $address['billing'] ),
                'shipping_module' => $shippingModuleData ,
                'payment_module' => $d['payment_module'],
                'taxes' =>  utils::jsonEncode($totalData['taxrate']),
                'user_id' => $uid,
                'sub_total' => (float)$totalData['subTotal'],     //Will be modified in next version
                'tax_rate' => (float)$totalData['taxrate']['rateTotal'],
                'total' => (float)$totalData['total'],
                'status' => frame::_()->getTable('orders')->getField('status')->default,
                'currency' => utils::jsonEncode(frame::_()->getModule('currency')->getDefault()),
                'comments' => $d['comments'],
				'date_created' => time(),
            );
            $dbRes = ($do == 'insert') ? 
                frame::_()->getTable('orders')->$do($sqlData) : 
                frame::_()->getTable('orders')->$do($sqlData, $currentID);
            if($dbRes) {
                if($do == 'insert') {
                    $currentID = db::lastID();
                    frame::_()->getModule('order')->setCurrentID($currentID);
                } else {
                    $this->deleteItems($currentID);
                }
                frame::_()->getModule('checkout')->clearCurrent();
                frame::_()->getModule('order')->clearCurrent();
                foreach($cart as $inCartId => $item) {
                    $product_params = array('options' => $item['options']);
                    $product_params = utils::jsonEncode($product_params);
                    $product_params = dispatcher::applyFilters('postOrderItemParams', $product_params, $item);
                    $sqlData = array(
                        'order_id' => $currentID,
                        'product_id' => $item['pid'],
                        'product_name' => $item['name'],
                        'product_sku' => $item['sku'],
                        'product_price' => $item['price'],
                        'product_qty' => $item['qty'],
                        'product_params' => $product_params,
                    );
                    frame::_()->getTable('orders_items')->insert($sqlData);
                }
                dispatcher::doAction('orderPost', $currentID);
            } else
                errors::push(frame::_()->getTable('orders')->getErrors());
        }
        return $currentID;
    }
    public function delete($d = array()) {
        frame::_()->getTable('orders')->delete($d);
        $this->deleteItems($d);
    }
    public function deleteItems($d = array()) {
        if(is_numeric($d) && $d) {
            frame::_()->getTable('orders_items')->delete(array('order_id' => $d));
        } else {
            frame::_()->getTable('orders_items')->delete($d);
        }
    }
    public function prePostValidate($d = array()) {
        $res = new response();
        $address = $this->_getAddresses($d);
        $address['shipping']['useFor'] = 'shipping';
        $address['billing']['useFor'] = 'billing';
		$shippingSameAsBilling = (bool) req::getVar('shippingSameAsBilling');
        if($shippingAddrErrors = frame::_()->getModule('options')->getModel('userfields')->validate($address['shipping'])) {
            $errors = array();
            foreach($shippingAddrErrors as $key => $e) {
				$errKey = $shippingSameAsBilling ? 'billing_' : 'shipping_';
				$errKey .= $key;
                $errors[$errKey] = lang::_('Shipping Address: '). $e;
            }
            $res->addError($errors);
        }
        if($billingAddrErrors = frame::_()->getModule('options')->getModel('userfields')->validate($address['billing'])) {
            $errors = array();
            foreach($billingAddrErrors as $key => $e) {
				$errKey = 'billing_'. $key;
                $errors[$errKey] = lang::_('Billing Address: '). $e;
            }
            $res->addError($errors);
        }
        if(frame::_()->getModule('options')->getModel()->get('shipping_mandatory') && empty($d['shipping_module'])) {
            $res->addError(lang::_('You must select Shipping Module before continue'), 'shipping_module');
        }
        if(frame::_()->getModule('options')->getModel()->get('payment_mandatory') && empty($d['payment_module'])) {
            $res->addError(lang::_('You must select Payment Module before continue'), 'payment_module');
        }
		$steps = frame::_()->getModule('checkout')->getSteps();
        if(!$steps['terms']['disable'] && empty($d['checkTerms'])) {
            $res->addError(lang::_('You must read Terms and Conditions before continue'), 'checkTerms');
        }
       /* if(!empty($d['shipping_module']) && frame::_()->getModule('shipping')->moduleActive($d['shipping_module'])) {
                frame::_()->getModule('shipping')->getEngineFor($d['shipping_module'])->getRate(array_merge($d, $address));
                if(frame::_()->getModule('shipping')->getEngineFor($d['shipping_module'])->allowShipping() == false) {
                    $res->addError( frame::_()->getModule('shipping')->getEngineFor($d['shipping_module'])->getErrors() );
                }
        }*/
		if(!$this->validateShippingModule($d, $address))
			$res->pushError( $this->getErrors() );
        $res = dispatcher::applyFilters('orderPrePostValidate', $res);
        return $res;
    }
	public function validateShippingModule($d = array(), $address = array()) {
		if(!empty($d['shipping_module']) && frame::_()->getModule('shipping')->moduleActive($d['shipping_module'])) {
			frame::_()->getModule('shipping')->getEngineFor($d['shipping_module'])->getRate(array_merge($d, $address));
			if(frame::_()->getModule('shipping')->getEngineFor($d['shipping_module'])->allowShipping() == false) {
				$this->pushError( frame::_()->getModule('shipping')->getEngineFor($d['shipping_module'])->getErrors() );
				return false;
			}
        }
		return true;
	}
    /**
     * Retrives fields from input for payment and shipping
     * @param array $d input data
     * @return array array with 2 keys: shipping - shipping address, billing - billing address
     */
    protected function _getAddresses($d = array()) {
        /*'' => utils::jsonEncode( $address['shipping'] ),
                'billing_address'*/
        $res = array(
            'shipping' => array(),
            'billing' => array(),
            /*Next 2  keys are used for combination with data keys in order array*/
            'shipping_address' => array(),
            'billing_address' => array(),
        );
        $shippingFields = frame::_()->getModule('user')->getModel()->getUserMeta(0, 'shipping');
        $billingFields = frame::_()->getModule('user')->getModel()->getUserMeta(0, 'billing');
        if(!empty($shippingFields)) {
            foreach($shippingFields as $f) {
                $res['shipping'][$f->name] = $d['shipping_'. $f->name];
            }
        }
        if(!empty($billingFields)) {
            foreach($billingFields as $f) {
                $res['billing'][$f->name] = $d['billing_'. $f->name];
            }
        }
        $res['shipping_address'] = $res['shipping'];
        $res['billing_address'] = $res['billing'];
        return $res;
    }
	/**
	 * Public alias for _getAddresses() method
	 */
	public function getAddresses($d = array()) {
		return $this->_getAddresses($d);
	}
    public function get($d = array()) {
        global $wpdb;
        $data = array();
        $orders = array();
        $query = 'SELECT o.*, oi.order_id, oi.product_id, oi.product_name, oi.product_sku, oi.product_price, oi.product_qty, oi.product_params, u.user_email
                FROM @__orders o 
                INNER JOIN @__orders_items oi ON oi.order_id = o.id
                LEFT JOIN '. $wpdb->users. ' u ON u.ID = o.user_id';
        if(is_numeric($d)) {    //Get by Order ID
           $query .= ' WHERE o.id = "'. (int)$d. '"';
           $data = db::get($query);
           if($data) {
               $orders = $this->recordFromDB($data, $data[0]['order_id']);
           }
        } else {
            if($d) {
                $w = frame::_()->getTable('orders')->_getQueryString($d);
                if($w) {
                    $query .= ' WHERE '. $w;
                }
            }
            $data = db::get($query);
            
            foreach($data as $d) {
                if(!isset($orders[$d['order_id']]))
                    $orders[$d['order_id']] = $this->recordFromDB($data, $d['order_id']);
            }
        }
        return $orders;
    }
    protected function _extractProduct($item) {
        $fields = frame::_()->getTable('orders_items')->getFields();
        $prod = array();
        foreach($fields as $f) {
            $prod[$f->name] = $item[$f->name];
            if($f->name == 'product_params') {
                if(empty($item[$f->name]))
                    $prod[$f->name] = array();
                else 
                    $prod[$f->name] = utils::jsonDecode ($item[$f->name]);
            }
        }
        return $prod;
    }
    public function recordFromDB($d, $oid) {
        $record = array();
        foreach($d as $item) {
            if($item['order_id'] == $oid) {
                if(empty($record)) {
                    $record = $item;
                    $record['products'] = array();
                }
                $record['products'][] = $this->_extractProduct($item);
            }
        }
		
        if(empty($record['shipping_address']))
            $record['shipping_address'] = array();
        else
            $record['shipping_address'] = toeMultArrayMap('stripslashes', utils::jsonDecode($record['shipping_address']));
        if(empty($record['billing_address']))
            $record['billing_address'] = array();
        else
            $record['billing_address'] = toeMultArrayMap('stripslashes', utils::jsonDecode($record['billing_address']));
        $record['currency'] = $this->_extractCurrency($record['currency']);
        $record['shipping_module'] = utils::jsonDecode($record['shipping_module']);
        return $record;
    }
    protected function _extractCurrency($currency) {
        if(empty($currency))
            return frame::_()->getModule('currency')->getDefault();
        elseif(is_string($currency))
            return (array)json_decode($currency);
        else
            return $currency;
    }
    public function put($d = array()) {
        $id = isset($d['id']) ? intval($d['id']) : NULL;
        if($id) {
            $sqlData = array();
            $fields = frame::_()->getTable('orders')->getFields();
            foreach($fields as $name => $f) {
                if(isset($d[$name])) {
					switch($name) {
						case 'shipping_address':
						case 'billing_address':
						case 'shipping_module':
							$sqlData[$name] = is_array($d[$name]) ? 
								mysql_real_escape_string(utils::jsonEncode($d[$name])) :
								$d[$name];
							break;
						default:
								$sqlData[$name] = $d[$name];
							break;
					}
                }
            }
            if($sqlData) {
				$oldData = $this->get( $id );
                frame::_()->getTable('orders')->update($sqlData, $id);
				$newData = $this->get( $id );
				$this->_fillAudit($oldData, $newData);
                if(req::getVar('notifyCustomer')) {
					$order = $this->get( $id );	// Data after changes
					if(isset($order['user_id']) && !empty($order['user_id']))
						$order['email'] = get_the_author_meta('user_email', $order['user_id']);
                    frame::_()->getModule('order')->getController()->sendNotification($order);
				}
                return true;
            }
        }
        return false;
    }
	protected function _fillAudit($oldData, $newData) {
		$userMeta = null;
		$keyToName = array(
			'sub_total' => lang::_('Sub total'),
			'tax_rate' => lang::_('Tax Rate'),
			'total' => lang::_('Total'),
			'status' => lang::_('Status'),
		);
		foreach($oldData as $k => $v) {
			$auditData = array('oid' => $newData['id']);
			if($oldData[ $k ] != $newData[ $k ]) {
				switch($k) {
					case 'shipping_address':
					case 'billing_address':
						if(is_array($oldData[ $k ]) && is_array($newData[ $k ])) {
							if(empty($userMeta))
								$userMeta = frame::_()->getModule('user')->getModel()->getMeta();
							foreach($oldData[ $k ] as $addrK => $addrV) {
								if($oldData[ $k ][ $addrK ] != $newData[ $k ][ $addrK ]) {
									if(isset($userMeta[ $addrK ])) 
										$auditData['fieldName'] = lang::_($userMeta[ $addrK ]->label);
									else
										$auditData['fieldName'] = $addrK;
									$auditData['fieldName'] = $k == 'shipping_address' ? 
										lang::_('Shipping address - '). $auditData['fieldName'] :
										lang::_('Billing address - '). $auditData['fieldName'];
									switch($addrK) {
										case 'country':
											$auditData['from'] = fieldAdapter::displayCountry( $oldData[ $k ][ $addrK ] );
											$auditData['to'] = fieldAdapter::displayCountry( $newData[ $k ][ $addrK ] );
											break;
										case 'state':
											$auditData['from'] = fieldAdapter::displayState( $oldData[ $k ][ $addrK ] );
											$auditData['to'] = fieldAdapter::displayState( $newData[ $k ][ $addrK ] );
											break;
										default:
											$auditData['from'] = $oldData[ $k ][ $addrK ];
											$auditData['to'] = $newData[ $k ][ $addrK ];
											break;
									}
									$this->_addAuditChangeMessage($auditData);
								}
							}
						}
						break;
					case 'sub_total':
					case 'tax_rate':
					case 'total':
					case 'status':
						$auditData['fieldName'] = $keyToName[ $k ];
						$auditData['from'] = $oldData[ $k ];
						$auditData['to'] = $newData[ $k ];
						$this->_addAuditChangeMessage($auditData);
						break;
					case 'shipping_module':
						if(isset($oldData[ $k ]['cost']) && isset($newData[ $k ]['cost']) && ($oldData[ $k ]['cost'] != $newData[ $k ]['cost'])) {
							$auditData['fieldName'] = lang::_('Shiiping price');
							$auditData['from'] = $oldData[ $k ]['cost'];
							$auditData['to'] = $newData[ $k ]['cost'];
							$this->_addAuditChangeMessage($auditData);
						}
						break;
				}
			}
			if(!empty($msg)) {
				
			}
		}
	}
	private function _addAuditChangeMessage($d = array('fieldName' => '', 'from' => '', 'to' => '', 'msg' => '', 'oid' => '')) {
		if(empty($d['msg'])) {
			if(empty($d['from'])) {
				$d['msg'] = $d['fieldName']. lang::_(' was set to '). $d['to'];
			} elseif(empty($d['to'])) {
				$d['msg'] = $d['fieldName']. lang::_(' was cleaned from '). $d['from'];
			} else {
				$d['msg'] = $d['fieldName']. lang::_(' was changed from '). $d['from']. lang::_(' to '). $d['to'];
			}
		}
		if(!empty($d['msg'])) {
			frame::_()->getModule('log')->getModel()->post(array('type' => 'order', 'data' => $d['msg'], 'oid' => $d['oid']));
		}
	}
    public function getLastID() {
        return frame::_()->getTable('orders')->getLastInsertID();
    }
    public function getSuccessVariables($params = array('order' => NULL, 'getKeysOnly' => false)) {
        if($params['getKeysOnly'])
            return array(
                'user_name',
                'order_info',
                'store_name',
                'order_id',
                'account_login_link',
                'account_downloads_link',
            );
        if(empty($params['order']))
            $params['order'] = frame::_()->getModule('order')->getCurrent();
        if(!function_exists('get_user_by'))
            frame::_()->loadPlugins ();
        $user = get_user_by('id', $params['order']['user_id']);
        $userName = $user ? $user->data->display_name : '';
        $order_info = frame::_()->getModule('order')->getView('order')->showOne($params['order']['order_id'], array('canEdit' => false));
        $store_name = frame::_()->getModule('options')->getModel('options')->get(array('code'=>'store_name'));
        return array(
            'user_name' => $userName,
            'order_info' => $order_info,
            'store_name' => $store_name,
            'order_id' => $params['order']['order_id'],
            'account_login_link' => frame::_()->getModule('pages')->getLink(array('mod' => 'user', 'action' => 'getAccountSummaryHtml')),
            'account_downloads_link' => frame::_()->getModule('pages')->getLink(array('mod' => 'digital_product', 'action' => 'getDownloadsList')),
        );
    }
	public function userHaveOrders($uid = 0) {
		$res = false;
		if($uid) {
			$res = (bool)frame::_()->getTable('orders')->exists($uid, 'user_id');
		}
		return $res;
	}
	public function remove($d = array()) {
		$d['id'] = isset($d['id']) ? (int) $d['id'] : 0;
		if($d['id']) {
			frame::_()->getTable('orders')->delete($d['id']);
			$this->deleteItems($d['id']);
			frame::_()->getTable('log')->delete(array('oid' => $d['id']));
			return $d['id'];
		} else
			$this->pushError (lang::_('Invalid Order ID'));
		return false;
	}
}
?>