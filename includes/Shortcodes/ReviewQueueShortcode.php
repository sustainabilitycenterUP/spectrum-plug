<?php
namespace Spectrum\Evidence\Shortcodes;

use Spectrum\Evidence\Core\Assets;
use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Core\Notices;
use Spectrum\Evidence\Core\View;

use Spectrum\Evidence\Repositories\EvidenceRepository;

if (!defined('ABSPATH')) exit;

final class ReviewQueueShortcode {
  public static function render() {
    if (!Auth::isLoggedIn()) return '<p>Silakan login.</p>';
    if (!Auth::isReviewer()) return '<p>Halaman ini hanya untuk reviewer.</p>';
    Assets::enqueueOnce();

    $user_id = Auth::userId();

    return View::render('review-queue', array(
      'notice' => Notices::get($user_id),
      'rows' => EvidenceRepository::listForReview('SUBMITTED'),
    ));
  }
}