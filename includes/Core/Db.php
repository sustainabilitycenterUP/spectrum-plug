<?php
namespace Spectrum\Evidence\Core;

if (!defined('ABSPATH')) exit;

final class Db {
  private static $resolved = array();

  public static function table($name) {
    global $wpdb;
    $name = preg_replace('/[^a-z0-9_]/i', '', (string)$name);
    if ($name === '') return $wpdb->prefix;

    if (isset(self::$resolved[$name])) {
      return self::$resolved[$name];
    }

    $preferred = $wpdb->prefix . $name;
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $preferred));
    if (!empty($exists)) {
      self::$resolved[$name] = $preferred;
      return $preferred;
    }

    // fallback kompatibilitas: banyak dump lokal memakai prefix default wp_
    $legacy = 'wp_' . $name;
    $legacy_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $legacy));
    if (!empty($legacy_exists)) {
      self::$resolved[$name] = $legacy;
      return $legacy;
    }

    self::$resolved[$name] = $preferred;
    return $preferred;
  }
}
