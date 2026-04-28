<?php
namespace Spectrum\Evidence\Http;

use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Core\Notices;
use Spectrum\Evidence\Core\Url;

use Spectrum\Evidence\Services\EvidenceService;
use Spectrum\Evidence\Services\ReviewService;
use Spectrum\Evidence\Services\DeleteService;
use Spectrum\Evidence\Repositories\FunctionMetricAssignmentRepository;
use Spectrum\Evidence\Repositories\MetricNoDataRepository;
use Spectrum\Evidence\Repositories\MetricCoverageRepository;

if (!defined('ABSPATH')) exit;

final class PostHandler {

  public static function handle() {
    if (!Auth::isLoggedIn()) return;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    // 1) evidence save (draft/submit/update_*)
    if (!empty($_POST['spectrum_action']) && !empty($_POST['spectrum_nonce'])) {
      self::handleEvidenceSave();
      return;
    }

    // 2) review action
    if (isset($_POST['review_action'], $_POST['evidence_id'])) {
      self::handleReview();
      return;
    }

    // 3) delete draft
    if (isset($_POST['action'], $_POST['evidence_id']) && $_POST['action'] === 'delete_evidence') {
      self::handleDelete();
      return;
    }
  }

  private static function redirectBack() {
    $redirect = '';
    if (!empty($_POST['redirect_to'])) $redirect = esc_url_raw($_POST['redirect_to']);
    if (!$redirect) $redirect = wp_get_referer();
    if (!$redirect) $redirect = home_url('/');
    wp_safe_redirect($redirect);
    exit;
  }

  private static function handleEvidenceSave() {
    $user_id = Auth::userId();

    if (!wp_verify_nonce($_POST['spectrum_nonce'], 'spectrum_save_evidence')) {
      Notices::set($user_id, 'error', 'Sesi sudah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.');
      self::redirectBack();
    }

    $year = isset($_POST['year']) ? (int)$_POST['year'] : 0;
    $metric_id = isset($_POST['metric_id']) ? (int)$_POST['metric_id'] : 0;
    $mode = isset($_POST['metric_mode']) ? sanitize_text_field($_POST['metric_mode']) : '';
    $is_no_data = !empty($_POST['is_no_data']) && $mode === 'MANDATORY';

    if ($is_no_data) {
      $unit_code = Auth::unitCode($user_id);
      $is_mandatory = FunctionMetricAssignmentRepository::isMetricAssignedToUnit($unit_code, $year, $metric_id, 'MANDATORY');
      if (!$is_mandatory) {
        Notices::set($user_id, 'error', 'Metrik ini bukan mandatory untuk unit Anda.');
        self::redirectBack();
      }
      if (MetricCoverageRepository::isMetricCompleteForUnit($unit_code, $year, $metric_id)) {
        Notices::set($user_id, 'error', 'Metrik ini sudah memiliki evidence approved, status NO tidak diperlukan.');
        self::redirectBack();
      }

      MetricNoDataRepository::mark($unit_code, $year, $metric_id, $user_id);
      Notices::set($user_id, 'success', 'Status NO berhasil disimpan untuk metrik mandatory ini.');
      wp_safe_redirect(Url::page('my'));
      exit;
    }

    $action = sanitize_text_field($_POST['spectrum_action']); // draft|submit|update_submit|update_draft etc

    $result = EvidenceService::createOrUpdateFromPost($action);

    if (is_wp_error($result)) {
      Notices::set($user_id, 'error', $result->get_error_message());
      self::redirectBack();
    }

    if ($mode === 'MANDATORY' && $metric_id > 0 && $year > 0) {
      MetricNoDataRepository::unmark(Auth::unitCode($user_id), $year, $metric_id);
    }

    $target_status = (strpos($action, 'submit') !== false) ? 'SUBMITTED' : 'DRAFT';
    $msg = ($target_status === 'SUBMITTED')
      ? 'Evidence berhasil disubmit dan akan direview.'
      : 'Draft evidence berhasil disimpan.';

    Notices::set($user_id, 'success', $msg);
    wp_safe_redirect(Url::page('my'));
    exit;
  }

  private static function handleReview() {
    $evidence_id = (int)$_POST['evidence_id'];

    if (!wp_verify_nonce($_POST['_wpnonce'], 'review_action_' . $evidence_id)) {
      wp_die('Nonce tidak valid');
    }

    $decision = sanitize_text_field($_POST['review_action']); // approve|reject
    $notes = sanitize_textarea_field($_POST['review_notes'] ?? '');
    $score = isset($_POST['review_score']) ? (int)$_POST['review_score'] : 0;

    if ($decision === 'reject' && $notes === '') {
      Notices::set(Auth::userId(), 'error', 'Alasan reject wajib diisi.');
      wp_safe_redirect(Url::page('review'));
      exit;
    }
    if ($decision === 'approve' && ($score < 1 || $score > 5)) {
      Notices::set(Auth::userId(), 'error', 'Score wajib diisi saat approve.');
      wp_safe_redirect(Url::page('review'));
      exit;
    }

    if ($decision === 'approve') {
      $notes = trim(($notes ? $notes . ' | ' : '') . 'Score: ' . $score . '/5');
    }

    $mapped = ($decision === 'approve') ? 'APPROVED' : (($decision === 'reject') ? 'REJECTED' : '');

    $res = ReviewService::applyDecision($evidence_id, $mapped, $notes);

    if (is_wp_error($res)) {
      Notices::set(Auth::userId(), 'error', $res->get_error_message());
    } else {
      Notices::set(Auth::userId(), 'success', 'Review berhasil disimpan.');
    }

    wp_safe_redirect(Url::page('review'));
    exit;
  }

  private static function handleDelete() {
    $evidence_id = (int)$_POST['evidence_id'];

    if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_evidence_' . $evidence_id)) {
      wp_die('Nonce tidak valid');
    }

    $res = DeleteService::deleteDraft($evidence_id);

    if (is_wp_error($res)) {
      Notices::set(Auth::userId(), 'error', $res->get_error_message());
    } else {
      Notices::set(Auth::userId(), 'success', 'Evidence draft berhasil dihapus.');
    }

    self::redirectBack();
  }
}
