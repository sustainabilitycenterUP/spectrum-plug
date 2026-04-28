<?php
use Spectrum\Evidence\Core\Url;

if (!defined('ABSPATH')) exit;

include __DIR__ . '/layout-open.php';
?>

<div class="sp-page-header">
  <div class="sp-page-title-block">
    <h1>Approved Evidence</h1>
    <p>Daftar evidence yang sudah <strong>APPROVED</strong>. Gunakan filter Unit atau SDG.</p>
  </div>
</div>

<section class="sp-card">
  <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin-bottom:14px;">
    <div>
      <label class="sp-label">Filter Unit</label>
      <select name="unit_code" class="sp-select" style="min-width:220px;">
        <option value="">Semua Unit</option>
        <?php foreach ((array)$units as $u): ?>
          <option value="<?php echo esc_attr($u); ?>" <?php selected($filters['unit_code'], $u); ?>>
            <?php echo esc_html($u); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="sp-label">Filter SDG</label>
      <select name="sdg_number" class="sp-select" style="min-width:160px;">
        <option value="">Semua SDG</option>
        <?php for ($i=1;$i<=17;$i++): ?>
          <option value="<?php echo $i; ?>" <?php selected((int)$filters['sdg_number'], $i); ?>>
            SDG <?php echo $i; ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>

    <div>
      <button class="sp-btn-primary" type="submit">Terapkan Filter</button>
      <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('approved')); ?>">Reset</a>
      <?php
        $export_params = array();
        if (!empty($filters['unit_code'])) $export_params['unit_code'] = $filters['unit_code'];
        if (!empty($filters['sdg_number'])) $export_params['sdg_number'] = (int)$filters['sdg_number'];
        $export_params['export'] = 'csv';
      ?>
      <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('approved', $export_params)); ?>">Export CSV</a>
    </div>
  </form>

  <?php if (empty($rows)): ?>
    <div style="padding:14px;color:#6b7280;">Belum ada evidence approved (atau hasil filter kosong).</div>
  <?php else: ?>
    <div style="width:100%;overflow-x:auto;">
      <table class="sp-table sp-datatable" style="min-width:980px;">
        <thead>
          <tr>
            <th>SDG</th>
            <th>Metrik</th>
            <th>Judul Evidence</th>
            <th>Link Dokumen / Evidence</th>
            <th>Catatan Evidence</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?php echo $r->sdg_number ? esc_html('SDG '.$r->sdg_number) : '—'; ?></td>
              <td><?php echo $r->metric_code ? esc_html($r->metric_code) : '—'; ?></td>
              <td><?php echo esc_html($r->title); ?></td>
              <td>
                <?php
                  $attachment_url = !empty($r->attachment_id) ? wp_get_attachment_url((int)$r->attachment_id) : '';
                  $evidence_url = !empty($r->link_url) ? $r->link_url : $attachment_url;
                ?>
                <?php if (!empty($evidence_url)): ?>
                  <a target="_blank" href="<?php echo esc_url($evidence_url); ?>">Buka Link</a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td>
                <?php
                  $txt = trim((string)($r->summary ?? ''));
                  echo $txt !== '' ? esc_html(mb_strimwidth($txt, 0, 90, '...')) : '—';
                ?>
              </td>
              <td>
                <?php if (!empty($r->id)): ?>
                  <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('detail', array('evidence_id'=>$r->id))); ?>">
                    Detail
                  </a>
                <?php else: ?>
                  <span class="sp-pill">NO Data</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/layout-close.php'; ?>
