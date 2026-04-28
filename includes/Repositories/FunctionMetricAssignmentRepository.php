<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class FunctionMetricAssignmentRepository {

  public static function table() {
    return Db::table('spectrum_function_metric_assignment');
  }

  public static function getAssignedMetricsByUnitAndYear($unit_code, $year, $category) {
    global $wpdb;

    $t  = self::table();
    $m  = Db::table('spectrum_metric');
    $ym = Db::table('spectrum_year_metric');

    return $wpdb->get_results($wpdb->prepare("
      SELECT 
        fma.metric_id,
        fma.category,
        m.sdg_number,
        m.metric_code,
        m.metric_type,
        m.metric_title,
        m.metric_question,
        m.metric_desc,
        m.metric_note,
        ym.year
      FROM {$t} fma
      INNER JOIN {$m} m
        ON m.id = fma.metric_id
      INNER JOIN {$ym} ym
        ON ym.metric_id = m.id AND ym.year = fma.year AND ym.is_active = 1
      WHERE fma.unit_code = %s
        AND fma.year = %d
        AND fma.category = %s
      ORDER BY m.sdg_number, m.metric_code
    ", $unit_code, (int)$year, $category));
  }

  public static function getAssignedMetricIdsByUnitAndYear($unit_code, $year) {
    global $wpdb;
    $t = self::table();

    return $wpdb->get_col($wpdb->prepare("
      SELECT metric_id
      FROM {$t}
      WHERE unit_code = %s AND year = %d
    ", $unit_code, (int)$year));
  }

  public static function getAssignedMetricIdsByUnitYearCategory($unit_code, $year, $category) {
    global $wpdb;
    $t = self::table();

    return $wpdb->get_col($wpdb->prepare("
      SELECT metric_id
      FROM {$t}
      WHERE unit_code = %s
        AND year = %d
        AND category = %s
    ", $unit_code, (int)$year, $category));
  }

  public static function isMetricAssignedToUnit($unit_code, $year, $metric_id, $category) {
    global $wpdb;
    $t = self::table();

    $found = $wpdb->get_var($wpdb->prepare("
      SELECT id
      FROM {$t}
      WHERE unit_code = %s
        AND year = %d
        AND metric_id = %d
        AND category = %s
      LIMIT 1
    ", $unit_code, (int)$year, (int)$metric_id, $category));

    return !empty($found);
  }

  public static function getAllAssignmentsByUnitAndYear($unit_code, $year) {
    global $wpdb;
    $t  = self::table();
    $m  = Db::table('spectrum_metric');
    $ym = Db::table('spectrum_year_metric');

    return $wpdb->get_results($wpdb->prepare("
      SELECT 
        fma.metric_id,
        fma.category,
        m.sdg_number,
        m.metric_code,
        m.metric_type,
        m.metric_title,
        m.metric_question,
        m.metric_desc,
        m.metric_note,
        ym.year
      FROM {$t} fma
      INNER JOIN {$m} m
        ON m.id = fma.metric_id
      INNER JOIN {$ym} ym
        ON ym.metric_id = m.id AND ym.year = fma.year AND ym.is_active = 1
      WHERE fma.unit_code = %s
        AND fma.year = %d
      ORDER BY fma.category, m.sdg_number, m.metric_code
    ", $unit_code, (int)$year));
  }

  public static function countAssignmentsByUnitYearCategory($unit_code, $year, $category) {
    global $wpdb;
    $t = self::table();

    return (int)$wpdb->get_var($wpdb->prepare("
      SELECT COUNT(*)
      FROM {$t}
      WHERE unit_code = %s
        AND year = %d
        AND category = %s
    ", $unit_code, (int)$year, $category));
  }
}
