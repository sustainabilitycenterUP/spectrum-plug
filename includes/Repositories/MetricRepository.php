<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class MetricRepository {

  public static function tMetric() {
    return Db::table('spectrum_metric');
  }

  public static function tYearMetric() {
    return Db::table('spectrum_year_metric');
  }

  public static function activeMetricsWithYear() {
    global $wpdb;
    $m = self::tMetric();
    $y = self::tYearMetric();

    return $wpdb->get_results("
      SELECT 
        m.id,
        m.metric_code,
        m.metric_title,
        m.metric_question,
        m.metric_desc,
        m.metric_note,
        m.metric_type,
        m.sdg_number,
        y.year
      FROM {$m} m
      JOIN {$y} y ON y.metric_id = m.id
      WHERE y.is_active = 1
      ORDER BY y.year DESC, m.sdg_number ASC, m.metric_code ASC
    ");
  }

  public static function activeYears() {
    global $wpdb;
    $y = self::tYearMetric();

    return $wpdb->get_col("
      SELECT DISTINCT year
      FROM {$y}
      WHERE is_active = 1
      ORDER BY year DESC
    ");
  }

  public static function catalog($filters = array()) {
    global $wpdb;
    $m = self::tMetric();
    $y = self::tYearMetric();

    $where = "WHERE y.is_active = 1";
    $params = array();

    if (!empty($filters['year'])) {
      $where .= " AND y.year = %d";
      $params[] = (int)$filters['year'];
    }

    if (!empty($filters['sdg_number'])) {
      $where .= " AND m.sdg_number = %d";
      $params[] = (int)$filters['sdg_number'];
    }

    if (!empty($filters['metric_type'])) {
      $where .= " AND m.metric_type = %s";
      $params[] = $filters['metric_type'];
    }

    if (!empty($filters['keyword'])) {
      $where .= " AND (m.metric_code LIKE %s OR m.metric_title LIKE %s OR m.metric_question LIKE %s)";
      $keyword = '%' . $wpdb->esc_like($filters['keyword']) . '%';
      $params[] = $keyword;
      $params[] = $keyword;
      $params[] = $keyword;
    }

    $sql = "
      SELECT 
        m.id,
        m.sdg_number,
        m.metric_code,
        m.metric_type,
        m.metric_title,
        m.metric_question,
        m.metric_desc,
        m.metric_note,
        y.year
      FROM {$m} m
      JOIN {$y} y ON y.metric_id = m.id
      {$where}
      ORDER BY y.year DESC, m.sdg_number ASC, m.metric_code ASC
    ";

    if (!empty($params)) {
      $sql = $wpdb->prepare($sql, $params);
    }

    return $wpdb->get_results($sql);
  }

  public static function getActiveMetricsByYear($year) {
    global $wpdb;
    $m = self::tMetric();
    $y = self::tYearMetric();

    return $wpdb->get_results($wpdb->prepare("
      SELECT 
        m.id,
        m.metric_code,
        m.metric_title,
        m.metric_question,
        m.metric_desc,
        m.metric_note,
        m.metric_type,
        m.sdg_number,
        y.year
      FROM {$m} m
      JOIN {$y} y ON y.metric_id = m.id
      WHERE y.year = %d
        AND y.is_active = 1
      ORDER BY m.sdg_number ASC, m.metric_code ASC
    ", (int)$year));
  }

  public static function getMetricById($metric_id) {
    global $wpdb;
    $m = self::tMetric();

    return $wpdb->get_row($wpdb->prepare("
      SELECT *
      FROM {$m}
      WHERE id = %d
    ", (int)$metric_id));
  }
}
