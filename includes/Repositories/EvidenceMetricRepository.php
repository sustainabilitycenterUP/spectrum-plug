<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class EvidenceMetricRepository {
  public static function table() { return Db::table('spectrum_evidence_metric'); }

  public static function getMetricIdByEvidence($evidence_id) {
    global $wpdb;
    $t = self::table();
    return (int)$wpdb->get_var($wpdb->prepare(
      "SELECT metric_id FROM {$t} WHERE evidence_id=%d",
      (int)$evidence_id
    ));
  }

  public static function setSingleMetric($evidence_id, $metric_id) {
    global $wpdb;
    $t = self::table();
    $wpdb->delete($t, array('evidence_id' => (int)$evidence_id), array('%d'));
    if ((int)$metric_id > 0) {
      $wpdb->insert($t, array(
        'evidence_id' => (int)$evidence_id,
        'metric_id'   => (int)$metric_id,
        'created_at'  => current_time('mysql'),
      ), array('%d','%d','%s'));
    }
  }
}