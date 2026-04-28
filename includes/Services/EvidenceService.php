<?php
namespace Spectrum\Evidence\Services;

use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Repositories\EvidenceRepository;
use Spectrum\Evidence\Repositories\LogRepository;
use Spectrum\Evidence\Repositories\EvidenceMetricRepository;

if (!defined('ABSPATH')) exit;

final class EvidenceService {

  public static function createOrUpdateFromPost($action) {
    $user_id = Auth::userId();

    $is_update   = !empty($_POST['evidence_id']);
    $evidence_id = $is_update ? (int)$_POST['evidence_id'] : 0;

    $year    = isset($_POST['year']) ? (int)$_POST['year'] : 0;
    $title   = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $summary = isset($_POST['summary']) ? sanitize_textarea_field($_POST['summary']) : '';
    $link    = isset($_POST['link_url']) ? esc_url_raw($_POST['link_url']) : '';
    $metric_id = isset($_POST['metric_id']) ? (int)$_POST['metric_id'] : 0;
    $metric_number_value = (isset($_POST['metric_number_value']) && $_POST['metric_number_value'] !== '')
      ? (float)$_POST['metric_number_value']
      : null;

    $target_status = (strpos($action, 'submit') !== false) ? 'SUBMITTED' : 'DRAFT';
    $unit_code = Auth::unitCode($user_id);

    $old = null;
    $old_status = null;
    $old_attachment_id = 0;

    if ($is_update) {
      $old = EvidenceRepository::find($evidence_id);
      if (!$old) return new \WP_Error('not_found', 'Evidence tidak ditemukan.');

      if ((int)$old->submitter_id !== (int)$user_id) {
        return new \WP_Error('forbidden', 'Anda tidak punya akses untuk mengubah evidence ini.');
      }

      if (!in_array($old->status, array('DRAFT','REJECTED'), true)) {
        return new \WP_Error('not_editable', 'Evidence hanya bisa diedit saat status DRAFT atau REJECTED.');
      }

      $old_status = $old->status;
      $old_attachment_id = (int)($old->attachment_id ?? 0);
    }

    // upload/replace file kalau upload baru
    $new_attachment_id = UploadService::maybeUploadAndReplace('evidence_file', $old_attachment_id);
    if (is_wp_error($new_attachment_id)) return $new_attachment_id;

    $now = current_time('mysql');

    if (!$is_update) {
      $insert_data = array(
        'submitter_id'  => $user_id,
        'year'          => $year,
        'unit_code'     => $unit_code,
        'title'         => $title,
        'summary'       => $summary,
        'link_url'      => $link,
        'status'        => $target_status,
        'submitted_at'  => ($target_status === 'SUBMITTED') ? $now : null,
        'created_at'    => $now,
        'updated_at'    => $now,
      );

      if (EvidenceRepository::hasColumn('numeric_value')) {
        $insert_data['numeric_value'] = $metric_number_value;
      }
      if (EvidenceRepository::hasColumn('attachment_id')) {
        $insert_data['attachment_id'] = $new_attachment_id ? (int)$new_attachment_id : null;
      }

      $insert_id = EvidenceRepository::insert($insert_data);

      if (!$insert_id) return new \WP_Error('db_insert_failed', 'Gagal menyimpan evidence ke database.');

      EvidenceMetricRepository::setSingleMetric($insert_id, $metric_id);
      LogRepository::add($insert_id, $user_id, null, $target_status, 'Create evidence');

      return $insert_id;
    }

    // update
    $update_data = array(
      'year'          => $year,
      'title'         => $title,
      'summary'       => $summary,
      'link_url'      => $link,
      'status'        => $target_status,
      'submitted_at'  => ($target_status === 'SUBMITTED') ? $now : null,
      'updated_at'    => $now,
    );

    if (EvidenceRepository::hasColumn('numeric_value')) {
      $update_data['numeric_value'] = $metric_number_value;
    }
    if (EvidenceRepository::hasColumn('attachment_id')) {
      $update_data['attachment_id'] = $new_attachment_id ? (int)$new_attachment_id : null;
    }

    $ok = EvidenceRepository::update($evidence_id, $update_data);

    if ($ok === false) return new \WP_Error('db_update_failed', 'Gagal update evidence.');

    EvidenceMetricRepository::setSingleMetric($evidence_id, $metric_id);

    if ($old_status !== $target_status) {
      LogRepository::add($evidence_id, $user_id, $old_status, $target_status, 'Update evidence');
    }

    return $evidence_id;
  }
}
