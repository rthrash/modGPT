<?php
/**
 * AutoSummaryPlugin
 * Updated version: Safely retrieves the action property and injects our assets.
 */

switch ($modx->event->name) {
    case 'OnManagerPageBeforeRender':
        // Initialize action to an empty string.
        $action = '';
        
        // Check if $modx->controller is set, is an object, and has an "action" property.
        if (isset($modx->controller) && is_object($modx->controller) && property_exists($modx->controller, 'action')) {
            $action = $modx->controller->action;
        } elseif (isset($_REQUEST['a'])) {
            // Fallback: Use the request parameter.
            $action = $_REQUEST['a'];
        }
        
        // Log the action value for debugging.
        $modx->log(modX::LOG_LEVEL_INFO, 'AutoSummaryPlugin: Current action value is: ' . print_r($action, true));
        
        // Adjust the check as per your Manager’s action identifiers.
        if (in_array($action, ['resource/create', 'resource/update', '78', '85'])) {
            // Define the assets URL (ensure this path matches your installation).
            $assetsUrl = $modx->getOption('assets_url') . 'components/autosummary/';
            // Register the custom CSS and JavaScript files.
            $modx->regClientCSS($assetsUrl . 'css/autosummary.css');
            $modx->regClientStartupScript($assetsUrl . 'js/autosummary.js');
        }
        break;
}
?>