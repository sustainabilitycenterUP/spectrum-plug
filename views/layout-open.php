<?php
use Spectrum\Evidence\Core\Auth;
use Spectrum\Evidence\Core\Url;

if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) { echo '<p>Silakan login.</p>'; return; }

$current_user = wp_get_current_user();
$user_name = $current_user->display_name ?: $current_user->user_login;
$initials = strtoupper(substr($user_name, 0, 2));
$role_badge = Auth::isReviewer() ? 'Kontributor & Reviewer' : 'Kontributor';

$active = isset($active) ? $active : '';
?>
<div class="spectrum-app">
  <header class="spectrum-header">
    <div class="spectrum-header-inner">
      <a class="sp-brand" href="<?php echo esc_url(\Spectrum\Evidence\Core\Url::page('dashboard')); ?>" style="text-decoration:none;color:inherit;">
        <div class="sp-brand-logo-img">
          <img src="https://super.universitaspertamina.ac.id/wp-content/uploads/2026/04/spectrum-logo.png" alt="SPECTRUM Logo">
        </div>
        <div class="sp-brand-desc">
          SDG PERFORMANCE EVALUATION,<br>
          COMPLIANCE TRACKING, AND REPORTING UNIFIED MONITOR
        </div>
      </a>
      <div class="sp-header-right">
        <span class="sp-role-badge"><?php echo esc_html($role_badge); ?></span>
        <div class="sp-user-pill">
          <div class="sp-user-avatar"><?php echo esc_html($initials); ?></div>
          <span><?php echo esc_html($user_name); ?></span>
        </div>
      </div>
    </div>
  </header>

  <div class="sp-main">
    <aside class="sp-sidebar">
      <div class="sp-sidebar-card">
        <div class="sp-sidebar-title">Main</div>
        <ul class="sp-sidebar-menu">
          <li class="sp-sidebar-item">
            <a class="sp-sidebar-link <?php echo ($active==='dashboard'?'active':''); ?>"
               href="<?php echo esc_url(\Spectrum\Evidence\Core\Url::page('dashboard')); ?>">
              <span class="sp-dot"></span>Dashboard
            </a>
          </li>
          
          <li class="sp-sidebar-item">
            <a class="sp-sidebar-link <?php echo ($active==='my'?'active':''); ?>"
              href="<?php echo esc_url(Url::page('my')); ?>">
              <span class="sp-dot"></span>Evidence Saya
            </a>
          </li>

          <li class="sp-sidebar-item">
            <a class="sp-sidebar-link <?php echo ($active==='new'?'active':''); ?>"
              href="<?php echo esc_url(Url::page('new')); ?>">
              <span class="sp-dot"></span>Buat Evidence Baru
            </a>
          </li>

          <li class="sp-sidebar-item">
            <a class="sp-sidebar-link <?php echo ($active==='metrics'?'active':''); ?>"
              href="<?php echo esc_url(Url::page('metrics')); ?>">
              <span class="sp-dot"></span>SDG &amp; Indikator THE
            </a>
          </li>

          <?php if (Auth::isReviewer()) : ?>
          <li class="sp-sidebar-item" style="margin-top:6px;">
            <a class="sp-sidebar-link <?php echo ($active==='review'?'active':''); ?>"
              href="<?php echo esc_url(Url::page('review')); ?>">
              <span class="sp-dot"></span>Evidence untuk Direview
            </a>
          </li>
          <?php endif; ?>

          <?php if (Auth::isReviewer()) : ?>
          <li class="sp-sidebar-item">
            <a class="sp-sidebar-link <?php echo ($active==='approved'?'active':''); ?>"
               href="<?php echo esc_url(Url::page('approved')); ?>">
              <span class="sp-dot"></span>Approved Evidence
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </div>
    </aside>

    <main class="sp-content">
