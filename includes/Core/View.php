<?php
namespace Spectrum\Evidence\Core;

if (!defined('ABSPATH')) exit;

final class View {

  public static function render($view, $data = array()) {
    $file = trailingslashit(SPECTRUM_EV_PATH) . 'views/' . $view . '.php';
    if (!file_exists($file)) {
      return '<p>View tidak ditemukan: ' . esc_html($view) . '</p>';
    }
    ob_start();
    extract($data, EXTR_SKIP);
    include $file;
    return ob_get_clean();
  }
}