<?php
namespace Spectrum\Evidence\Repositories;

use Spectrum\Evidence\Core\Db;

if (!defined('ABSPATH')) exit;

final class YearMetricRepository {
  public static function table() { return Db::table('spectrum_year_metric'); }

  public static function yearsActiveDistinct() {
    global $wpdb;
    $t = self::table();
    return $wpdb->get_col("SELECT DISTINCT year FROM {$t} WHERE is_active=1 ORDER BY year DESC");
  }
}