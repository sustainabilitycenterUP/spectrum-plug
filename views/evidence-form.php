<?php
use Spectrum\Evidence\Core\Url;

if (!defined('ABSPATH')) exit;

$active = 'new';
include __DIR__ . '/layout-open.php';
?>

<div class="sp-page-header">
  <div>
    <h1>Buat Evidence Baru</h1>
  </div>
  <a class="sp-btn-secondary" href="<?php echo esc_url(Url::page('my')); ?>">← Kembali</a>
</div>

<section class="sp-card">
  <?php if (!empty($notice) && !empty($notice['messages'])): ?>
    <div class="sp-alert <?php echo ($notice['type']==='success') ? 'sp-alert-success' : 'sp-alert-error'; ?>">
      <ul>
        <?php foreach ((array)$notice['messages'] as $m): ?>
          <li><?php echo esc_html($m); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="sp-form-wrapper" id="sp-form">
    <?php wp_nonce_field('spectrum_save_evidence', 'spectrum_nonce'); ?>
    <input type="hidden" name="year" value="<?php echo (int)$year; ?>">
    <h4>Tahun pelaporan 2027 <br> Tahun akademik 2024/2025</h4>

    <div class="sp-form-row">
      <label class="sp-label">Kategori *</label>
      <div style="display:flex;gap:18px;align-items:center;">
        <label><input type="radio" name="metric_mode" value="MANDATORY" required checked> Mandatory</label>
        <label><input type="radio" name="metric_mode" value="GENERAL" required> General</label>
      </div>
    </div>

    <div class="sp-form-row" id="sp-sdg-row" style="display:none;">
      <label class="sp-label">Pilih SDG (untuk General) *</label>
      <select name="general_sdg" id="general_sdg" class="sp-select">
        <option value="">-- Pilih SDG --</option>
        <?php for ($i=1; $i<=17; $i++): ?>
          <option value="<?php echo (int)$i; ?>">SDG <?php echo (int)$i; ?></option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="sp-form-row">
      <label class="sp-label">Metrik *</label>
      <select name="metric_id" id="metric_select" class="sp-select" required>
        <option value="">-- Pilih Metrik --</option>
      </select>
    </div>

    <div id="sp-metric-info" class="sp-metric-box" style="display:none;margin-bottom:12px;">
      <div class="sp-metric-title">Metric Question</div>
      <div id="sp-metric-question">-</div>
      <div class="sp-metric-title" style="margin-top:8px;">Deskripsi Data yang Dibutuhkan</div>
      <div id="sp-metric-desc">-</div>
      <div class="sp-metric-title" style="margin-top:8px;">Catatan</div>
      <div id="sp-metric-note">-</div>
    </div>

    <div class="sp-form-row" id="sp-no-data-wrap" style="display:none;">
      <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="is_no_data" id="is_no_data" value="1"> Not Available
      </label>
      <div class="sp-help" style="margin-left:24px;">centang jika fungsi Anda tidak memiliki data yang diminta</div>
    </div>

    <div class="sp-form-row" id="sp-number-wrap" style="display:none;">
      <label class="sp-label">Input Numeric Value *</label>
      <input type="number" step="any" name="metric_number_value" id="metric_number_value" class="sp-input">
      <div class="sp-help">Field ini wajib untuk metrik bertipe number/numeric.</div>
    </div>

    <div class="sp-form-row">
      <label class="sp-label">Judul Evidence *</label>
      <input type="text" name="title" id="title" class="sp-input">
    </div>

    <div class="sp-form-row">
      <label class="sp-label">Sumber Evidence *</label>
      <div style="display:flex;gap:16px;align-items:center;">
        <label><input type="radio" name="source_type" value="link"> Link</label>
        <label><input type="radio" name="source_type" value="file"> File</label>
      </div>
    </div>

    <div class="sp-form-row sp-source-link" style="display:none;">
      <label class="sp-label">Link URL *</label>
      <input type="url" name="link_url" id="link" class="sp-input" placeholder="https://...">
    </div>

    <div class="sp-form-row sp-source-file" style="display:none;">
      <label class="sp-label">Upload File *</label>
      <input type="file" name="evidence_file" id="file" class="sp-input">
    </div>

    <div class="sp-form-row">
      <label class="sp-label">Ringkasan Evidence *</label>
      <textarea name="summary" id="summary" class="sp-textarea"></textarea>
    </div>

    <div class="sp-form-actions">
      <button type="submit" name="spectrum_action" value="draft" class="sp-btn-secondary">Simpan Draft</button>
      <button type="submit" name="spectrum_action" value="submit" class="sp-btn-primary">Submit</button>
    </div>
  </form>
</section>

