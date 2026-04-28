<?php
namespace Spectrum\Evidence\Core;

use Spectrum\Evidence\Http\PostHandler;

use Spectrum\Evidence\Shortcodes\MyEvidenceShortcode;
use Spectrum\Evidence\Shortcodes\EvidenceFormShortcode;
use Spectrum\Evidence\Shortcodes\EvidenceDetailShortcode;
use Spectrum\Evidence\Shortcodes\ReviewQueueShortcode;
use Spectrum\Evidence\Shortcodes\DashboardShortcode;
use Spectrum\Evidence\Shortcodes\MetricCatalogShortcode;

if (!defined('ABSPATH')) exit;

final class Plugin {

  public static function init() {
    // autoload sederhana (tanpa composer)
    spl_autoload_register(array(__CLASS__, 'autoload'));

    add_action('init', array(PostHandler::class, 'handle'));

    add_shortcode('spectrum_my_evidence', array(MyEvidenceShortcode::class, 'render'));
    add_shortcode('spectrum_evidence_form', array(EvidenceFormShortcode::class, 'render'));
    add_shortcode('spectrum_evidence_detail', array(EvidenceDetailShortcode::class, 'render'));
    add_shortcode('spectrum_review_queue', array(ReviewQueueShortcode::class, 'render'));
    // add_shortcode('spectrum_dashboard', array(DashboardShortcode::class, 'render'));
    add_shortcode('spectrum_approved_evidence', array(\Spectrum\Evidence\Shortcodes\ApprovedEvidenceShortcode::class, 'render'));
    add_shortcode(
      'spectrum_dashboard',
      array(\Spectrum\Evidence\Shortcodes\DashboardShortcode::class, 'render'));
    add_shortcode('spectrum_metric_catalog', array(MetricCatalogShortcode::class, 'render'));
  }

  public static function activate() {
    require_once SPECTRUM_EV_PATH . 'includes/Core/Installer.php';
    Installer::install();
  }

  public static function autoload($class) {
    if (strpos($class, 'Spectrum\\Evidence\\') !== 0) return;

    $rel = str_replace('Spectrum\\Evidence\\', '', $class);
    $rel = str_replace('\\', '/', $rel);
    $file = SPECTRUM_EV_PATH . 'includes/' . $rel . '.php';

    if (file_exists($file)) require_once $file;
  }
}
