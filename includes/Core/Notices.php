<?php
namespace Spectrum\Evidence\Core;

if (!defined('ABSPATH')) exit;

final class Notices {

  public static function set($user_id, $type, $messages) {
    set_transient('spectrum_ev_notice_' . (int)$user_id, array(
      'type' => $type,
      'messages' => (array)$messages,
    ), 60);
  }

  public static function get($user_id) {
    $key = 'spectrum_ev_notice_' . (int)$user_id;
    $n = get_transient($key);
    if ($n) delete_transient($key);
    return $n;
  }
}