<?php
namespace Spectrum\Evidence\Core;

if (!defined('ABSPATH')) exit;

final class Url {

  // kamu bisa ubah di sini kalau slug kamu pakai spasi / beda
  public static function slug($key) {
    $map = array(
      'my'     => 'evidence-saya',
      'new'    => 'buat-evidence-baru',
      'detail' => 'detail-evidence',
      'review' => 'evidence-direview',
      'dash'   => 'dashboard-reviewer',
      'approved' => 'approved-evidence',
      'dashboard' => 'dashboard-spectrum',
      'metrics' => 'sdg-indikator-the',
    );
    return isset($map[$key]) ? $map[$key] : '';
  }

  public static function to($slug = '', $params = array()) {
    $url = site_url('/index.php/' . trim($slug, '/') . '/');
    if (!empty($params)) $url .= '?' . http_build_query($params);
    return $url;
  }

  public static function page($key, $params = array()) {
    return self::to(self::slug($key), $params);
  }
}
