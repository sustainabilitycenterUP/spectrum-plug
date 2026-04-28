<?php
use Spectrum\Evidence\Core\Url;

if (!defined('ABSPATH')) exit;

$active = 'review';
include __DIR__ . '/layout-open.php';
?>

<div class="sp-page-header">
  <div class="sp-page-title-block">
    <h1>Evidence untuk Direview</h1>
    <p>Daftar evidence dengan status <strong>SUBMITTED</strong>.</p>
  </div>
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
    <div style="padding:14px;color:#6b7280;">Belum ada evidence untuk direview.</div>
  <?php else: ?>
    <div style="width:100%;overflow-x:auto;">
      <table class="sp-table sp-datatable">
        <thead>
          <tr><th>SDG</th><th>Nomor Metrik</th><th>Judul Metrik</th><th>Unit</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?php echo !empty($r->sdg_number) ? esc_html('SDG ' . $r->sdg_number) : '—'; ?></td>
              <td><?php echo !empty($r->metric_code) ? esc_html($r->metric_code) : '—'; ?></td>
              <td><?php echo !empty($r->metric_title) ? esc_html($r->metric_title) : '—'; ?></td>
              <td><?php echo esc_html($r->unit_code); ?></td>
              <td>
                <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('detail', array('evidence_id'=>$r->id))); ?>">Detail</a>

                <form method="post" style="display:inline;">
                  <?php wp_nonce_field('review_action_' . $r->id); ?>
                  <input type="hidden" name="review_action" value="approve">
                  <input type="hidden" name="evidence_id" value="<?php echo (int)$r->id; ?>">
                  <select name="review_score" required
                    style="min-width:120px;vertical-align:middle;border-radius:8px;border:1px solid #ccc;padding:6px;font-size:12px;">
                    <option value="">Score</option>
                    <option value="1">★☆☆☆☆ (1)</option>
                    <option value="2">★★☆☆☆ (2)</option>
                    <option value="3">★★★☆☆ (3)</option>
                    <option value="4">★★★★☆ (4)</option>
                    <option value="5">★★★★★ (5)</option>
                  </select>
                  <button class="sp-btn-primary" style="margin-left:6px;">Approve</button>
                </form>

                <form method="post" style="display:inline;">
                  <?php wp_nonce_field('review_action_' . $r->id); ?>
                  <input type="hidden" name="review_action" value="reject">
                  <input type="hidden" name="evidence_id" value="<?php echo (int)$r->id; ?>">
                  <textarea name="review_notes" placeholder="Alasan reject (wajib)"
                    required
                    style="width:220px;vertical-align:middle;border-radius:8px;border:1px solid #ccc;padding:6px;font-size:12px;"></textarea>
                  <button class="sp-btn-secondary" style="margin-left:6px;">Reject</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/layout-close.php'; ?>
