<?php
namespace Spectrum\Evidence\Shortcodes;

use Spectrum\Evidence\Core\Assets;
use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Core\View;
use Spectrum\Evidence\Repositories\DashboardRepository;

if (!defined('ABSPATH')) exit;

final class DashboardShortcode {
  public static function render() {
    if (!Auth::isLoggedIn()) return '<p>Silakan login.</p>';

    Assets::enqueueOnce();

    // sementara dashboard difokuskan untuk metrik tahun 2027
    $year = 2027;

    $data = array(
      'active' => 'dashboard',
      'year'   => $year,
      'overview' => DashboardRepository::mandatoryOverview($year),
      'sdg_summary' => DashboardRepository::sdgSummary($year),
      'unit'   => DashboardRepository::unitCounts($year),
      'general_unit' => DashboardRepository::generalApprovedByUnit($year),
    );

    return View::render('dashboard', $data);
  }
}
