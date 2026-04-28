<?php
namespace Spectrum\Evidence\Shortcodes;

use Spectrum\Evidence\Core\Assets;
use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Core\Notices;
use Spectrum\Evidence\Core\View;

use Spectrum\Evidence\Repositories\MetricRepository;
use Spectrum\Evidence\Repositories\FunctionMetricAssignmentRepository;
use Spectrum\Evidence\Repositories\MetricNoDataRepository;
use Spectrum\Evidence\Repositories\MetricCoverageRepository;

if (!defined('ABSPATH')) exit;

final class EvidenceFormV2Shortcode {
  public static function render() {
    if (!Auth::isLoggedIn()) return '<p>Silakan login untuk mengisi evidence.</p>';
    Assets::enqueueOnce();

    $user_id = Auth::userId();
    $unit_code = Auth::unitCode($user_id);
    $year = 2027;

    $mandatory_metrics = FunctionMetricAssignmentRepository::getAssignedMetricsByUnitAndYear($unit_code, $year, 'MANDATORY');
    $mandatory_ids = FunctionMetricAssignmentRepository::getAssignedMetricIdsByUnitAndYear($unit_code, $year);
    $no_data_ids = MetricNoDataRepository::getMetricIdsByUnitAndYear($unit_code, $year);
    $approved_ids = MetricCoverageRepository::getApprovedMetricIdsByUnitAndYear($unit_code, $year);
    $approved_lookup = array();
    foreach ((array)$approved_ids as $mid) $approved_lookup[(int)$mid] = true;
    $no_lookup = array();
    foreach ((array)$no_data_ids as $mid) $no_lookup[(int)$mid] = true;

    $formatted_mandatory = array();
    foreach ((array)$mandatory_metrics as $m) {
      $id = (int)$m->metric_id;
      $status = !empty($approved_lookup[$id]) ? 'Complete' : (!empty($no_lookup[$id]) ? 'No data' : 'Uncompleted');
      $m->label = $m->metric_code . ' – ' . $m->metric_title . ' [' . $status . ']';
      $formatted_mandatory[] = $m;
    }

    $active_metrics = MetricRepository::getActiveMetricsByYear($year);
    $mandatory_lookup = array();
    foreach ((array)$mandatory_ids as $mid) $mandatory_lookup[(int)$mid] = true;

    $general_metrics = array();
    foreach ((array)$active_metrics as $m) {
      if (!empty($mandatory_lookup[(int)$m->id])) continue;
      $id = (int)$m->id;
      $status = !empty($approved_lookup[$id]) ? 'Complete' : 'Uncompleted';
      $m->label = $m->metric_code . ' – ' . $m->metric_title . ' [' . $status . ']';
      $general_metrics[] = $m;
    }

    return View::render('evidence-form-v2', array(
      'active' => 'new',
      'notice' => Notices::get($user_id),
      'year' => $year,
      'mandatory_metrics' => $formatted_mandatory,
      'general_metrics' => $general_metrics,
      'no_data_ids' => array_map('intval', (array)$no_data_ids),
    ));
  }
}
