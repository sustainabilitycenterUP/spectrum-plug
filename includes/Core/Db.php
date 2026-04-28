<?php
namespace Spectrum\Evidence\Core;

if (!defined('ABSPATH')) exit;

final class Db {
  public static function table($name) {
    global $wpdb;
    return $wpdb->prefix . $name;
  }
}