<?php
namespace Spectrum\Evidence\Shortcodes;

use Spectrum\Evidence\Core\Assets;
use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Core\View;

use Spectrum\Evidence\Repositories\ApprovedEvidenceRepository;
use Spectrum\Evidence\Services\ExportService;

if (!defined('ABSPATH')) exit;

final class ApprovedEvidenceShortcode {

  public static function render() {
    if (!Auth::isLoggedIn()) return '<p>Silakan login.</p>';
    if (!Auth::isReviewer()) return '<p>Halaman ini hanya untuk reviewer.</p>';

    Assets::enqueueOnce();

    $unit = isset($_GET['unit_code']) ? sanitize_text_field($_GET['unit_code']) : '';
    $sdg  = isset($_GET['sdg_number']) ? (int)$_GET['sdg_number'] : 0;

    $filters = array(
      'unit_code' => $unit,
      'sdg_number' => $sdg,
    );

    $rows = ApprovedEvidenceRepository::list($filters);
    if (!empty($_GET['export']) && $_GET['export'] === 'csv') {
      $export_rows = array();
      foreach ((array)$rows as $r) {
        $attachment_url = !empty($r->attachment_id) ? wp_get_attachment_url((int)$r->attachment_id) : '';
        $evidence_link = !empty($r->link_url) ? $r->link_url : $attachment_url;

        $export_rows[] = array(
          'sdg_number' => (int)($r->sdg_number ?? 0),
          'metric_code' => $r->metric_code ?? '',
          'metric_title' => $r->metric_title ?? '',
          'metric_question' => $r->metric_question ?? '',
          'judul_evidence' => $r->title ?? '',
          'number_evidence' => isset($r->numeric_value) ? $r->numeric_value : '',
          'attachment_or_link' => $evidence_link ?: '',
          'ringkasan_evidence' => $r->summary ?? '',
        );
      }
      ExportService::outputCsv(
        'approved-evidence-' . date('Ymd-His') . '.csv',
        array('sdg_number', 'metric_code', 'metric_title', 'metric_question', 'judul_evidence', 'number_evidence', 'attachment_or_link', 'ringkasan_evidence'),
        $export_rows
      );
    }

    return View::render('approved-evidence', array(
      'active' => 'approved',
      'filters' => $filters,
      'units' => ApprovedEvidenceRepository::distinctUnits(),
      'rows' => $rows,
    ));
  }
}
