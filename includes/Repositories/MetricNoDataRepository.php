<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class MetricNoDataRepository {
  public static function table() { return Db::table('spectrum_metric_no_data'); }

  public static function mark($unit_code, $year, $metric_id, $submitter_id) {
    global $wpdb;
    $t = self::table();

    $exists = $wpdb->get_var($wpdb->prepare(
      "SELECT id FROM {$t} WHERE unit_code=%s AND year=%d AND metric_id=%d LIMIT 1",
      $unit_code, (int)$year, (int)$metric_id
    ));

    if ($exists) {
      return $wpdb->update($t, array(
        'submitter_id' => (int)$submitter_id,
        'created_at' => current_time('mysql'),
      ), array('id' => (int)$exists), array('%d','%s'), array('%d'));
    }

    return $wpdb->insert($t, array(
      'unit_code' => $unit_code,
      'year' => (int)$year,
      'metric_id' => (int)$metric_id,
      'submitter_id' => (int)$submitter_id,
      'created_at' => current_time('mysql'),
    ), array('%s','%d','%d','%d','%s'));
  }

  public static function getMetricIdsByUnitAndYear($unit_code, $year) {
    global $wpdb;
    $t = self::table();
    return $wpdb->get_col($wpdb->prepare(
      "SELECT metric_id FROM {$t} WHERE unit_code=%s AND year=%d",
      $unit_code, (int)$year
    ));
  }

  public static function unmark($unit_code, $year, $metric_id) {
    global $wpdb;
    $t = self::table();
    return $wpdb->delete($t, array(
      'unit_code' => $unit_code,
      'year' => (int)$year,
      'metric_id' => (int)$metric_id,
    ), array('%s','%d','%d'));
  }
}
