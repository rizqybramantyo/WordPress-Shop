<?php
class toecoptions extends module {
    /**
     * Method to trigger the database update
     */
    public function init(){
        parent::init();
        dispatcher::add('onAfterInit', array($this,'updateDatabase'));
        
        $add_option = array(
            'add_checkbox' => lang::_('Add Checkbox'),
            'add_radiobutton' => lang::_('Add Radio Button'),
            'add_item' => lang::_('Add Item'),
        );
        frame::_()->addJSVar('adminOptions', 'TOE_LANG', $add_option);
    }
    /**
     * update database to current version if the update available
     */
    public function updateDatabase() {
        $current_version = get_option('re_db_version',0);
        if ($current_version) {
            if (version_compare(S_VERSION, $current_version,'>')) {
                $update_to_version_method = 'update_'.str_replace('.','',S_VERSION);
                if (method_exists('installer', $update_to_version_method)) {
                    return installer::$update_to_version_method();
                } else {
                    return true;
                }
            }
        }
    }
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    public function getTabs(){
        $tabs = array();
        $tab = new tab(lang::_('General'), $this->getCode());
        $tab->setView('optionTab');
        $tab->setSortOrder(-99);
        $tabs[] = $tab;
        return $tabs;
    }
    /**
     * This method provides fast access to options model method get
     * @see optionsModel::get($d)
     */
    public function get($d = array()) {
        return $this->getController()->getModel()->get($d);
    }
}
?>
