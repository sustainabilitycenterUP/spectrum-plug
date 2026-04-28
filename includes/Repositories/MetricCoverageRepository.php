<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class MetricCoverageRepository {

  public static function getApprovedMetricIdsByUnitAndYear($unit_code, $year) {
    global $wpdb;

    $e  = Db::table('spectrum_evidence');
    $em = Db::table('spectrum_evidence_metric');

    return $wpdb->get_col($wpdb->prepare("
      SELECT DISTINCT em.metric_id
      FROM {$e} e
      INNER JOIN {$em} em
        ON em.evidence_id = e.id
      WHERE e.unit_code = %s
        AND e.year = %d
        AND e.status = 'APPROVED'
    ", $unit_code, (int)$year));
  }

  public static function isMetricCompleteForUnit($unit_code, $year, $metric_id) {
    global $wpdb;

    $e  = Db::table('spectrum_evidence');
    $em = Db::table('spectrum_evidence_metric');

    $found = $wpdb->get_var($wpdb->prepare("
      SELECT e.id
      FROM {$e} e
      INNER JOIN {$em} em
        ON em.evidence_id = e.id
      WHERE e.unit_code = %s
        AND e.year = %d
        AND em.metric_id = %d
        AND e.status = 'APPROVED'
      LIMIT 1
    ", $unit_code, (int)$year, (int)$metric_id));

    return !empty($found);
  }

  public static function countCompletedAssignedMetrics($unit_code, $year, $category) {
    global $wpdb;

    $e   = Db::table('spectrum_evidence');
    $em  = Db::table('spectrum_evidence_metric');
    $fma = Db::table('spectrum_function_metric_assignment');

    $sql = $wpdb->prepare("
      SELECT COUNT(DISTINCT fma.metric_id)
      FROM {$fma} fma
      WHERE fma.unit_code = %s
        AND fma.year = %d
        AND fma.category = %s
        AND EXISTS (
          SELECT 1
          FROM {$e} e
          INNER JOIN {$em} em
            ON em.evidence_id = e.id
          WHERE e.unit_code = fma.unit_code
            AND e.year = fma.year
            AND em.metric_id = fma.metric_id
            AND e.status = 'APPROVED'
        )
    ", $unit_code, (int)$year, $category);

    return (int)$wpdb->get_var($sql);
  }
}