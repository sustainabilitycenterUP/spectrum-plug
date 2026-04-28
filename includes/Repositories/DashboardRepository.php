<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class DashboardRepository {

  public static function mandatoryOverview($year = 0) {
    global $wpdb;

    $fma = Db::table('spectrum_function_metric_assignment');
    $em  = Db::table('spectrum_evidence_metric');
    $e   = Db::table('spectrum_evidence');
    $nd  = Db::table('spectrum_metric_no_data');

    $where = "WHERE f.category = 'MANDATORY'";
    $params = array();

    if ($year) {
      $where .= " AND f.year = %d";
      $params[] = (int)$year;
    }

    $sql = "
      SELECT
        COUNT(DISTINCT CONCAT(f.unit_code, '#', f.metric_id, '#', f.year)) AS requested_total,
        COUNT(DISTINCT CASE
          WHEN EXISTS (
            SELECT 1
            FROM {$em} em2
            INNER JOIN {$e} e2 ON e2.id = em2.evidence_id
            WHERE em2.metric_id = f.metric_id
              AND e2.unit_code = f.unit_code
              AND e2.year = f.year
              AND e2.status = 'APPROVED'
          ) OR nd.metric_id IS NOT NULL
          THEN CONCAT(f.unit_code, '#', f.metric_id, '#', f.year)
        END) AS confirmed_total,
        COUNT(DISTINCT CASE
          WHEN EXISTS (
            SELECT 1
            FROM {$em} em3
            INNER JOIN {$e} e3 ON e3.id = em3.evidence_id
            WHERE em3.metric_id = f.metric_id
              AND e3.unit_code = f.unit_code
              AND e3.year = f.year
              AND e3.status = 'SUBMITTED'
          )
          AND NOT EXISTS (
            SELECT 1
            FROM {$em} em4
            INNER JOIN {$e} e4 ON e4.id = em4.evidence_id
            WHERE em4.metric_id = f.metric_id
              AND e4.unit_code = f.unit_code
              AND e4.year = f.year
              AND e4.status = 'APPROVED'
          )
          AND nd.metric_id IS NULL
          THEN CONCAT(f.unit_code, '#', f.metric_id, '#', f.year)
        END) AS submitted_total
      FROM {$fma} f
      LEFT JOIN {$nd} nd ON nd.metric_id = f.metric_id
                        AND nd.unit_code = f.unit_code
                        AND nd.year = f.year
      {$where}
    ";

    if (!empty($params)) {
      $sql = $wpdb->prepare($sql, $params);
    }

    $row = $wpdb->get_row($sql);

    $requested = (int)($row->requested_total ?? 0);
    $confirmed = (int)($row->confirmed_total ?? 0);

    return array(
      'requested_total' => $requested,
      'confirmed_total' => $confirmed,
      'submitted_total' => (int)($row->submitted_total ?? 0),
      'percent' => $requested > 0 ? (int)round(($confirmed / $requested) * 100) : 0,
    );
  }

  public static function unitCounts($year = 0) {
    global $wpdb;

    $fma = Db::table('spectrum_function_metric_assignment');
    $em  = Db::table('spectrum_evidence_metric');
    $e   = Db::table('spectrum_evidence');
    $nd  = Db::table('spectrum_metric_no_data');

    $where = "WHERE f.category = 'MANDATORY'";
    $params = array();

    if ($year) {
      $where .= " AND f.year = %d";
      $params[] = (int)$year;
    }

    $sql = "
      SELECT
        f.unit_code,
        COUNT(DISTINCT f.metric_id) AS requested_total,
        COUNT(DISTINCT CASE
          WHEN EXISTS (
            SELECT 1
            FROM {$em} em2
            INNER JOIN {$e} e2 ON e2.id = em2.evidence_id
            WHERE em2.metric_id = f.metric_id
              AND e2.unit_code = f.unit_code
              AND e2.year = f.year
              AND e2.status = 'APPROVED'
          ) THEN f.metric_id
        END) AS approved_total,
        COUNT(DISTINCT CASE
          WHEN nd.metric_id IS NOT NULL
           AND NOT EXISTS (
            SELECT 1
            FROM {$em} em3
            INNER JOIN {$e} e3 ON e3.id = em3.evidence_id
            WHERE em3.metric_id = f.metric_id
              AND e3.unit_code = f.unit_code
              AND e3.year = f.year
              AND e3.status = 'APPROVED'
           )
          THEN f.metric_id
        END) AS no_data_total
      FROM {$fma} f
      LEFT JOIN {$nd} nd ON nd.metric_id = f.metric_id
                        AND nd.unit_code = f.unit_code
                        AND nd.year = f.year
      {$where}
      GROUP BY f.unit_code
      ORDER BY f.unit_code ASC
    ";

    if (!empty($params)) {
      $sql = $wpdb->prepare($sql, $params);
    }

    $rows = $wpdb->get_results($sql);

    foreach ((array)$rows as $r) {
      $requested = (int)($r->requested_total ?? 0);
      $approved = (int)($r->approved_total ?? 0);
      $noData = (int)($r->no_data_total ?? 0);
      $r->confirmed_total = min($requested, $approved + $noData);
      $r->percent = $requested > 0 ? (int)round(($r->confirmed_total / $requested) * 100) : 0;
      $r->approved_percent = $requested > 0 ? ($approved / $requested) * 100 : 0;
      $r->no_data_percent = $requested > 0 ? ($noData / $requested) * 100 : 0;
    }

    return $rows;
  }

  public static function generalApprovedByUnit($year = 0) {
    global $wpdb;

    $e   = Db::table('spectrum_evidence');
    $em  = Db::table('spectrum_evidence_metric');
    $fma = Db::table('spectrum_function_metric_assignment');

    $where = "WHERE e.status = 'APPROVED'";
    $params = array();

    if ($year) {
      $where .= " AND e.year = %d";
      $params[] = (int)$year;
    }

    $sql = "
      SELECT
        e.unit_code,
        COUNT(DISTINCT e.id) AS general_approved_total
      FROM {$e} e
      INNER JOIN {$em} em ON em.evidence_id = e.id
      {$where}
        AND NOT EXISTS (
          SELECT 1
          FROM {$fma} f
          WHERE f.unit_code = e.unit_code
            AND f.year = e.year
            AND f.metric_id = em.metric_id
            AND f.category = 'MANDATORY'
        )
      GROUP BY e.unit_code
      ORDER BY e.unit_code ASC
    ";

    if (!empty($params)) {
      $sql = $wpdb->prepare($sql, $params);
    }

    return $wpdb->get_results($sql);
  }

  public static function sdgSummary($year = 0) {
    global $wpdb;
    $s  = Db::table('spectrum_sdg');
    $m  = Db::table('spectrum_metric');
    $em = Db::table('spectrum_evidence_metric');
    $e  = Db::table('spectrum_evidence');

    $joinYear = '';
    $params = array();
    if ($year) {
      $joinYear = ' AND e.year = %d';
      $params[] = (int)$year;
    }

    $sql = "
      SELECT
        s.sdg_number,
        s.sdg_title,
        COUNT(DISTINCT e.id) AS total,
        COUNT(DISTINCT CASE WHEN e.status='SUBMITTED' THEN e.id END) AS submitted,
        COUNT(DISTINCT CASE WHEN e.status='APPROVED' THEN e.id END) AS approved,
        COUNT(DISTINCT CASE WHEN e.status='REJECTED' THEN e.id END) AS rejected
      FROM {$s} s
      LEFT JOIN {$m} m ON m.sdg_number = s.sdg_number
      LEFT JOIN {$em} em ON em.metric_id = m.id
      LEFT JOIN {$e} e ON e.id = em.evidence_id {$joinYear}
      GROUP BY s.sdg_number, s.sdg_title
      ORDER BY s.sdg_number ASC
    ";

    if (!empty($params)) {
      $sql = $wpdb->prepare($sql, $params);
    }

    return $wpdb->get_results($sql);
  }
}
