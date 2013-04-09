<?php
class templates extends module {
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    protected $_styles = array();
    public function getTabs(){
        $tabs = array();
        $tab = new tab(lang::_('Templates'), $this->getCode());
        $tab->setView('templatesTab');
		$tab->setSortOrder(1);
        $tabs[] = $tab;
        return $tabs;
    }
    public function init() {
        $this->_styles = array(
            'style' => array('path' => S_CSS_PATH. 'style.css'), 
            'subScreen' => array('path' => S_CSS_PATH. 'subScreen.css'), 
            'rating' => array('path' => S_CSS_PATH. 'rating.css'), 
            'jquery-datepicker' => array('path' => S_CSS_PATH. 'jquery-datepicker.css'),
            'adminStyles' => array('path' => S_CSS_PATH. 'adminStyles.css'),
            'farbtasticCss' => array('path' => S_CSS_PATH.'farbtastic.css'),
            'jquery-tabs' => array('path' => S_CSS_PATH. 'jquery-tabs.css', 'substituteFor' => 'frontend'),
            'evoslider' => array('path' => S_CSS_PATH. 'evoslider/evoslider.css', 'for' => 'frontend'),
            'evoslider-default-default' => array('path' => S_CSS_PATH. 'evoslider/default/default.css', 'for' => 'frontend'),
            'jquery-slider' => array('path' => S_CSS_PATH. 'jquery-slider.css', 'for' => 'admin'),
            'jquery-accordion' => array('path' => S_CSS_PATH. 'jquery-accordion.css', 'for' => 'admin'),
			'system' => array('path' => S_CSS_PATH. 'system.css'),
            //'jquery-theme' => array('path' => S_CSS_PATH. 'ui-lightness/jquery-ui-1.8.16.custom.css'),
        );
        $defaultPlugTheme = frame::_()->getModule('options')->get('default_theme');
		$ajaxurl = admin_url('admin-ajax.php');
		if(frame::_()->getModule('options')->get('ssl_on_ajax')) {
			$ajaxurl = uri::makeHttps($ajaxurl);
		}
        $jsData = array(
            'siteUrl'					=> S_SITE_URL,
            'imgPath'					=> S_IMG_PATH,
            'loader'					=> S_LOADER_IMG, 
            'close'						=> S_IMG_PATH. 'cross.gif', 
            'ajaxurl'					=> $ajaxurl,
            'animationSpeed'			=> frame::_()->getModule('options')->get('js_animation_speed'),
            'tag_ID'					=> req::getVar('tag_ID', 'all', 0),
            'taxonomy'					=> req::getVar('taxonomy', 'all', ''),
            'lang' => array(
                'Value'				=> lang::_('Value'),
                'Price'				=> lang::_('Price'),
                'Weight'			=> lang::_('Weight'),
            ),
			'siteLang'					=> lang::getData(),
            'showSubscreenOnCenter'		=> frame::_()->getModule('options')->get('show_subscreen_on_center'),
			'options'					=> frame::_()->getModule('options')->getByCode(),
			'countries'					=> fieldAdapter::getCachedCountries(),
        );
        $jsData = dispatcher::applyFilters('jsInitVariables', $jsData);
        frame::_()->addScript('thickbox');
        frame::_()->addStyle('thickbox');
        frame::_()->addScript('jquery-ui-tabs', '', array('jquery'), false, true);
        frame::_()->addScript('jquery-ui-sortable', '', array('jquery'), false, true);
        frame::_()->addScript('jquery-ui-button', '', array('jquery'), false, true);
        frame::_()->addScript('jquery-datepicker', S_JS_PATH. 'jquery.datepicker.min.js', array('jquery'), false, true);
		frame::_()->addScript('recapcha', 'https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
        //frame::_()->addStyle('jquery-datepicker', S_CSS_PATH. 'ui-lightness/jquery-ui-1.8.16.custom.css');    //For datepicker
        frame::_()->addScript('postbox', get_bloginfo('wpurl'). '/wp-admin/js/postbox.js');
        if (is_admin()) {
            frame::_()->addScript('jquery-ui-slider', '', array('jquery'), false, true);
            frame::_()->addScript('jquery-ui-accordion', '', array('jquery'), false, true);
            //jquery.ui.slider.min
            //frame::_()->addStyle('adminStyles', S_CSS_PATH. 'adminStyles.css');
            //frame::_()->addStyle('farbtasticCss', S_CSS_PATH.'farbtastic.css');
            //frame::_()->addScript('colorpicker');
            frame::_()->addScript('farbtastic');
            frame::_()->addScript('jquery-form');
            frame::_()->addScript('media-upload');
            frame::_()->addScript('adminEditOptions', S_JS_PATH. 'adminEditOptions.js', array(), false, true);
			if(req::getVar('taxonomy','get')=="products_categories")
            frame::_()->addScript('categoryImageUpload', S_JS_PATH.'categoryImageUpload.js');
            frame::_()->addScript('ajaxupload', S_JS_PATH. 'ajaxupload.js');
			frame::_()->addScript('countries', S_JS_PATH. 'countries.js');
			frame::_()->addScript('states', S_JS_PATH. 'states.js');
            frame::_()->addScript('quicktags');
            
        } else {
            frame::_()->addScript('jquery.easing.1.3', S_JS_PATH. 'jquery.easing.1.3.js', array('jquery'), false, false);
            frame::_()->addScript('jquery.evoslider-2.1.0', S_JS_PATH. 'jquery.evoslider-2.1.0.js', array('jquery'), false, false);
            frame::_()->addScript('rating', S_JS_PATH. 'rating.js');
            frame::_()->addScript('frontend', S_JS_PATH. 'frontend.js');
        }
        frame::_()->addJSVar('adminForm', 'TOE_DATA', $jsData);
        frame::_()->addScript('adminForm', S_JS_PATH. 'adminForm.js', array('postbox', 'jquery-form'));
		frame::_()->addScript('jquery-placeholder', S_JS_PATH. 'jquery.placeholder.min.js');
        frame::_()->addJSVar('adminOptions', 'TOE_SELECT_ALL', lang::_('Select All'));
        frame::_()->addJSVar('adminOptions', 'TOE_DESELECT_ALL', lang::_('Deselect All'));
        frame::_()->addJSVar('adminOptions', 'TOE_LOADING', lang::_('Loading, please wait ...'));
        frame::_()->addScript('adminOptions', S_JS_PATH. 'adminOptions.js', array(), false, true);
        frame::_()->addScript('adminOrders', S_JS_PATH. 'adminOrders.js', array(), false, true);
        
        
        //frame::_()->addStyle('jquery-tabs', S_CSS_PATH. 'jquery-tabs.css');
		$desktop = true;
		if(utils::isTablet()) {
			$this->_styles['style-tablet'] = array();
			$desktop = false;
		} elseif(utils::isMobile()) {
			$this->_styles['style-mobile'] = array();
			$desktop = false;
		}
		if($desktop) {
			$this->_styles['style-desctop'] = array();
		}
        
        foreach($this->_styles as $s => $sInfo) {
            if(isset($sInfo['for'])) {
                if(($sInfo['for'] == 'frontend' && is_admin()) || ($sInfo['for'] == 'admin' && !is_admin()))
                    continue;
            }
            $canBeSubstituted = true;
            if(isset($sInfo['substituteFor'])) {
                switch($sInfo['substituteFor']) {
                    case 'frontend':
                        $canBeSubstituted = !is_admin();
                        break;
                    case 'admin':
                        $canBeSubstituted = is_admin();
                        break;
                }
            }
            if($canBeSubstituted && file_exists(S_TEMPLATES_DIR. $defaultPlugTheme. DS. $s. '.css')) {
                frame::_()->addStyle($s, S_TEMPLATES_PATH. $defaultPlugTheme. '/'. $s. '.css');
            } elseif($canBeSubstituted && file_exists(utils::getCurrentWPThemeDir(). 'toe'. DS. $s. '.css')) {
                frame::_()->addStyle($s, utils::getCurrentWPThemePath(). '/toe/'. $s. '.css');
            } elseif(!empty($sInfo['path'])) {
                frame::_()->addStyle($s, $sInfo['path']);
            }
        }
		add_action('wp_head', array($this, 'addInitJsVars'));
        parent::init();
    }
	/**
	 * Some JS variables should be added after first wordpress initialization.
	 * Do it here.
	 */
	public function addInitJsVars() {
		frame::_()->addJSVar('adminOptions', 'TOE_PAGES', array(
			'isCheckoutStep1' => frame::_()->getModule('pages')->isCheckoutStep1(),
			'isCart' => frame::_()->getModule('pages')->isCart(),
		));
	}
}
?>
