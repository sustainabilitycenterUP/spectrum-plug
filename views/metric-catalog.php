<?php
use Spectrum\Evidence\Core\Url;

if (!defined('ABSPATH')) exit;

$active = 'metrics';
include __DIR__ . '/layout-open.php';
?>

<div class="sp-page-header">
  <div class="sp-page-title-block">
    <h1>SDG &amp; Indikator THE</h1>
    <p>Daftar metrik aktif per tahun pelaporan, lengkap dengan pencarian dan filter SDG.</p>
  </div>
</div>

<section class="sp-card">
  <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin-bottom:14px;">
    <div>
      <label class="sp-label">Tahun</label>
      <select name="f_year" class="sp-select" style="min-width:160px;">
        <option value="">Semua Tahun</option>
        <?php foreach ((array)$years as $y): ?>
          <option value="<?php echo (int)$y; ?>" <?php selected((int)$filters['year'], (int)$y); ?>>
            <?php echo esc_html($y); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="sp-label">SDG</label>
      <select name="f_sdg" class="sp-select" style="min-width:160px;">
        <option value="">Semua SDG</option>
        <?php for ($i=1; $i<=17; $i++): ?>
          <option value="<?php echo (int)$i; ?>" <?php selected((int)$filters['sdg_number'], (int)$i); ?>>
            SDG <?php echo (int)$i; ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>

    <div>
      <label class="sp-label">Tipe</label>
      <select name="f_type" class="sp-select" style="min-width:180px;">
        <option value="">Semua Tipe</option>
        <?php foreach (array('numeric', 'initiatives', 'policy') as $tp): ?>
          <option value="<?php echo esc_attr($tp); ?>" <?php selected($filters['metric_type'], $tp); ?>>
            <?php echo esc_html(ucfirst($tp)); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="min-width:260px;">
      <label class="sp-label">Kata Kunci</label>
      <input type="text" name="q" class="sp-input" value="<?php echo esc_attr($filters['keyword']); ?>" placeholder="kode metrik / judul / pertanyaan">
    </div>

    <div>
      <button class="sp-btn-primary" type="submit">Terapkan</button>
      <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('metrics')); ?>">Reset</a>
    </div>
  </form>

  <?php if (empty($rows)): ?>
    <div style="padding:14px;color:#6b7280;">Tidak ada metrik sesuai filter.</div>
  <?php else: ?>
    <div style="width:100%;overflow-x:auto;">
      <table class="sp-table" style="min-width:1100px;">
        <thead>
          <tr>
            <th>Tahun</th>
            <th>SDG</th>
            <th>Kode</th>
            <th>Tipe</th>
            <th>Judul Metrik</th>
            <th>Pertanyaan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?php echo (int)$r->year; ?></td>
              <td><?php echo esc_html('SDG ' . $r->sdg_number); ?></td>
              <td><?php echo esc_html($r->metric_code); ?></td>
              <td><?php echo esc_html($r->metric_type); ?></td>
              <td><?php echo esc_html($r->metric_title); ?></td>
              <td><?php echo esc_html($r->metric_question ?: '—'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/layout-close.php'; ?>
