<?php
class admin_ProductsView extends view {
    public function display($tpl = '') {
        $products = frame::_()->getModule('products')->getController()->getModel('products')->get();
        $this->assign('products', $products);
        parent::display('adminProducts');
    }
    /**
     * Adding extra blocks to product page
     */
    public function addMetaBoxes() {
        add_action('add_meta_boxes', array($this, 'addPlugFields'));
        add_action('add_meta_boxes', array($this, 'addProductMedia'));
		if(frame::_()->getModule('special_products'))
			add_action('add_meta_boxes', array($this, 'addSpecialPricesBox'));
    }
	public function addSpecialPricesBox() {
		add_meta_box('toeProductSpecialPrices', 
				lang::_('Product\'s Special Prices'), 
				array(frame::_()->getModule('special_products')->getView(), 'addProductMetaFields'), 
				S_PRODUCT, 
				'side');
	}
    /**
     * Adding product extra fields
     */
    public function addPlugFields() {
        add_meta_box('toeProductMeta', lang::_('Product\'s Data'), array($this, 'addMetaFields'), S_PRODUCT, 'advanced', 'high');
    }
    /**
     * Rendering the product extra fields block
     * 
     * @global object $post 
     */
    public function addMetaFields() {
        global $post;
        $fields = frame::_()->getModule('products')->getController()->getModel('products')->get($post);
		$fields = dispatcher::applyFilters('prodFieldsEdit', $fields);
        //$extra_values = frame::_()->getModule('options')->getModel('productfields')->getProductExtraFieldValue($post);
        $extraFields = frame::_()->getModule('options')->getModel('productfields')->getProductExtraField($post);
        $extraFieldsMultiple = array();
        $exFieldsDisable = array();
        $exFieldsEfVals = array();
        if(!empty($extraFields)) {
            $exIds = array();
            foreach($extraFields as $k => $f) {
                $exIds[ $f->getID() ] = 1;
                if($f->getHtml() == 'text') {
                    $f->setHtml('checkbox');    //You will be able just to disable it
                } else {
                    $f->setHtml('selectlist');  //You will be able select list of params for this product
                }
            }
            $exFieldsDesc = frame::_()->getModule('options')->getModel('productfields')->getFieldsDesc(array('options' => $exIds, 'pid' => $post->ID, 'includeDisabled' => true));
            foreach($extraFields as $k => $f) {
                $f->setName('exOptions['. $f->getID(). ']');
                if($f->getHtml() == 'checkbox') {
                    $checked = false;
                    $option = end($exFieldsDesc[ $f->getID() ]['opt_values']);
                    if(isset($option['excludePids']) && 
                            is_array($option['excludePids']) && 
                                   in_array($post->ID, $option['excludePids'])) {
                        
                        $checked = true;
                    }
                    $f->addHtmlParam('checked', $checked);
                    $f->setValue($option['id']);
                } else {
                    if(isset($exFieldsDesc[ $f->getID() ])) {
                        if(!empty($exFieldsDesc[ $f->getID() ]['opt_values'])) {
                            $selectListOptions = array();
                            $selectListValues = array();
                            foreach($exFieldsDesc[ $f->getID() ]['opt_values'] as $optVal) {
                                $selectListOptions[ $optVal['id'] ] = $optVal['value'];
                                
                                $exFieldsEfVals[ $optVal['id'] ]['price'] = toeCreateObj('field', array('exVal['. $optVal['id']. '][price]', 'hidden'));
                                $exFieldsEfVals[ $optVal['id'] ]['price']->setValue($optVal['price']);
                                $exFieldsEfVals[ $optVal['id'] ]['price_absolute'] = toeCreateObj('field', array('exVal['. $optVal['id']. '][price_absolute]', 'hidden'));
                                $exFieldsEfVals[ $optVal['id'] ]['price_absolute']->setValue($optVal['price_absolute']);
                                $exFieldsEfVals[ $optVal['id'] ]['disabled'] = toeCreateObj('field', array('exVal['. $optVal['id']. '][disabled]', 'hidden'));
                                $exFieldsEfVals[ $optVal['id'] ]['disabled']->setValue($optVal['disabled']);
                                
                                if(!empty($optVal['excludePids']) && is_array($optVal['excludePids']) && in_array($post->ID, $optVal['excludePids'])) {
                                    $selectListValues[] = $optVal['id'];
                                }
                            }
                            $f->addHtmlParam('options', $selectListOptions);
                            $f->addHtmlParam('attrs', 'class="toeExtraFieldsSelectList"');
                            //$f->setValue($selectListValues);
                        }
                    }
                }
                $exFieldsDisable[ $f->getID() ] = toeCreateObj('field', array('exFieldsDisable['. $f->getID(). ']'));
            }
        }
        $categories = frame::_()->getModule('products')->getModel('products')->getProductTerms($post);
        $this->assign('fields', $fields);
        $this->assign('categories', $categories);
        $this->assign('extraFieldsMultiple', $extraFields);
        $this->assign('exFieldsDisable', $exFieldsDisable);
        $this->assign('exFieldsEfVals', $exFieldsEfVals);
        $this->assign('extra_values', $extra_values);
        $this->assign('post', $post);
        parent::display('metaFields');
    }
    /**
     * Adding product media Files
     */
    public function addProductMedia() {
        add_meta_box('toeProductMedia', lang::_('Media Gallery'), array($this, 'addMediaFiles'), S_PRODUCT, 'advanced', 'high');
    }
    /**
     * Rendering product Media Gallery
     * 
     * @global object $post 
     */
    public function addMediaFiles(){
        global $post;
        $attachments = frame::_()->getModule('products')->getModel()->getImages(array('pid' => $post->ID));
        $result = array();
        foreach ($attachments as $item) {
            $image = wp_get_attachment_image_src($item->ID, 'full');
            $thumb = wp_get_attachment_image_src($item->ID, 'thumbnail');
            $result[$item->ID] = array(
                'image'=> $image,
                'thumb'=> $thumb,
                'status' => frame::_()->getTable('img_status')->getById($item->ID, 'status', 'one'),
            );
            if(empty($result[$item->ID]['status']))
                $result[$item->ID]['status'] = 'all';
        }
        $this->assign('media',$attachments);
        $this->assign('files',$result);
        $this->assign('url', $wp_upload_url);
        $this->assign('post', $post);
        $this->assign('imgStatuses', array('all', 'catt', 'catt_only'));
        $this->assign('imgStatusesOptions', array('all' => lang::_('Show everywhere'), 'catt' => lang::_('Show on Listing'), 'catt_only' => lang::_('Show on Listing only')));
        parent::display('mediaFiles');
    }
    
