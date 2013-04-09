<?php
class checkout extends module {
    protected $_total = 0;
    protected $_subTotal = 0;
    protected $_shippingCost = 0;
    protected $_taxRates = array();
    protected $_gifts = array();
    protected $_taxRatesCalculated = false;
    protected $_steps = array(
        'loginForm'			=> array('title' => 'Login Form',				'sortOrder' => 0, 'disable' => false),
        'registrationForm'	=> array('title' => 'Registration Form',		'sortOrder' => 0, 'disable' => false),
        'billingAddress'	=> array('title' => 'Billing Address',			'sortOrder' => 0, 'disable' => false),
		'shippingAddress'	=> array('title' => 'Shipping Address',			'sortOrder' => 0, 'disable' => false),
        'paymentModules'	=> array('title' => 'Payment',					'sortOrder' => 0, 'disable' => false),
        'terms'				=> array('title' => 'Terms and Conditions',		'sortOrder' => 0, 'disable' => true),
		'shippingModules'	=> array('title' => 'Shipping',					'sortOrder' => 0, 'disable' => false),
        'summaryBlock'		=> array('title' => 'Cart Summary',				'sortOrder' => 0, 'disable' => false),
        'totalBlock'		=> array('title' => 'Totals',					'sortOrder' => 0, 'disable' => false),
        'comments'			=> array('title' => 'Comments',					'sortOrder' => 0, 'disable' => false)
    );
    protected $_stepsDetected = false;
    