<script>
(function(){
  const mandatory = <?php echo wp_json_encode(array_values((array)$mandatory_metrics)); ?>;
  const general = <?php echo wp_json_encode(array_values((array)$general_metrics)); ?>;
  const noDataIds = new Set(<?php echo wp_json_encode(array_values((array)$no_data_ids)); ?>.map(Number));

  const modeEls = document.querySelectorAll('input[name="metric_mode"]');
  const sdgRow = document.getElementById('sp-sdg-row');
  const sdgSelect = document.getElementById('general_sdg');
  const metricSelect = document.getElementById('metric_select');
  const noWrap = document.getElementById('sp-no-data-wrap');
  const noData = document.getElementById('is_no_data');
  const numberWrap = document.getElementById('sp-number-wrap');
  const numberInput = document.getElementById('metric_number_value');
  const metricInfo = document.getElementById('sp-metric-info');
  const metricQuestion = document.getElementById('sp-metric-question');
  const metricDesc = document.getElementById('sp-metric-desc');
  const metricNote = document.getElementById('sp-metric-note');
  const form = document.getElementById('sp-form');

  const title = document.getElementById('title');
  const summary = document.getElementById('summary');
  const link = document.getElementById('link');
  const file = document.getElementById('file');
  const srcLinkWrap = document.querySelector('.sp-source-link');
  const srcFileWrap = document.querySelector('.sp-source-file');
  const sourceRadios = document.querySelectorAll('input[name="source_type"]');

  function getMode(){
    const checked = document.querySelector('input[name="metric_mode"]:checked');
    return checked ? checked.value : 'MANDATORY';
  }

  function rebuildMetric() {
    const mode = getMode();
    metricSelect.innerHTML = '<option value="">-- Pilih Metrik --</option>';
    noWrap.style.display = (mode === 'MANDATORY') ? '' : 'none';
    if (mode !== 'MANDATORY') noData.checked = false;

    const items = mode === 'MANDATORY'
      ? mandatory
      : general.filter(m => String(m.sdg_number) === String(sdgSelect.value || ''));

    items.forEach(item => {
      const id = Number(item.metric_id || item.id);
      const opt = document.createElement('option');
      opt.value = id;
      opt.textContent = item.label || `${item.metric_code} – ${item.metric_title}${noDataIds.has(id) ? ' [NO]' : ''}`;
      opt.dataset.question = item.metric_question || '';
      opt.dataset.desc = item.metric_desc || '';
      opt.dataset.note = item.metric_note || '';
      opt.dataset.type = (item.metric_type || '').toLowerCase();
      metricSelect.appendChild(opt);
    });

    onMetricChange();
  }

  function updateSourceMode() {
    const src = document.querySelector('input[name="source_type"]:checked');
    if (!src || src.value === 'link') {
      srcLinkWrap.style.display = '';
      srcFileWrap.style.display = 'none';
    } else {
      srcLinkWrap.style.display = 'none';
      srcFileWrap.style.display = '';
    }
  }

  function syncRequired() {
    const no = !!noData.checked;
    const selectedOpt = metricSelect.options[metricSelect.selectedIndex];
    const type = selectedOpt ? (selectedOpt.dataset.type || '') : '';
    const isNumber = (type === 'numeric' || type === 'number');
    title.required = !no;
    summary.required = !no;
    numberWrap.style.display = isNumber ? '' : 'none';
    numberInput.required = !no && isNumber;
    if (!isNumber) numberInput.value = '';
    const src = document.querySelector('input[name="source_type"]:checked');
    if (!src || src.value === 'link') {
      link.required = !no;
      file.required = false;
    } else {
      file.required = !no;
      link.required = false;
    }
  }

  function onMetricChange() {
    const selectedOpt = metricSelect.options[metricSelect.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) {
      metricInfo.style.display = 'none';
      syncRequired();
      return;
    }
    metricQuestion.textContent = selectedOpt.dataset.question || '-';
    metricDesc.textContent = selectedOpt.dataset.desc || '-';
    metricNote.textContent = selectedOpt.dataset.note || '-';
    metricInfo.style.display = '';
    syncRequired();
  }

  modeEls.forEach(el => el.addEventListener('change', function(){
    sdgRow.style.display = (getMode() === 'GENERAL') ? '' : 'none';
    rebuildMetric();
  }));

  sdgSelect.addEventListener('change', rebuildMetric);
  metricSelect.addEventListener('change', onMetricChange);
  noData.addEventListener('change', syncRequired);
  sourceRadios.forEach(r => r.addEventListener('change', function(){ updateSourceMode(); syncRequired(); }));

  form.addEventListener('submit', function(e){
    const mode = getMode();
    if (mode === 'GENERAL' && !sdgSelect.value) {
      alert('Pilih SDG dulu untuk kategori General.');
      e.preventDefault();
      return;
    }
  });

  sdgRow.style.display = 'none';
  rebuildMetric();
  updateSourceMode();
  syncRequired();
})();
</script>

<?php include __DIR__ . '/layout-close.php'; ?>
