<?php
namespace Spectrum\Evidence\Shortcodes;

use Spectrum\Evidence\Core\Assets;
use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Core\Notices;
use Spectrum\Evidence\Core\View;

use Spectrum\Evidence\Repositories\EvidenceRepository;
use Spectrum\Evidence\Repositories\LogRepository;
use Spectrum\Evidence\Repositories\MetricRepository;
use Spectrum\Evidence\Repositories\YearMetricRepository;
use Spectrum\Evidence\Repositories\EvidenceMetricRepository;

if (!defined('ABSPATH')) exit;

final class EvidenceDetailShortcode {
  public static function render() {
    if (!Auth::isLoggedIn()) return '<p>Silakan login.</p>';
    Assets::enqueueOnce();

    $user_id = Auth::userId();
    $evidence_id = isset($_GET['evidence_id']) ? (int)$_GET['evidence_id'] : 0;
    if (!$evidence_id) return '<p>Evidence tidak ditemukan.</p>';

    $ev = EvidenceRepository::find($evidence_id);
    if (!$ev) return '<p>Evidence tidak ditemukan.</p>';

    $can_review = Auth::isReviewer($user_id);
    if ((int)$ev->submitter_id !== (int)$user_id && !$can_review) {
      return '<p>Anda tidak memiliki akses ke evidence ini.</p>';
    }

    $mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'view';

    $editable = (
      $mode === 'edit' &&
      (int)$ev->submitter_id === (int)$user_id &&
      in_array($ev->status, array('DRAFT','REJECTED'), true)
    );

    // metrics (untuk edit)
    $years = YearMetricRepository::yearsActiveDistinct();
    $metrics = MetricRepository::activeMetricsWithYear();
    $metric_options = array();
    foreach ((array)$metrics as $m) {
      $key = 'SDG ' . $m->sdg_number . ' – Tahun ' . $m->year;
      if (!isset($metric_options[$key])) $metric_options[$key] = array();
      $metric_options[$key][] = $m;
    }

    $selected_metric_id = EvidenceMetricRepository::getMetricIdByEvidence($evidence_id);
    $selected_metric = $selected_metric_id ? MetricRepository::getMetricById($selected_metric_id) : null;
    $logs = LogRepository::listByEvidence($evidence_id);

    $file_url = '';
    if (!empty($ev->attachment_id)) {
      $file_url = wp_get_attachment_url((int)$ev->attachment_id);
    }

    return View::render('evidence-detail', array(
      'notice' => Notices::get($user_id),
      'ev' => $ev,
      'editable' => $editable,
      'metric_options' => $metric_options,
      'selected_metric_id' => $selected_metric_id,
      'selected_metric' => $selected_metric,
      'years' => $years,
      'logs' => $logs,
      'file_url' => $file_url,
      'can_review' => $can_review,
    ));
  }
}
