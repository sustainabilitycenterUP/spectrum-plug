<?php
/**
 * Plugin Name: SPECTRUM Evidence
 * Description: Sistem pengelolaan evidence THE & dashboard (OOP).
 * Version: 0.4.0
 * Author: Sustainability Center Universitas Pertamina
 */

if (!defined('ABSPATH')) exit;

define('SPECTRUM_EV_PATH', plugin_dir_path(__FILE__));
define('SPECTRUM_EV_URL',  plugin_dir_url(__FILE__));
define('SPECTRUM_EV_VER',  '0.4.0');

require_once SPECTRUM_EV_PATH . 'includes/Core/Plugin.php';

register_activation_hook(__FILE__, array('Spectrum\\Evidence\\Core\\Plugin', 'activate'));

add_action('plugins_loaded', function () {
  \Spectrum\Evidence\Core\Plugin::init();
});