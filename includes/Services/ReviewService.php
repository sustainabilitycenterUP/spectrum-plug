<?php
namespace Spectrum\Evidence\Services;

use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Repositories\EvidenceRepository;
use Spectrum\Evidence\Repositories\LogRepository;

if (!defined('ABSPATH')) exit;

final class ReviewService {

  public static function applyDecision($evidence_id, $decision, $notes = '') {
    $evidence_id = (int)$evidence_id;

    if (!Auth::isReviewer()) {
      return new \WP_Error('forbidden', 'Unauthorized');
    }

    $ev = EvidenceRepository::find($evidence_id);
    if (!$ev) return new \WP_Error('not_found', 'Evidence tidak ditemukan.');

    $old_status = $ev->status;

    $decision = strtoupper($decision);
    if (!in_array($decision, array('APPROVED','REJECTED'), true)) {
      return new \WP_Error('bad_request', 'Decision tidak valid.');
    }

    $ok = EvidenceRepository::update($evidence_id, array(
      'status' => $decision,
      'updated_at' => current_time('mysql'),
      'last_reviewed_at' => current_time('mysql'),
    ));

    if ($ok === false) return new \WP_Error('db_update_failed', 'Gagal update status review.');

    LogRepository::add(
      $evidence_id,
      Auth::userId(),
      $old_status,
      $decision,
      $notes ? $notes : 'Review decision'
    );

    return true;
  }
}