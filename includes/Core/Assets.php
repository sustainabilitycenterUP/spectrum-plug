<?php
namespace Spectrum\Evidence\Core;

if (!defined('ABSPATH')) exit;

final class Assets {
  private static $enqueued = false;

  public static function enqueueOnce() {
    if (self::$enqueued) return;
    self::$enqueued = true;

    add_action('wp_enqueue_scripts', function () {
      wp_enqueue_style(
        'spectrum-evidence-app',
        SPECTRUM_EV_URL . 'assets/app.css',
        array(),
        SPECTRUM_EV_VER
      );
      wp_enqueue_script(
        'spectrum-evidence-app',
        SPECTRUM_EV_URL . 'assets/app.js',
        array(),
        SPECTRUM_EV_VER,
        true
      );
    });
  }
}