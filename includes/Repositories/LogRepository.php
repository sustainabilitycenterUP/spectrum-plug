<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class LogRepository {

  public static function table() {
    return Db::table('spectrum_evidence_log');
  }

  public static function add($evidence_id, $actor_id, $from, $to, $notes = '') {
    global $wpdb;
    $t = self::table();

    return $wpdb->insert($t, array(
      'evidence_id' => (int)$evidence_id,
      'actor_id'    => $actor_id ? (int)$actor_id : null,
      'from_status' => $from,
      'to_status'   => $to,
      'notes'       => $notes,
      'created_at'  => current_time('mysql'),
    ), array('%d','%d','%s','%s','%s','%s'));
  }

  public static function listByEvidence($evidence_id) {
    global $wpdb;
    $t = self::table();
    return $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM {$t} WHERE evidence_id=%d ORDER BY created_at DESC, id DESC",
      (int)$evidence_id
    ));
  }
}