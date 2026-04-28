<?php
namespace Spectrum\Evidence\Core;

if (!defined('ABSPATH')) exit;

final class Auth {
  public static function userId() {
    return get_current_user_id();
  }

  public static function isLoggedIn() {
    return is_user_logged_in();
  }

  public static function isReviewer($user_id = 0) {
    $user_id = $user_id ? $user_id : self::userId();
    return user_can($user_id, 'manage_options') || user_can($user_id, 'edit_others_posts');
  }

  public static function unitCode($user_id = 0) {
    $user_id = $user_id ? $user_id : self::userId();
    $unit = get_user_meta($user_id, 'fungsi_slug', true);
    return $unit ? $unit : 'UNKNOWN';
  }
}