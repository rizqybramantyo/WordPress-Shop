<?php
class wpUpdater {
    public $pluginSlug = '';
    public function __construct($pluginSlug) {
        $this->pluginSlug = $pluginSlug;
    }
    static public function getInstance($pluginSlug) {
        static $instances = array();
        if(!isset($instances[ $pluginSlug ])) {
            $instances[ $pluginSlug ] = new wpUpdater($pluginSlug);
        }
        return $instances[ $pluginSlug ];
    }
    public function checkForPluginUpdate($checkedData) {
		// This is disabled for now
        return $checkedData;
        if (empty($checkedData->checked))
            return $checkedData;
	
        $request_args = array(
            'slug' => $this->pluginSlug,
            'version' => $checkedData->checked[$this->pluginSlug .'/'. $this->pluginSlug .'.php'],
        );

        $request_string = $this->prepareRequest('basic_check', $request_args);

        // Start checking for an update
        $raw_response = wp_remote_post(S_API_UPDATE_URL, $request_string);

        if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
            $response = unserialize($raw_response['body']);

        if (is_object($response) && !empty($response)) // Feed the update data into WP updater
            $checkedData->response[$this->pluginSlug .'/'. $this->pluginSlug .'.php'] = $response;

        return $checkedData;
    }
    public function myPluginApiCall($def, $action, $args) {
		// This is disabled for now
		return $def;
        if ($args->slug != $this->pluginSlug)
            return false;

        // Get the current version
        $plugin_info = get_site_transient('update_plugins');
        $current_version = $plugin_info->checked[$this->pluginSlug .'/'. $this->pluginSlug .'.php'];
        $args->version = $current_version;

        $request_string = $this->prepareRequest($action, $args);

        $request = wp_remote_post(S_API_UPDATE_URL, $request_string);

        if (is_wp_error($request)) {
            $res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
        } else {
            $res = unserialize($request['body']);

            if ($res === false)
                $res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
        }

        return $res;
    }
    public function prepareRequest($action, $args) {
        global $wp_version;
	
        return array(
            'body' => array(
                'action' => $action, 
                'request' => serialize($args),
                'api-key' => md5(get_bloginfo('url'))
            ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
        );	
    }
}
?>
