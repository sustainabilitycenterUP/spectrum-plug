<?php
namespace Spectrum\Evidence\Services;

use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Repositories\EvidenceRepository;

if (!defined('ABSPATH')) exit;

final class DeleteService {

  public static function deleteDraft($evidence_id) {
    $evidence_id = (int)$evidence_id;

    if (!Auth::isLoggedIn()) return new \WP_Error('forbidden', 'Unauthorized');

    $ev = EvidenceRepository::find($evidence_id);
    if (!$ev) return new \WP_Error('not_found', 'Evidence tidak ditemukan.');

    if ((int)$ev->submitter_id !== (int)Auth::userId()) {
      return new \WP_Error('forbidden', 'Tidak punya akses.');
    }

    if ($ev->status !== 'DRAFT') {
      return new \WP_Error('not_allowed', 'Hanya DRAFT yang boleh dihapus.');
    }

    $att = (int)($ev->attachment_id ?? 0);
    if ($att) {
      wp_delete_attachment($att, true);
    }

    // FK cascade akan bersihin evidence_metric/log/attachment custom kalau FK kamu aktif.
    EvidenceRepository::delete($evidence_id);

    return true;
  }
}