<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class EvidenceRepository {

  public static function table() {
    return Db::table('spectrum_evidence');
  }

  public static function find($id) {
    global $wpdb;
    $t = self::table();
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t} WHERE id=%d", (int)$id));
  }

  public static function findBySubmitter($submitter_id) {
    global $wpdb;
    $t = self::table();
    return $wpdb->get_results($wpdb->prepare(
      "SELECT id, year, title, status, unit_code, updated_at, created_at
       FROM {$t}
       WHERE submitter_id=%d
       ORDER BY updated_at DESC, created_at DESC",
      (int)$submitter_id
    ));
  }

  public static function findBySubmitterFiltered($submitter_id, $filters = array()) {
    global $wpdb;

    $e  = self::table();
    $em = Db::table('spectrum_evidence_metric');
    $m  = Db::table('spectrum_metric');

    $select_extra = '';
    if (self::hasColumn('numeric_value')) {
      $select_extra .= ', e.numeric_value';
    }
    if (self::hasColumn('attachment_id')) {
      $select_extra .= ', e.attachment_id';
    }

    $where = "WHERE e.submitter_id = %d";
    $params = array((int)$submitter_id);

    if (!empty($filters['year'])) {
      $where .= " AND e.year = %d";
      $params[] = (int)$filters['year'];
    }

    if (!empty($filters['status'])) {
      $where .= " AND e.status = %s";
      $params[] = $filters['status'];
    }

    if (!empty($filters['sdg_number'])) {
      $where .= " AND m.sdg_number = %d";
      $params[] = (int)$filters['sdg_number'];
    }

    if (!empty($filters['keyword'])) {
      $where .= " AND e.title LIKE %s";
      $params[] = '%' . $wpdb->esc_like($filters['keyword']) . '%';
    }

    $sql = "
      SELECT e.id, e.year, e.title, e.summary, e.status, e.unit_code, e.link_url, e.updated_at, e.created_at
             {$select_extra},
             m.sdg_number, m.metric_code, m.metric_title, m.metric_question
      FROM {$e} e
      LEFT JOIN {$em} em ON em.evidence_id = e.id
      LEFT JOIN {$m}  m  ON m.id = em.metric_id
      {$where}
      ORDER BY e.updated_at DESC, e.created_at DESC
    ";

    $sql = $wpdb->prepare($sql, $params);
    return $wpdb->get_results($sql);
  }

  public static function distinctYearsBySubmitter($submitter_id) {
    global $wpdb;
    $t = self::table();
    return $wpdb->get_col($wpdb->prepare(
      "SELECT DISTINCT year FROM {$t} WHERE submitter_id = %d ORDER BY year DESC",
      (int)$submitter_id
    ));
  }


  public static function hasColumn($column) {
    global $wpdb;
    $t = self::table();
    $col = sanitize_key($column);
    if ($col === '') return false;
    $sql = $wpdb->prepare("SHOW COLUMNS FROM {$t} LIKE %s", $col);
    $res = $wpdb->get_var($sql);
    return !empty($res);
  }

  public static function insert($data) {
    global $wpdb;
    $t = self::table();
    $ok = $wpdb->insert($t, $data);
    if (!$ok) return false;
    return (int)$wpdb->insert_id;
  }

  public static function update($id, $data) {
    global $wpdb;
    $t = self::table();
    return $wpdb->update($t, $data, array('id' => (int)$id));
  }

  public static function delete($id) {
    global $wpdb;
    $t = self::table();
    return $wpdb->delete($t, array('id' => (int)$id));
  }

  public static function listForReview($status = '') {
    global $wpdb;
    $t = self::table();
    $em = Db::table('spectrum_evidence_metric');
    $m = Db::table('spectrum_metric');

    if ($status) {
      return $wpdb->get_results($wpdb->prepare(
        "SELECT e.id, e.unit_code, e.status, e.updated_at, e.created_at,
                m.sdg_number, m.metric_code, m.metric_title
         FROM {$t} e
         LEFT JOIN {$em} em ON em.evidence_id = e.id
         LEFT JOIN {$m} m ON m.id = em.metric_id
         WHERE e.status=%s
         ORDER BY e.updated_at DESC, e.created_at DESC",
        $status
      ));
    }

    return $wpdb->get_results(
      "SELECT e.id, e.unit_code, e.status, e.updated_at, e.created_at,
              m.sdg_number, m.metric_code, m.metric_title
       FROM {$t} e
       LEFT JOIN {$em} em ON em.evidence_id = e.id
       LEFT JOIN {$m} m ON m.id = em.metric_id
       WHERE e.status='SUBMITTED'
       ORDER BY e.updated_at DESC, e.created_at DESC"
    );
  }
}
