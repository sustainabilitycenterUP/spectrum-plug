<?php
if (!defined('ABSPATH')) exit;

include __DIR__ . '/layout-open.php';

$year = (int)($year ?? 0);
$overview = $overview ?? array(
  'requested_total' => 0,
  'confirmed_total' => 0,
  'submitted_total' => 0,
  'percent' => 0,
);
$sdg_summary = $sdg_summary ?? array();
$unit = $unit ?? array();
$general_unit = $general_unit ?? array();
?>

<div class="sp-page-header">
  <div class="sp-page-title-block">
    <h1>Dashboard SPECTRUM</h1>
    <p>Ringkasan progres pengumpulan evidence tahun <?php echo (int)$year; ?>.</p>
  </div>
</div>

<section class="sp-card">
  <div class="sp-dashboard-cards">
    <div class="sp-box sp-dashboard-stat">
      <div class="sp-dashboard-stat-label">Jumlah data yang diminta</div>
      <div class="sp-dashboard-stat-value"><?php echo (int)$overview['requested_total']; ?></div>
    </div>
    <div class="sp-box sp-dashboard-stat">
      <div class="sp-dashboard-stat-label">Jumlah data dikonfirmasi</div>
      <div class="sp-dashboard-stat-value"><?php echo (int)$overview['confirmed_total']; ?></div>
      <div class="sp-help">Termasuk APPROVED + NO Data (per metrik mandatory unik per fungsi).</div>
    </div>
    <div class="sp-box sp-dashboard-stat">
      <div class="sp-dashboard-stat-label">Proses Pengumpulan data</div>
      <div class="sp-dashboard-stat-value"><?php echo (int)$overview['percent']; ?>%</div>
      <div class="sp-help">Jumlah data dikonfirmasi / Jumlah data yang diminta.</div>
    </div>
    <div class="sp-box sp-dashboard-stat">
      <div class="sp-dashboard-stat-label">Jumlah data belum dikonfirmasi</div>
      <div class="sp-dashboard-stat-value"><?php echo (int)$overview['submitted_total']; ?></div>
      <div class="sp-help">Mandatory yang statusnya masih SUBMITTED.</div>
    </div>
  </div>

  <div class="sp-dashboard-two-col" style="margin-top:14px;">
    <div class="sp-box">
      <h3 style="margin:0 0 6px 0;">Progress per Unit</h3>
      <div class="sp-help">Sumbu Y: Fungsi, sumbu X: persentase konfirmasi mandatory.</div>
      <label style="font-size:12px;color:#334155;display:flex;align-items:center;gap:6px;">
        Filter Fungsi
        <select id="sp-unit-filter" class="sp-select" style="width:auto;min-width:180px;padding:4px 8px;">
          <option value="ALL">Semua</option>
        </select>
      </label>
      <div class="sp-chart-wrap" style="margin-top:10px;">
        <canvas id="sp-unit-progress-chart"></canvas>
      </div>
    </div>

    <div class="sp-box">
      <h3 style="margin:0 0 6px 0;">Grafik Kontribusi Unit</h3>
      <div class="sp-help">Sumbu Y: Fungsi, sumbu X: jumlah evidence GENERAL yang APPROVED.</div>
      <div class="sp-chart-wrap" style="margin-top:10px;">
        <canvas id="sp-unit-general-chart"></canvas>
      </div>
    </div>
  </div>

  <div class="sp-box" style="margin-top:14px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
      <h3 style="margin:0;">SDG Evidence Status</h3>
      <label style="font-size:12px;color:#334155;display:flex;align-items:center;gap:6px;">
        Filter Status
        <select id="sp-sdg-status-filter" class="sp-select" style="width:auto;min-width:140px;padding:4px 8px;">
          <option value="ALL">Semua</option>
          <option value="APPROVED">Approved</option>
          <option value="SUBMITTED">Submitted</option>
          <option value="REJECTED">Rejected</option>
        </select>
      </label>
    </div>
    <div class="sp-chart-wrap" style="margin-top:12px;">
      <canvas id="sp-sdg-evidence-chart"></canvas>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  if (typeof Chart === 'undefined') return;

  const rows = <?php echo wp_json_encode(array_values((array)$sdg_summary)); ?>;
  const unitRows = <?php echo wp_json_encode(array_values((array)$unit)); ?>;
  const generalRows = <?php echo wp_json_encode(array_values((array)$general_unit)); ?>;

  const commonTooltip = {
    backgroundColor: '#dcfce7',
    titleColor: '#166534',
    bodyColor: '#14532d',
    borderColor: '#86efac',
    borderWidth: 1,
    titleFont: { size: 11, weight: '600' },
    bodyFont: { size: 11 }
  };

  const sdgLabels = rows.map(r => 'SDG ' + (r.sdg_number || 0));
  const approved = rows.map(r => Number(r.approved || 0));
  const submitted = rows.map(r => Number(r.submitted || 0));
  const rejected = rows.map(r => Number(r.rejected || 0));

  const sdgColorMap = {
    1:'#e5243b', 2:'#dda63a', 3:'#4c9f38', 4:'#c5192d', 5:'#ff3a21', 6:'#26bde2',
    7:'#fcc30b', 8:'#a21942', 9:'#fd6925', 10:'#dd1367', 11:'#fd9d24', 12:'#bf8b2e',
    13:'#3f7e44', 14:'#0a97d9', 15:'#56c02b', 16:'#00689d', 17:'#19486a'
  };

  const baseColors = rows.map(r => sdgColorMap[Number(r.sdg_number)] || '#64748b');
  const hexToRgba = (hex, alpha) => {
    const c = hex.replace('#','');
    const r = parseInt(c.substring(0,2), 16);
    const g = parseInt(c.substring(2,4), 16);
    const b = parseInt(c.substring(4,6), 16);
    return `rgba(${r},${g},${b},${alpha})`;
  };

  const sdgEl = document.getElementById('sp-sdg-evidence-chart');
  if (sdgEl) {
    const sdgChart = new Chart(sdgEl, {
      type: 'bar',
      data: {
        labels: sdgLabels,
        datasets: [
          { key:'APPROVED', label:'Approved', data: approved, backgroundColor: baseColors.map(c => hexToRgba(c, 1)) },
          { key:'SUBMITTED', label:'Submitted', data: submitted, backgroundColor: baseColors.map(c => hexToRgba(c, 0.5)) },
          { key:'REJECTED', label:'Rejected', data: rejected, backgroundColor: baseColors.map(c => hexToRgba(c, 0.2)) }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' },
          tooltip: {
            ...commonTooltip,
            callbacks: { label: function(ctx){ return `${ctx.dataset.label}: ${ctx.raw}`; } }
          }
        },
        scales: {
          x: { ticks: { maxRotation: 45, minRotation: 45 }, grid: {display: false} },
          y: { beginAtZero: true, title: { display: true, text: 'Jumlah Evidence' }, grid: {display: false} }
        }
      }
    });

    const statusFilter = document.getElementById('sp-sdg-status-filter');
    if (statusFilter) {
      statusFilter.addEventListener('change', function(){
        const selected = this.value;
        sdgChart.data.datasets.forEach(function(ds){
          ds.hidden = (selected !== 'ALL' && ds.key !== selected);
        });
        sdgChart.update();
      });
    }
  }

  const unitCanvas = document.getElementById('sp-unit-progress-chart');
  const unitFilter = document.getElementById('sp-unit-filter');
  let unitChart = null;

  function getFilteredUnits(){
    if (!unitFilter || unitFilter.value === 'ALL') return unitRows;
    return unitRows.filter(u => String(u.unit_code || '') === String(unitFilter.value));
  }

  function renderUnitChart(){
    if (!unitCanvas) return;
    const rows = getFilteredUnits();
    const labels = rows.map(u => u.unit_code || '—');
    const approvedPct = rows.map(u => Number(u.approved_percent || 0));
    const noDataPct = rows.map(u => Number(u.no_data_percent || 0));

    if (unitChart) unitChart.destroy();

    unitChart = new Chart(unitCanvas, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'Approved Mandatory',
            data: approvedPct,
            backgroundColor: '#2563eb',
            borderColor: '#1d4ed8',
            borderWidth: 1
          },
          {
            label: 'No Data',
            data: noDataPct,
            backgroundColor: '#f97316',
            borderColor: '#ea580c',
            borderWidth: 1
          }
        ]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true, position: 'top' },
          tooltip: {
            ...commonTooltip,
            callbacks: {
              label: function(ctx){
                const idx = ctx.dataIndex;
                const item = rows[idx] || {};
                if (ctx.dataset.label === 'Approved Mandatory') {
                  return `Approved: ${Number(item.approved_percent || 0).toFixed(1)}% (${item.approved_total || 0}/${item.requested_total || 0})`;
                }
                return `No Data: ${Number(item.no_data_percent || 0).toFixed(1)}% (${item.no_data_total || 0}/${item.requested_total || 0})`;
              }
            }
          }
        },
        scales: {
          x: { beginAtZero: true, max: 100, title: { display: true, text: 'Persentase (%)' }, stacked: true, grid: {display: false} },
          y: { ticks: { autoSkip: false }, stacked: true, grid: {display: false} }
        }
      }
    });
  }

  if (unitFilter) {
    const uniqUnits = Array.from(new Set(unitRows.map(u => String(u.unit_code || '')).filter(Boolean)));
    uniqUnits.forEach(code => {
      const opt = document.createElement('option');
      opt.value = code;
      opt.textContent = code;
      unitFilter.appendChild(opt);
    });
    unitFilter.addEventListener('change', renderUnitChart);
  }

  renderUnitChart();

  const generalCanvas = document.getElementById('sp-unit-general-chart');
  if (generalCanvas) {
    const labels = generalRows.map(u => u.unit_code || '—');
    const totals = generalRows.map(u => Number(u.general_approved_total || 0));

    new Chart(generalCanvas, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'General Approved',
          data: totals,
          backgroundColor: '#16a34a',
          borderColor: '#15803d',
          borderWidth: 1
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            ...commonTooltip,
            callbacks: { label: function(ctx){ return `Jumlah evidence: ${ctx.raw}`; } }
          }
        },
        scales: {
          x: { beginAtZero: true, title: { display: true, text: 'Jumlah Evidence' }, grid: {display: false} },
          y: { ticks: { autoSkip: false }, grid: {display: false} }
        }
      }
    });
  }
})();
</script>

<?php include __DIR__ . '/layout-close.php'; ?>
