<?php
class extrafieldsModel extends model {
    public function get($d = array(), $where = '', $parent = '') {
        parent::get($d);
        $res = array();
        $extrafields = frame::_()->getTable('extrafields');
        $htmltype = frame::_()->getTable('htmltype');
        $extraoptions = frame::_()->getTable('extraoptions');
        //$extraoptionsExclude = frame::_()->getTable('extraoptions_exclude');
        $extrafieldsvalue = frame::_()->getTable('extrafieldsvalue');
        if(isset($d['id']) && is_numeric($d['id'])) {
            if($d['id']) {
                $extrafields->fillFromDB($d['id'], $where);
            }
            $fields = $extrafields->getFields();
            $fields['mandatory']->addHtmlParam('checked', (bool)$fields['mandatory']->value);
            $fields['types'] = array();
            $fields['type_labels'] = array();
            if ($parent == '') {
                $parent = $fields['parent']->value;
            }
            $types = $this->getHtmlTypes($parent);
            foreach($types as $t) {
                $fields['types'][$t['id']->value] = $t['label']->value;
                $fields['type_labels'][$t['id']->value] = $t['description']->value;
            }
            $res = $fields;
        } else {
            
            $extrafields->innerJoin($htmltype, 'htmltype_id');
            $extrafields->orderBy($extrafieldsvalue->alias(). '.id DESC, '. $extrafields->alias(). '.ordering ASC');
            $extrafields->leftJoin($extraoptions, 'ef_id');
            $extrafields->arbitraryJoin('LEFT JOIN '. $extrafieldsvalue->getTable(). ' AS '. $extrafieldsvalue->alias(). ' ON '. $extrafieldsvalue->alias(). '.opt_id = '. $extraoptions->alias(). '.id');
            $fields = $extrafields->get($extrafields->alias(). '.*, '.
                                        $htmltype->alias(). '.label as type ,'. 
                                        $htmltype->alias(). '.description as type_label, '.
                                        $extraoptions->alias(). '.id as opt_id, '. 
                                        $extraoptions->alias(). '.value as opt_value, '.
                                        $extraoptions->alias(). '.data as opt_data, '.
                                        $extrafieldsvalue->alias(). '.price as opt_price, '.
                                        $extrafieldsvalue->alias(). '.price_absolute as opt_price_absolute, '.
                                        $extrafieldsvalue->alias(). '.disabled as opt_disabled, '.
                                        $extrafieldsvalue->alias(). '.parent_id as opt_parent_id ',
                                        //'(SELECT GROUP_CONCAT(pid) FROM '. $extraoptionsExclude->getTable(). ' WHERE oid = opt_id) AS excludePids',
                                        
                                        $where);
			$removeOptions = array();
            foreach($fields as $f) {
                if(!isset($res[$f['id']]))
                    $res[$f['id']] = $f;
                $res[$f['id']]['opt_values'][$f['opt_id']]['id'] = $f['opt_id'];
                $res[$f['id']]['opt_values'][$f['opt_id']]['value'] = $f['opt_value'];
                $res[$f['id']]['opt_values'][$f['opt_id']]['data'] = utils::jsonDecode($f['opt_data']);
                if((int)$parent === (int)$f['opt_parent_id'] && !empty($parent)) {
                    if(empty($f['opt_disabled']) || isset($d['includeDisabled'])) {
                        $res[$f['id']]['opt_values'][$f['opt_id']]['price'] = $f['opt_price'];
                        $res[$f['id']]['opt_values'][$f['opt_id']]['price_absolute'] = $f['opt_price_absolute'];
                        $res[$f['id']]['opt_values'][$f['opt_id']]['disabled'] = $f['opt_disabled'];
                        $res[$f['id']]['opt_values'][$f['opt_id']]['parent_id'] = $f['opt_parent_id'];
                    } else {
						if(!isset($removeOptions[$f['id']]))
							$removeOptions[$f['id']] = array();
						$removeOptions[$f['id']][] = $f['opt_id'];
                    }
                }
            }
			// Remove disabled options
			// We didn't simple unset it earlier as disabled option can be included into responce from db more than one time
			if(!empty($removeOptions) && !empty($res)) {
				foreach($removeOptions as $fId => $remove) {
					if(isset($res[$fId]) && isset($res[$fId]['opt_values']) && !empty($res[$fId]['opt_values'])) {
						foreach($remove as $rId) {
							if(isset($res[$fId]['opt_values'][$rId]))
								unset($res[$fId]['opt_values'][$rId]);
						}
					}
				}
			}
        }
        return $res;
    }
    /**
     * Validate user input according to existing rules in database
     */
    public function validate($d = array()) {
        if(isset($d['allFields']))
            $all = $d['allFields'];
        else
            $all = $this->get();
        $errors = array();
        foreach($all as $f) {
            //if(isset($d[$f['code']])) {
                $validate = array();
                if($f['mandatory'])
                    $validate[] = 'notEmpty';
                if($f['validate'])
                    $validate[] = $f['validate'];
                if($validate) {
                    $field = new field($f['code'], $f['type'], '', $f['default_value'], $f['label'], 0, array(), $validate);
                    $field->setValue($d[$f['code']]);
                    if($e = validator::_($field)) {
                        $errors = array_merge($errors, $e);
                    }
                }
            //}
        }
        return $errors;
    }
    public function put($d = array()) {
        $nameForRes = $d['parent'] == 'user' ? 'Userfield' : 'Extrafield';
        $res = new response();
        $id = $d['id'];
        $options = array();
        if(!empty($d['params']) && is_array($d['params'])) {
            $d['params'] = db::prepareHtml($d['params']);
        }
        $d = prepareParams($d, $options);
        if(is_numeric($id)) {
            if(!isset($d['ignore']))
                $d['ignore'] = array();
            if(!isset($d['mandatory']) && !in_array('mandatory', $d['ignore']))
              $d['mandatory'] = 0;
            if ((!isset($d['ordering']) || !is_numeric($d['ordering'])) && !in_array('ordering', $d['ignore'])) {
                $d['ordering'] = 0;
            }
            if(frame::_()->getTable('extrafields')->update($d, array('id' => $id))) {
                frame::_()->getModule('options')->getModel('extraoptions')->saveOptions($options, $id);                
				if(isset($d['default_value_label'])) {
					// Retrive extrafield value by label (value column) and extrafield ID
					$defaultValue = frame::_()->getModule('options')->getModel('extraoptions')->get(array('ef_id' => $id, 'value' => $d['default_value_label']));
					if(!empty($defaultValue) && isset($defaultValue[0]) && !empty($defaultValue[0])) {
						frame::_()->getTable('extrafields')->update(array('default_value' => $defaultValue[0]['id']), array('id' => $id));
					}
				}
                $res->messages[] = lang::_($nameForRes. ' Updated');
                $exFields = frame::_()->getTable('extrafields')->getById($id);
                $newType = frame::_()->getTable('htmltype')->getById($exFields['htmltype_id'], 'label');
                $newType = $newType['label'];
                $res->data = array(
                    'id' => $id, 
                    'label' => $exFields['label'], 
                    'code' => $exFields['code'], 
                    'type' => $newType,
                    'active' => $d['active'],
                );
            } else {
                if($tableErrors = frame::_()->getTable('extrafields')->getErrors()) 
                    $res->errors = array_merge($res->errors, $tableErrors);
                else
                    $res->errors[] = lang::_($nameForRes. ' Update Failed');
            }
        } else {
            $res->errors[] = lang::_('Error '. $nameForRes. ' ID');
        }
        
        return $res;
    }
    public function post($d = array()) {
        $nameForRes = $d['parent'] == 'user' ? 'Userfield' : 'Extrafield';
        $res = new response();
        $options = array();
        $d = prepareParams($d, $options);
        if($id = frame::_()->getTable('extrafields')->insert($d)) {
			if(!isset($d['ignoreOptionsInsert']) || !$d['ignoreOptionsInsert'])
				frame::_()->getModule('options')->getModel('extraoptions')->saveOptions($options, $id);    
            $res->messages[] = lang::_($nameForRes. ' Added');
            $newType = frame::_()->getTable('htmltype')->getById($d['htmltype_id'], 'label');
            $newType = $newType['label'];
            $res->data = array(
                'id' => $id, 
                'label' => $d['label'], 
                'code' => $d['code'], 
                'type' => $newType
            );
        } else
            $res->errors[] = lang::_($nameForRes. ' Insert Failed');
        return $res;
    }
    public function getHtmlTypes($parent) {
        static $types;
        if(!$types)
            $types = frame::_()->getTable('htmltype')->fillFromDB();
        foreach ($types as $key => $value) {
            switch ($parent) {
                /**
                 * @deprecated moved to productfieldsModel
                 */
                /*case 'products':
                       if (!in_array($types[$key]['label']->value, array('text', 'radiobuttons', 'checkboxlist', 'selectbox', 'selectlist'))) {
                           unset($types[$key]);
                       }
                    break;*/
                case 'user':
                    if (!in_array($types[$key]['label']->value, array('text', 'radiobuttons', 'countryList', 'password', 'checkboxlist', 'selectbox', 'selectlist', 'statesList', 'textFieldsDynamicTable', 'datepicker'))) {
                           unset($types[$key]);
                       }
                    break;
                default: 
                    break;
            }
        }
        return $types;
    }
    /**
     * Delete Extra Field
     * 
     * @param array $d
     * @return response 
     */
    public function delete($d = array()) {
        $nameForRes = $d['parent'] == 'user' ? 'Userfield' : 'Extrafield';
        $res = new response();
        $id = $d['id'];
        if(is_numeric($id)) {
            if(frame::_()->getTable('extrafields')->delete($d, array('id' => $id))) {
                frame::_()->getTable('extraoptions')->delete(array('ef_id'=> $id));
                frame::_()->getTable('extrafieldsvalue')->delete(array('ef_id'=> $id));
                $res->messages[] = lang::_($nameForRes. ' Deleted');
            } else
                $res->errors[] = lang::_($nameForRes. ' Delete Failed');
        } else 
            $res->errors[] = lang::_('Error '. $nameForRes. ' ID');
        return $res;
    }
       
}
?>