<?php
    class productsController extends controller {
        protected $_currentPostArr = array();
        public function saveProduct($post_ID, $post) {
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return false;
            if(!current_user_can('edit_post', $post_ID)) return false;
            if(!$post_ID) return false;
            if(empty($this->_currentPostArr)) return false;     //nothing to update
            if($post->post_type != S_PRODUCT) return false;
            if(frame::_()->getTable('products')->exists($post_ID))
                $method = 'put';
            else
                $method = 'post';
            if(empty($this->_currentPostArr['ID']))
                $this->_currentPostArr['ID'] = $post_ID;
            if(empty($this->_currentPostArr['post_id']))
                $this->_currentPostArr['post_id'] = $post_ID;
            //$this->_currentPostArr['views']; //for now
            $this->getModel('products')->$method( $this->_currentPostArr );
            $this->saveProductExtraField( $this->_currentPostArr );
            $this->_currentPostArr = array();   //Ready for next cycle - if need
            return true;
        }
        public function savePostData($data , $postarr) {
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $data;
            if($data['post_type'] != S_PRODUCT)  return $data;
            if(empty($this->_currentPostArr)) {     //Only one data may pass through cycle of this two methods - savePostData() and saveProduct()
				$this->_currentPostArr = $postarr;
            }
            return $data;
        }
       /**
        * Saves Extra Fields to Database
        * @param array $post 
        */
        public function saveProductExtraField($post_data, $exPostId = NULL) {
            global $post;
            if(empty($post) && !empty($exPostId))
                $post = $exPostId;
            //I don't know what this code do, realy, but leave it
            $metaFields = frame::_()->getModule('options')->getModel('productfields')->getProductExtraField($post);
            //frame::_()->getModule('options')->getModel('extrafieldsvalue')->saveExtraFields($post_data, $metaFields, $post_data['post_ID'], 'products');
            //Below we will save exclude products for ex. options
            if(!empty($post_data['exVal']) && is_array($post_data['exVal'])) {
                $deleteOpts = implode(',', array_keys($post_data['exVal']));
                frame::_()->getTable('extrafieldsvalue')->delete('parent_id = '. $post_data['post_ID']. ' AND parent_type = "'. S_PRODUCT. '" AND opt_id IN ('. $deleteOpts. ')');
                foreach($post_data['exVal'] as $optId => $optData) {
                    $dbData = array(
                        'parent_id' => $post_data['post_ID'],
                        'parent_type' => S_PRODUCT,
                        'ef_id' => $post_data['exValuesToFields'][ $optId ],
                        'opt_id' => $optId,
                        'price' => $optData['price'],
                        'price_absolute' => $optData['price_absolute'],
                        'disabled' => $optData['disabled'],
                        'value' => '',  //can't be NULL
                    );
                    frame::_()->getTable('extrafieldsvalue')->insert($dbData);
                }
            }
        }
        /**
         * Add the fields that are specific to added category
         */
        public function addCategoryFields() {
            $res = new response();
            $post = req::get('post');
            $id = $post['id'];
            $cats = array_unique($post['cats']);
            if (!is_numeric($id)) {
                $res->html = new errors('There is no such category in the system','Invalid category');
            }
            $fields = frame::_()->getModule('options')->getModel('productfields')->get();
            $result = '';
            if (!empty($fields)) {
                foreach ($fields as $f) {
                    $destination = utils::jsonDecode($f['destination']);
                    if (empty($destination)) {
                        continue;
                    }
                    $common_cats = array_intersect($destination, $cats);
                    $intersect = false;
                    if (count($common_cats) == 1 and $common_cats[0] == $id) {
                        $intersect = true;
                    }
                    if (in_array($id, $destination) && !in_array(0, $destination) && $intersect) {
                        $item = new field($f['code'], $f['type'], 'other', '', $f['label']);
                        $item->id = $f['id'];
                        $item->mandatory = $f['mandatory'];
                        $item->destination = $destination;
                        $item->htmlParams = (array)json_decode($f['params']);
                        $item->default_value = $f['default_value'];
                        $output = '<div class="product_extra"><label for="'.$item->name.'">'.$item->label.':</label>';
                        $result .= $output.'<div class="product_field">'.$item->viewField($f['code']).'</div><br clear="all" /></div>';
                    }
                } 
            }
            $res->html = $result;
            $res->ajaxExec();
        }
        /**
         * Delete the fields of a specific category
         */
        public function deleteCategoryFields(){
           $res = new response();
           $post = req::get('post');
           $id = $post['id']; 
           if (!is_numeric($id)) {
                $res->html = new errors('There is no such category in the system','Invalid category');
           }
           $fields = frame::_()->getModule('options')->getModel('productfields')->get();
           $result = array();
            if (!empty($fields)) {
                foreach ($fields as $f) {
                    $destination = utils::jsonDecode($f['destination']);
                    if (empty($destination)) {
                        continue;
                    }
                    if (count($destination) == 1 && $destination[0] == $id) {
                        $result[] = $f['code'];
                    }
                }  
            }
           $res->data = $result;
           $res->ajaxExec();
        }
        
        public function updateProductMedia() {
           $res = new response();
           $post = req::get('post');
           $id = $post['id']; 
           if (!is_numeric($id)) {
                $res->html = new errors('There is no such category in the system','Invalid category');
           }
           $result = frame::_()->getModule('products')->getView('admin_Products')->updateMediaFiles($id);
           $res->html = $result;
           $res->ajaxExec();
        }
        public function showPrice($pid, $price) {
            return $this->getView()->getPrice($pid, $price);
        }
        public function setImgStatus() {
            $res = new response();
            $parentId = (int) req::getVar('parent_id');
            $postId = (int) req::getVar('post_id');
            $status = req::getVar('status');
            if($parentId && $postId && $status) {
                frame::_()->getTable('img_status')->delete(array(
                    'parent_id' => $parentId,
                    'post_id' => $postId,
                ));
                if($status != 'all') {      //Just do nothing - it will not be in table, all is default value
                    frame::_()->getTable('img_status')->insert(array(
                        'parent_id' => $parentId,
                        'post_id' => $postId,
                        'status' => $status,
                    ));
                }
            } else 
                $res->pushError(lang::_('Invalid data was specified'));
            
            return $res->ajaxExec();
        }
		public function saveImagesSortOrder() {
			global $wpdb;
			$res = new response();
			$newSortOrder = req::getVar('newSortOrder');
			if(!empty($newSortOrder)) {
				$newSortOrder = explode(',', $newSortOrder);
				if(is_array($newSortOrder)) {
					foreach($newSortOrder as $i => $imgId) {
						db::query('UPDATE '. $wpdb->posts. ' SET menu_order = '. (int)$i. ' WHERE ID = '. (int)$imgId. ' LIMIT 1');
					}
				}
			}
			return $res->ajaxExec();
		}
		public function getCategoriesListHtml() {
			add_filter('the_content', array($this->getView(), 'getCategoriesListHtml'));
		}
		public function getBrandsListHtml() {
			add_filter('the_content', array($this->getView(), 'getBrandsListHtml'));
		}
		public function getAllProductsListHtml() {
			add_filter('the_content', array($this->getView(), 'getAllProductsListHtml'));
		}
    }
?>