    public function getSteps() {
        if(!$this->_stepsDetected)
            $this->_detectSteps();
        return $this->_steps;
    }
    protected function _detectSteps() {
        $this->_stepsDetected = true;
        $fromDb = frame::_()->getModule('options')->get('checkout_steps');
        $fromDb = utils::jsonDecode($fromDb);
        if(!empty($fromDb)) {
            $current = $this->_steps;
            if(!empty($fromDb['sortOrder'])) {
				$oldSteps = $this->_steps;
                $this->_steps = array();
                foreach($fromDb['sortOrder'] as $i => $key) {
                    $this->_steps[$key] = $current[$key];
                    $this->_steps[$key]['sortOrder'] = (int) $i;
                }
				foreach($oldSteps as $key => $val) {
					if(!isset($this->_steps[$key]))
						$this->_steps[$key] = $val;
				}
            }
            $current = $this->_steps;
            if(!empty($fromDb['disable'])) {
                foreach($fromDb['disable'] as $key => $v) {
                    $this->_steps[$key]['disable'] = true;
                }
            }
			// All fields that are not in disable array - should be enabled if they are not disabled by default
			if(!empty($fromDb['sortOrder'])) {
				foreach($fromDb['sortOrder'] as $i => $key) {
					if(!(!empty($fromDb['disable']) && is_array($fromDb['disable']) && isset($fromDb['disable'][$key]))) {
						$this->_steps[$key]['disable'] = false;
					}
				}
			}
        }
    }
    /**
     * Recalculates and return all data in one array
     */
    public function calculate($include = array('all', 'subtotal', 'total', 'shipping', 'taxrate', 'gifts'), $params = array('shippingMod' => '')) {
        if(empty($include))
            $include = array('all', 'subtotal', 'total', 'shipping', 'taxrate', 'gifts');
        $data = array();
        if(in_array_array(array('all', 'subtotal'), $include))
            $data['subTotal'] = $this->getSubTotal();
         if(in_array_array(array('all', 'gifts'), $include))
            $data['gifts'] = $this->getGifts();
        if(in_array_array(array('all', 'shipping'), $include))
            $data['shipping'] = $this->getShippingCost($params['shippingMod']);
        if(in_array_array(array('all', 'taxrate'), $include))
            $data['taxrate'] = $this->getTaxRate(array('subTotal' => $data['subTotal']));
        if(in_array_array(array('all', 'total'), $include))
            $data['total'] = $this->getTotal($include, $params);
        return $data;
    }
    /**
     * Count all taxes, discounts, etc.
     */
    public function getTotal($include = array('all', 'subtotal', 'total', 'shipping', 'taxrate', 'gifts'), $params = array('shippingMod' => '')) {
        if(empty($include))
            $include = array('all', 'subtotal', 'total', 'shipping', 'taxrate');
        $total = 0;
        if(empty($this->_total)) {
            if(in_array_array(array('all', 'subtotal'), $include))
                $total += $this->getSubTotal();
            if(in_array_array(array('all', 'gifts'), $include)) {
                $this->getGifts();
                if(!empty($this->_gifts['total']))
                    $total -= $this->_gifts['total'];
            }
            if(in_array_array(array('all', 'shipping'), $include))
                $total += $this->getShippingCost($params['shippingMod']);
            if(in_array_array(array('all', 'taxrate'), $include))
                $total += $this->getTaxRate(array('getTotal' => true));
            $this->_total = $total;
        } else
            $total = $this->_total;
        return $total;
    }
    /**
     * Calculate price for products in cart
     */
    public function getSubTotal() {
        if(empty($this->_subTotal)) {
            $cart = frame::_()->getModule('user')->getModel('cart')->get();
            if($cart) {
                foreach($cart as $pid => $c) {
                    $this->_subTotal += frame::_()->getModule('currency')->calculateTotal($c['price'], $c['qty'], $c['pid'], array('options' => $c['options']));
                    //$this->_subTotal += (float)$c['price'] * (float)$c['qty'];
                }
            }
        }
        return $this->_subTotal;
    }
    public function getGifts() {
        //At first - check if gifts module exist
        if(empty($this->_gifts) && frame::_()->getModule('gifts')) {  
            $this->_gifts = frame::_()->getModule('gifts')->getGifts();
        }
        return $this->_gifts;
    }
    public function getShippingCost($modId = '') {
        if(!empty($this->_gifts['data'])) {
            foreach($this->_gifts['data'] as $g) {
                if($g['type'] == 'shipping')    //Shipping is presented
                    return 0;
            }
        }
		$have_shipping_items = false;
		$cart_items = frame::_()->getModule('user')->getCartItems();
		foreach($cart_items as $product) {
				if(!frame::_()->getTable('product_files')->exists($product['pid'], 'pid'))
				$have_shipping_items = true;
            }
        if(empty($this->_shippingCost)) {
            if(empty($modId)) {
                $order = frame::_()->getModule('order')->getCurrent();
                $modId = $order['shipping_module'];
            }
            if(!empty($modId)) {
                $active = false;
                if(is_numeric($modId)) {
                    $active = frame::_()->getModule('shipping')->moduleActive($modId);
                } elseif(is_string($modId)) {
                    $modData = utils::jsonDecode($modId);
                    $modId = $modData['id'];
                    $active = (bool) $modData['active'];
                } elseif(is_array($modId)) {
                    $modData = $modId;
                    $modId = $modData['id'];
                    $active = (bool) $modData['active'];
                }
                if($active && frame::_()->getModule('shipping')->getEngineFor($modId)) {
                    $this->_shippingCost = frame::_()->getModule('shipping')->getEngineFor($modId)->getRate( frame::_()->getModule('order')->getCurrent() );
                }
            }
        }
		if($have_shipping_items)
			return $this->_shippingCost;
		else
		{
			$this->_shippingCost = 0;
			return 0;
		}
    }
    private function _addrFromFieldsToStd($type, $order = array()) {
        if(empty($order))
            $order = frame::_()->getModule('order')->getCurrent();
        $addr = frame::_()->getModule('user')->getModel()->getUserMeta(0, $type, $order);
        $data = new stdClass();
        if(!empty($addr)) {
            foreach($addr as $key => $val) {
                $data->$key = $val->value;
            }
        }
        return $data;
    }
    public function getTaxRate($d = array()) {
        if($this->_taxRatesCalculated) {
            if($d['getTotal'])
                return (float)$this->_taxRates['rateTotal'];
            return $this->_taxRates;
        }
        $ratesData = array();
        $rateTotal = 0;
        $allTaxes = frame::_()->getModule('taxes')->get();
        $cart = frame::_()->getModule('user')->getModel('cart')->get();
        $order = NULL;
        $userAddresses = array();
        $allUserFields = frame::_()->getModule('options')->getModel('userfields')->get();
        if(!empty($allTaxes)) {
            $subTotal = (float)$d['subTotal'];
            foreach($allTaxes as $t) {
                if(empty($t['data']['dest']))
                    $t['data']['dest'] = array();
                $priceToApply = 0;
                $absolute = (bool)$t['data']['absolute'];
                $data = $t['data'];
                $applyForTotal = true;
                /* Apply tax for each category in tax list */
                if((isset($data['categories']) && !empty($data['categories']) && is_array($data['categories'])) || (isset($data['selectAllCategories']) && $data['selectAllCategories'])) {
                    $applyForTotal = false;
                    $apliedForProducts = array();   //Prevent apply tax for one product twice
                    foreach($cart as $pid => $prod) {
                        if(isset($apliedForProducts[$pid])) continue;
                        if(is_array($prod['categories'])) {
							if($data['selectAllCategories']) {
								$priceToApply += $prod['price'] * $prod['qty'];
								$apliedForProducts[$pid] = 1;
								continue;
							}
                            foreach($t['data']['categories'] as $cid) {
                                if(isset($prod['categories'][(int)$cid]) && !empty($prod['categories'][(int)$cid])) {
                                    $priceToApply += $prod['price'] * $prod['qty'];
                                    $apliedForProducts[$pid] = 1;
                                    break;
                                }
                            }
                        }
                    }
                }
                /* Userfields check - if any userfield are in tax */
                $userfieldsForApply = array();
                foreach($allUserFields as $uf) {
                    if(isset($data[ $uf['code'] ]) && !empty($data[ $uf['code'] ])) {
                        $userfieldsForApply[] = $uf;
                    }
                }
                if(!empty($userfieldsForApply)) {
                    $applyForTotal = false;
					$priceForShipping = 0;
                    if(in_array('registration', $t['data']['dest'])) {
                        if(empty($userAddresses['registration']))
                            $userAddresses['registration'] = frame::_()->getModule('user')->getCurrent();
                    }
                    if(in_array('shipping', $t['data']['dest'])) {
                        if(empty($order))
                            $order = frame::_()->getModule('order')->getCurrent();
                        if(empty($userAddresses['shipping'])) 
                            $userAddresses['shipping'] = $this->_addrFromFieldsToStd('shipping', $order);
                    }
                    if(in_array('billing', $t['data']['dest'])) {
                        if(empty($order))
                            $order = frame::_()->getModule('order')->getCurrent();
                        if(empty($userAddresses['billing'])) 
                            $userAddresses['billing'] = $this->_addrFromFieldsToStd('billing', $order);
                    }
					/*if(empty($priceToApply))
						$priceForAddress = $subTotal;
					else
						$priceForAddress = $priceToApply;*/
                    /* If this is enabled - we will calculate tax including shipping cost, again - if shipping is available */
                    if($t['data']['applyToShipping']) {
                        if(empty($order))
                            $order = frame::_()->getModule('order')->getCurrent();
                        if(!empty($order['shipping_module'])) {
                            $priceForShipping = $this->getShippingCost($order['shipping_module']);
                        }
                    }
                    $applyForAddressUserfieldsCounter = 0;
                    foreach($userfieldsForApply as $uf) {
                        foreach($userAddresses as $dest => $user) {
                            if(!in_array($dest, $t['data']['dest'])) continue;
                            if((is_array($data[ $uf['code'] ]) 
									&& (in_array($userAddresses[$dest]->{$uf['code']}, $data[ $uf['code'] ])) 
											|| (in_array($uf['code'], array('country', 'state')) && $data[ $uf['code'] ] == array(0))) 
							|| (is_string($data[ $uf['code'] ])	
									&& strtolower($data[ $uf['code'] ]) == strtolower($userAddresses[$dest]->{$uf['code']}))
							) {
                                $applyForAddressUserfieldsCounter++;
                                        //$priceToApply += $priceForAddress;
                                break;
                            }
                        }
                    }
                    if($applyForAddressUserfieldsCounter == count($userfieldsForApply)) {
						if(empty($priceToApply)) {	// Apply NOT for categories
							$priceToApply = $subTotal;
						}
                        $priceToApply += $priceForShipping;
					} else
						$priceToApply = 0;
                }
                if($applyForTotal) {
                    $priceToApply = $subTotal;
                }
                if($priceToApply) {
                    $rate = $t['data']['absolute'] ? (float)$t['data']['rate'] : $priceToApply * (float)$t['data']['rate'] / 100;
                    if($rate) {
                        $ratesData[] = array(
                            'label' => $t['label'],
                            'rate' => $rate,
                            'absolute' => $t['data']['absolute']);
                        $rateTotal += $rate;
                    }
                }
            }
        }
        //if(!empty($ratesData)) {      //Maybe it is incorrect.....
            if($rateTotal)
                $ratesData['rateTotal'] = $rateTotal;
            $this->_taxRates = $ratesData;
            $this->_taxRatesCalculated = true;
        //}
        if($d['getTotal']) return $rateTotal;
        return empty($ratesData) ? NULL : $ratesData;
    }
    public function clearCurrent() {
        $this->_total = 0;
        $this->_subTotal = 0;
        $this->_shippingCost = 0;
        $this->_taxRates = array();
        $this->_taxRatesCalculated = false;
    }
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    public function getTabs(){
        $tabs = array();
        $tab = new tab(lang::_('Checkout'), $this->getCode());
        $tab->setView('checkoutTab');
		$tab->setSortOrder(6);
		$tab->setParent('templates');
		$tab->setNestingLevel(1);
        $tabs[] = $tab;
        return $tabs;
    }
}
?>