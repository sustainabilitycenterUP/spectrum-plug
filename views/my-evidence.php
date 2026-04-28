<?php
use Spectrum\Evidence\Core\Url;

if (!defined('ABSPATH')) exit;

$active = 'my';
include __DIR__ . '/layout-open.php';
?>

<div class="sp-page-header">
  <div class="sp-page-title-block">
    <h1>Evidence Saya</h1>
    <p>Lihat dan kelola seluruh evidence yang pernah Anda ajukan. Akun: <strong><?php echo esc_html($email); ?></strong></p>
  </div>
  <a href="<?php echo esc_url(Url::page('new')); ?>" class="sp-btn-primary">+ Buat Evidence Baru</a>
</div>

<section class="sp-card">
  <?php if (!empty($notice) && !empty($notice['messages'])): ?>
    <div class="sp-alert <?php echo ($notice['type']==='success')?'sp-alert-success':'sp-alert-error'; ?>">
      <ul>
        <?php foreach ((array)$notice['messages'] as $m): ?><li><?php echo esc_html($m); ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (empty($rows)): ?>
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
        <label class="sp-label">Status</label>
        <select name="f_status" class="sp-select" style="min-width:180px;">
          <option value="">Semua Status</option>
          <?php foreach (array('DRAFT','SUBMITTED','APPROVED','REJECTED') as $st): ?>
            <option value="<?php echo esc_attr($st); ?>" <?php selected($filters['status'], $st); ?>>
              <?php echo esc_html($st); ?>
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

      <div style="min-width:220px;">
        <label class="sp-label">Cari Judul</label>
        <input type="text" name="q" class="sp-input" value="<?php echo esc_attr($filters['keyword']); ?>" placeholder="contoh: food waste">
      </div>

      <div>
        <button class="sp-btn-primary" type="submit">Terapkan</button>
        <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('my')); ?>">Reset</a>
        <?php
          $export_params = array();
          if (!empty($filters['year'])) $export_params['f_year'] = (int)$filters['year'];
          if (!empty($filters['status'])) $export_params['f_status'] = $filters['status'];
          if (!empty($filters['sdg_number'])) $export_params['f_sdg'] = (int)$filters['sdg_number'];
          if (!empty($filters['keyword'])) $export_params['q'] = $filters['keyword'];
          $export_params['export'] = 'csv';
        ?>
        <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('my', $export_params)); ?>">Export CSV</a>
      </div>
    </form>

    <div class="sp-empty">Belum ada evidence. Klik <strong>"Buat Evidence Baru"</strong> untuk mulai.</div>
  <?php else: ?>
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
        <label class="sp-label">Status</label>
        <select name="f_status" class="sp-select" style="min-width:180px;">
          <option value="">Semua Status</option>
          <?php foreach (array('DRAFT','SUBMITTED','APPROVED','REJECTED') as $st): ?>
            <option value="<?php echo esc_attr($st); ?>" <?php selected($filters['status'], $st); ?>>
              <?php echo esc_html($st); ?>
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

      <div style="min-width:220px;">
        <label class="sp-label">Cari Judul</label>
        <input type="text" name="q" class="sp-input" value="<?php echo esc_attr($filters['keyword']); ?>" placeholder="contoh: food waste">
      </div>

      <div>
        <button class="sp-btn-primary" type="submit">Terapkan</button>
        <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('my')); ?>">Reset</a>
        <?php
          $export_params = array();
          if (!empty($filters['year'])) $export_params['f_year'] = (int)$filters['year'];
          if (!empty($filters['status'])) $export_params['f_status'] = $filters['status'];
          if (!empty($filters['sdg_number'])) $export_params['f_sdg'] = (int)$filters['sdg_number'];
          if (!empty($filters['keyword'])) $export_params['q'] = $filters['keyword'];
          $export_params['export'] = 'csv';
        ?>
        <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('my', $export_params)); ?>">Export CSV</a>
      </div>
    </form>

    <div style="width:100%;overflow-x:auto;">
      <table class="sp-table sp-datatable">
        <thead>
          <tr>
            <th>Judul</th><th>Tahun</th><th>SDG</th><th>Unit</th><th>Status</th><th>Update</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td>
                <a href="<?php echo esc_url(Url::page('detail', array('evidence_id'=>$r->id))); ?>">
                  <?php echo esc_html($r->title); ?>
                </a>
              </td>
              <td><?php echo esc_html($r->year); ?></td>
              <td><?php echo !empty($r->sdg_number) ? esc_html('SDG ' . $r->sdg_number) : '—'; ?></td>
              <td><?php echo esc_html($r->unit_code); ?></td>
              <td><span class="sp-status-badge sp-status-<?php echo esc_attr($r->status); ?>"><?php echo esc_html($r->status); ?></span></td>
              <td><?php echo esc_html($r->updated_at); ?></td>
              <td>
                <?php if (in_array($r->status, array('DRAFT','REJECTED'), true)): ?>
                  <div class="sp-action-group">
                    <a href="<?php echo esc_url(Url::page('detail', array('evidence_id'=>$r->id,'mode'=>'edit'))); ?>" 
                       class="sp-action-btn" title="Edit">
                      <span class="dashicons dashicons-edit"></span>
                    </a>

                    <form method="post" class="sp-action-form">
                      <?php wp_nonce_field('delete_evidence_' . $r->id); ?>
                      <input type="hidden" name="action" value="delete_evidence">
                      <input type="hidden" name="evidence_id" value="<?php echo (int)$r->id; ?>">
                      <button type="submit" class="sp-action-btn"
                        onclick="return confirm('Yakin hapus evidence ini?');">
                        <span class="dashicons dashicons-trash"></span>
                      </button>
                    </form>

                  </div>
                <?php else: ?>
                  —
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
