<?php
use Spectrum\Evidence\Core\Url;

if (!defined('ABSPATH')) exit;

$active = 'my';
include __DIR__ . '/layout-open.php';
?>

<div class="sp-page-header">
  <div class="sp-page-title-block">
    <h1>Detail Evidence</h1>
    <p>• Status: <span class="sp-pill"><?php echo esc_html($ev->status); ?></span></p>
  </div>
  <!-- <a class="sp-btn-secondary" href="<?php #echo esc_url(Url::page('my')); ?>">← Kembali</a> -->
</div>

<section class="sp-card">
  <?php if (!empty($notice) && !empty($notice['messages'])): ?>
    <div class="sp-alert <?php echo ($notice['type']==='success')?'sp-alert-success':'sp-alert-error'; ?>">
      <ul>
        <?php foreach ((array)$notice['messages'] as $m): ?><li><?php echo esc_html($m); ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="sp-detail-wrapper">

    <div class="sp-detail-main">

      <div class="sp-box">
        <h3 style="margin-top:0;">Informasi</h3>
        <div class="sp-kv">
          <div class="sp-k">Tahun</div><div><?php echo esc_html($ev->year); ?></div>
          <div class="sp-k">Unit</div><div><?php echo esc_html($ev->unit_code); ?></div>
          <div class="sp-k">Status</div><div><span class="sp-pill"><?php echo esc_html($ev->status); ?></span></div>
          <div class="sp-k">Last Update</div><div><?php echo esc_html($ev->updated_at); ?></div>
          <div class="sp-k">SDG</div><div><?php echo !empty($selected_metric->sdg_number) ? esc_html('SDG ' . $selected_metric->sdg_number) : '—'; ?></div>
          <div class="sp-k">Metric</div>
          <div>
            <?php
              echo !empty($selected_metric->metric_code)
                ? esc_html($selected_metric->metric_code . ' – ' . $selected_metric->metric_title)
                : '—';
            ?>
          </div>
          <div class="sp-k">Metric Question</div>
          <div><?php echo !empty($selected_metric->metric_question) ? esc_html($selected_metric->metric_question) : '—'; ?></div>
          <div class="sp-k">Link</div>
          <div>
            <?php echo $ev->link_url ? '<a target="_blank" href="'.esc_url($ev->link_url).'">Buka Link</a>' : '—'; ?>
          </div>
        </div>
      </div>

      <div class="sp-box" style="margin-top:16px;">
        <?php if ($editable): ?>
          <?php 
            $ev_type = !empty($ev->attachment_id) ? 'file' : (!empty($ev->link_url) ? 'link' : 'link');
          ?>
            <h3>Edit Evidence (DRAFT)</h3>

            <form method="post" enctype="multipart/form-data">
              <!--hidden input untuk “mengunci” type, biar server tau-->
              <input type="hidden" name="source_type" value="<?php echo esc_attr($ev_type); ?>"> 

              <?php wp_nonce_field('spectrum_save_evidence','spectrum_nonce'); ?>
              <input type="hidden" name="evidence_id" value="<?php echo (int)$ev->id; ?>">

              <div class="sp-form-row">
                <label class="sp-label">Metrik THE *</label>
                <select name="metric_id" class="sp-select" required>
                  <option value="">-- Pilih Metrik --</option>
                  <?php foreach ((array)$metric_options as $group_label => $items): ?>
                    <optgroup label="<?php echo esc_attr($group_label); ?>">
                      <?php foreach ((array)$items as $m): ?>
                        <option value="<?php echo esc_attr($m->id); ?>" <?php selected((int)$selected_metric_id, (int)$m->id); ?>>
                          <?php echo esc_html('SDG '.$m->sdg_number.' – '.$m->metric_code.' – '.$m->metric_title); ?>
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="sp-form-row">
                <label class="sp-label">Judul *</label>
                <input class="sp-input" type="text" name="title" value="<?php echo esc_attr($ev->title); ?>" required>
              </div>

              <div class="sp-form-row">
                <label class="sp-label">Ringkasan *</label>
                <textarea class="sp-textarea" name="summary" required><?php echo esc_textarea($ev->summary); ?></textarea>
              </div>

              <?php if ($ev_type === 'link'): ?>

                <div class="sp-form-row">
                  <label class="sp-label">Link URL *</label>
                  <input type="url" name="link_url" class="sp-input" value="<?php echo esc_attr($ev->link_url); ?>" required>
                  <div class="sp-help">Catatan: pastikan link accessible dan bisa diakses sampai tahun 2025.</div>
                </div>

              <?php else: ?>

                <div class="sp-form-row">
                  <label class="sp-label">Upload File <?php echo !empty($file_url) ? '(opsional, untuk ganti file)' : '*'; ?></label>
                  <input type="file" name="evidence_file" class="sp-input" <?php echo empty($file_url) ? 'required' : ''; ?>>
                  <?php if (!empty($file_url)): ?>
                    <div class="sp-help">File saat ini: <a target="_blank" href="<?php echo esc_url($file_url); ?>">Download</a></div>
                  <?php endif; ?>
                </div>

              <?php endif; ?>

              <div class="sp-form-row">
                <label class="sp-label">Tahun *</label>
                <select name="year" class="sp-select" required>
                  <?php foreach ((array)$years as $y): ?>
                    <option value="<?php echo esc_attr($y); ?>" <?php selected((int)$ev->year, (int)$y); ?>>
                      <?php echo esc_html($y); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="sp-form-actions">
                <button class="sp-btn-secondary" type="submit" name="spectrum_action" value="update_draft">Update Draft</button>
                <button class="sp-btn-primary" type="submit" name="spectrum_action" value="update_submit">Update & Submit</button>
              </div>
            </form>

          <?php else: ?>

            <h2 style="margin-top:0;"><?php echo esc_html($ev->title); ?></h2>
            <div style="margin-top:12px;white-space:pre-wrap;"><?php echo esc_html($ev->summary ?: '—'); ?></div>

            <?php if (!empty($file_url)): ?>
              <div style="margin-top:16px;">
                <strong>Lampiran:</strong><br>
                <a target="_blank" href="<?php echo esc_url($file_url); ?>">Download File</a>

                <?php if (stripos($file_url, '.pdf') !== false): ?>
                  <iframe src="<?php echo esc_url($file_url); ?>"
                    style="width:100%;height:500px;margin-top:10px;border:1px solid #ddd;"></iframe>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if ($ev->status === 'DRAFT'): ?>
              <div style="margin-top:14px;">
                <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('detail', array('evidence_id'=>$ev->id,'mode'=>'edit'))); ?>">
                  Edit Draft
                </a>
              </div>
            <?php endif; ?>

          <?php endif; ?>
      </div>

    </div>

    <div class="sp-detail-right">
      <div class="sp-box">
          <h3 style="margin-top:0;">Riwayat Evidence</h3>
          <?php if (empty($logs)): ?>
            <div style="color:#6b7280;">Belum ada log.</div>
          <?php else: ?>
            <div class="sp-timeline">
            <?php foreach ($logs as $lg):
              $actor = $lg->actor_id ? get_user_by('id', (int)$lg->actor_id) : null;
              $to = strtoupper((string)$lg->to_status);
              $actor_name = $actor ? $actor->display_name : 'System';
              $initial = strtoupper(substr($actor_name, 0, 1));
              $actor_role = ($actor && user_can($actor->ID, 'manage_options')) ? 'Admin SC' : (($to === 'APPROVED' || $to === 'REJECTED') ? 'Reviewer' : 'Kontributor');
              $action_text = ($to === 'APPROVED')
                ? 'Evidence disetujui oleh'
                : (($to === 'REJECTED') ? 'Evidence ditolak oleh' : 'Perubahan diajukan oleh');
            ?>
              <div class="sp-timeline-item sp-timeline-<?php echo esc_attr(strtolower($to)); ?>">
                <div class="sp-timeline-dot"></div>
                <div class="sp-timeline-content">
                  <div class="sp-timeline-date">📅 <?php echo esc_html(date_i18n('d M Y H:i', strtotime($lg->created_at))); ?></div>
                  <div class="sp-timeline-title">
                    📝 <?php echo esc_html($action_text); ?>, <span class="sp-timeline-role"><?php echo esc_html($actor_role); ?></span>
                  </div>
                  <div class="sp-timeline-profile">
                    <div class="sp-timeline-avatar"><?php echo esc_html($initial); ?></div>
                    <div class="sp-timeline-profile-text">
                      <div class="sp-timeline-name"><?php echo esc_html($actor_name); ?></div>
                      <div class="sp-timeline-flow"><?php echo esc_html(($lg->from_status ?: '—') . ' → ' . $lg->to_status); ?></div>
                      <span class="sp-status-badge sp-status-<?php echo esc_attr($to); ?>"><?php echo esc_html($to); ?></span>
                    </div>
                  </div>
                <?php if (!empty($lg->notes)): ?>
                    <div class="sp-timeline-note">💬 <?php echo esc_html($lg->notes); ?></div>
                <?php endif; ?>
                <?php if (!empty($file_url) && $to === 'REJECTED'): ?>
                    <div><a class="sp-timeline-attachment" target="_blank" href="<?php echo esc_url($file_url); ?>">📄 Download Attachment</a></div>
                <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</section>

<?php include __DIR__ . '/layout-close.php'; ?>

