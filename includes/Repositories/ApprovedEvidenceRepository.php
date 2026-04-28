<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class ApprovedEvidenceRepository {

  public static function list($filters = array()) {
    global $wpdb;

    $e  = Db::table('spectrum_evidence');
    $em = Db::table('spectrum_evidence_metric');
    $m  = Db::table('spectrum_metric');
    $nd = Db::table('spectrum_metric_no_data');

    $hasNumeric = EvidenceRepository::hasColumn('numeric_value');
    $hasAttachment = EvidenceRepository::hasColumn('attachment_id');

    $unitWhere = '';
    $params = array();
    if (!empty($filters['unit_code'])) {
      $unitWhere .= " AND e.unit_code = %s";
      $params[] = $filters['unit_code'];
    }
    if (!empty($filters['sdg_number'])) {
      $unitWhere .= " AND m.sdg_number = %d";
      $params[] = (int)$filters['sdg_number'];
    }

    $approvedSelect = "
      SELECT
        e.id,
        e.year,
        e.unit_code,
        e.status,
        e.title,
        e.summary,
        e.link_url,
        " . ($hasAttachment ? "e.attachment_id" : "NULL") . " AS attachment_id,
        " . ($hasNumeric ? "e.numeric_value" : "NULL") . " AS numeric_value,
        m.sdg_number,
        m.metric_code,
        m.metric_title,
        m.metric_question,
        e.updated_at,
        e.created_at,
        0 AS is_no_data
      FROM {$e} e
      LEFT JOIN {$em} em ON em.evidence_id = e.id
      LEFT JOIN {$m} m ON m.id = em.metric_id
      WHERE e.status = 'APPROVED' {$unitWhere}
    ";

    $noWhere = '';
    if (!empty($filters['unit_code'])) {
      $noWhere .= " AND nd.unit_code = %s";
      $params[] = $filters['unit_code'];
    }
    if (!empty($filters['sdg_number'])) {
      $noWhere .= " AND m.sdg_number = %d";
      $params[] = (int)$filters['sdg_number'];
    }

    $noDataSelect = "
      SELECT
        0 AS id,
        nd.year,
        nd.unit_code,
        'NO_DATA' AS status,
        CONCAT('Not Available - ', m.metric_code) AS title,
        'Not Available (mandatory metric).' AS summary,
        '' AS link_url,
        NULL AS attachment_id,
        NULL AS numeric_value,
        m.sdg_number,
        m.metric_code,
        m.metric_title,
        m.metric_question,
        nd.created_at AS updated_at,
        nd.created_at AS created_at,
        1 AS is_no_data
      FROM {$nd} nd
      INNER JOIN {$m} m ON m.id = nd.metric_id
      WHERE 1=1 {$noWhere}
    ";

    $sql = "SELECT * FROM ( {$approvedSelect} UNION ALL {$noDataSelect} ) x ORDER BY x.updated_at DESC, x.created_at DESC";

    if (!empty($params)) {
      $sql = $wpdb->prepare($sql, $params);
    }

    return $wpdb->get_results($sql);
  }

  public static function distinctUnits() {
    global $wpdb;
    $e = Db::table('spectrum_evidence');
    $nd = Db::table('spectrum_metric_no_data');

    $sql = "
      SELECT unit_code FROM (
        SELECT unit_code FROM {$e} WHERE unit_code <> ''
        UNION
        SELECT unit_code FROM {$nd} WHERE unit_code <> ''
      ) u
      ORDER BY unit_code ASC
    ";

    return $wpdb->get_col($sql);
  }
}
