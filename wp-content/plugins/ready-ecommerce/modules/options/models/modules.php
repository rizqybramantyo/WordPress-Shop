<?php
class modulesModel extends model {
    public function get($d = array()) {
        if($d['id'] && is_numeric($d['id'])) {
            $fields = frame::_()->getTable('modules')->fillFromDB($d['id'])->getFields();
            $fields['types'] = array();
            $types = frame::_()->getTable('modules_type')->fillFromDB();
            foreach($types as $t) {
                $fields['types'][$t['id']->value] = $t['label']->value;
            }
            return $fields;
        } elseif(!empty($d)) {
            $data = frame::_()->getTable('modules')->get('*', $d);
            /*foreach($data as $i => $d) {
                if(!frame::_()->getModule()) {
                    
                }
            }*/
            return $data;
        } else {
            return frame::_()->getTable('modules')
                ->innerJoin(frame::_()->getTable('modules_type'), 'type_id')
                ->getAll(frame::_()->getTable('modules')->alias().'.*, '. frame::_()->getTable('modules_type')->alias(). '.label as type');
        }
        parent::get($d);
    }
    public function put($d = array()) {
        $res = new response();
        $id = $this->_getIDFromReq($d);
        $d = prepareParams($d);
        if(is_numeric($id) && $id) {
            if(isset($d['active']))
                $d['active'] = ((is_string($d['active']) && $d['active'] == 'true') || $d['active'] == 1) ? 1 : 0;           //mmm.... govnokod?....)))
           /* else
                 $d['active'] = 0;*/
            
            if(frame::_()->getTable('modules')->update($d, array('id' => $id))) {
                $res->messages[] = lang::_('Module Updated');
                $mod = frame::_()->getTable('modules')->getById($id);
                $newType = frame::_()->getTable('modules_type')->getById($mod['type_id'], 'label');
                $newType = $newType['label'];
                $res->data = array(
                    'id' => $id, 
                    'label' => $mod['label'], 
                    'code' => $mod['code'], 
                    'type' => $newType,
                    'params' => utils::jsonEncode($mod['params']),
                    'description' => $mod['description'],
                    'active' => $mod['active'], 
                );
            } else {
                if($tableErrors = frame::_()->getTable('modules')->getErrors()) {
                    $res->errors = array_merge($res->errors, $tableErrors);
                } else
                    $res->errors[] = lang::_('Module Update Failed');
            }
        } else {
            $res->errors[] = lang::_('Error module ID');
        }
        parent::put($d);
        return $res;
    }
    public function delete($d = array()) {
        $id = $this->_getIDFromReq($d);
        if(is_numeric($id) && $id) {
            frame::_()->getTable('modules')->delete($d);
        }
    }
    protected function _getIDFromReq($d = array()) {
        $id = 0;
        if(isset($d['id']))
            $id = $d['id'];
        elseif(isset($d['code'])) {
            $fromDB = $this->get(array('code' => $d['code']));
            if($fromDB[0]['id'])
                $id = $fromDB[0]['id'];
        }
        return $id;
    }
    /**
     * Collect the tabs from the given modules
     * 
     * @param array $modules
     * @return array of tab 
     */
    public function getTabs($modules = array()){
        if (!is_array($modules)) {
            $modules = array($modules);
        }
        $tabs = array();
        if (!empty($modules)) {
            foreach ($modules as $module) {
                if ($module['has_tab'] && frame::_()->getModule($module['code'])) {
                    $moduleTabs = frame::_()->getModule($module['code'])->getTabs();
                    if (!empty($moduleTabs)) {
						$tabs = array_merge($tabs, $moduleTabs);
                    }
                }
            }
        }
		if(!empty($tabs)) {
			usort($tabs, array($this, 'sortTabsCallback'));
			$tempTabs = $tabs;
			foreach($tempTabs as $i => $tab) {
				$parent = $tab->getParent();
				if(empty($parent) && ($parentIter = $this->getTabIterByModule($tabs, $parent))) {
					array_splice($tabs, $parentIter+1, 1, array($tabs[$parentIter+1], $tab));
				}
			}
		}
        return $tabs;
    }
	
	public function getTabIterByModule($tabs, $module) {
		foreach($tabs as $i => $tab) {
			if($tab->getModule() == $module)
				return $i;
		}
		return false;
	}
	public function sortTabsCallback($a, $b) {
		$sortOrderA = $a->getSortOrder();
		$sortOrderB = $b->getSortOrder();
		/*if($sortOrderA === false)
			$sortOrderA = -1;
		if($sortOrderB === false)
			$sortOrderB = -1;
		if($sortOrderA == $sortOrderB)
			return 0;
		return $sortOrderA > $sortOrderB ? 1 : -1;*/
		if($sortOrderA === false && $sortOrderB === false) {
			return 0;
		} elseif($sortOrderA !== false && $sortOrderB === false) {
			return -1;
		} elseif($sortOrderA === false && $sortOrderB !== false) {
			return 1;
		} elseif($sortOrderA !== false && $sortOrderB !== false) {
			if($sortOrderA == $sortOrderB)
				return 0;
			else
				return $sortOrderA > $sortOrderB ? 1 : -1;
		}
		return 0;
	}
}
?>