    /**
     * Get the list of the media Files 
     * @param int $post_id 
     */
    public function updateMediaFiles($post_id) {
        $post = get_post($post_id);
        $attachments = frame::_()->getModule('products')->getModel()->getImages(array('pid' => $post->ID));
        $result = array();
        foreach ($attachments as $item) {
            $image = wp_get_attachment_image_src($item->ID, 'full');
            $thumb = wp_get_attachment_image_src($item->ID, 'thumbnail');
            $result[$item->ID] = array(
                'image'=> $image,
                'thumb'=> $thumb,
                'status' => frame::_()->getTable('img_status')->getById($item->ID, 'status', 'one'),
            );      
            if(empty($result[$item->ID]['status']))
                $result[$item->ID]['status'] = 'all';
        }
        $this->assign('media',$attachments);
        $this->assign('files',$result);
        $this->assign('url', $wp_upload_url);
        $this->assign('post', $post);
        $this->assign('imgStatuses', array('all', 'catt', 'catt_only'));
        $this->assign('imgStatusesOptions', array('all' => lang::_('Show everywhere'), 'catt' => lang::_('Show on Listing'), 'catt_only' => lang::_('Show on Listing only')));
        return parent::getContent('mediaFiles');
    }
	/**
	 * Used to add additional fields to product categories data on admin side
	 */
	public function editProductsCategories($tag, $taxonomy) {
		$menus = wp_get_nav_menus();
		$menuItemsIds = array();
		if(!empty($menus)) {
			foreach($menus as $m) {
				$menuItems = wp_get_nav_menu_items($m->term_id);
				if(!empty($menuItems)) {
					foreach($menuItems as $mItem) {
						$menuItemsIds[] = $mItem->ID;
					}
				}
			}
		}
		$this->assign('category_thumb', get_metadata($tag->taxonomy, $tag->term_id, 'category_thumb', true));
        $this->assign('category_menu', get_metadata($tag->taxonomy, $tag->term_id, 'category_menu', true));
		$this->assign('sort_order', get_metadata($tag->taxonomy, $tag->term_id, 'sort_order', true));
		$this->assign('tag', $tag);
		$this->assign('menus', $menus);
		$this->assign('menuItemsIds', $menuItemsIds);
		//toeVarDump(wp_get_nav_menus(), wp_get_nav_menu_items(11));
		return parent::getContent('editProductsCategories');
	}
	/**
	 * Used to add additional fields to product brands data on admin side
	 */
	public function editProductsBrands($tag, $taxonomy) {
		$this->assign('tag', $tag);
		$this->assign('brand_thumb', get_metadata($tag->taxonomy, $tag->term_id, 'brand_thumb', true));
        $this->assign('brand_url', get_metadata($tag->taxonomy, $tag->term_id, 'brand_url', true));
		/*$this->assign('sort_order', get_metadata($tag->taxonomy, $tag->term_id, 'sort_order', true));
		$this->assign('menus', wp_get_nav_menus());*/
		return parent::getContent('editProductsBrands');
	}
}
?>
