(function(){
  function parseValue(value) {
    const txt = String(value || '').trim();
    if (!txt) return { type: 'string', value: '' };

    const normalized = txt.replace(/SDG\s+/i, '').replace(/,/g, '');
    if (/^-?\d+(\.\d+)?$/.test(normalized)) {
      return { type: 'number', value: parseFloat(normalized) };
    }

    const date = Date.parse(txt);
    if (!Number.isNaN(date)) {
      return { type: 'date', value: date };
    }

    return { type: 'string', value: txt.toLowerCase() };
  }

  function sortTable(table, colIndex, direction) {
    const tbody = table.tBodies[0];
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort(function(a, b){
      const aCell = a.children[colIndex] ? a.children[colIndex].innerText : '';
      const bCell = b.children[colIndex] ? b.children[colIndex].innerText : '';

      const pa = parseValue(aCell);
      const pb = parseValue(bCell);

      let cmp = 0;
      if (pa.type === pb.type && (pa.type === 'number' || pa.type === 'date')) {
        cmp = pa.value - pb.value;
      } else {
        cmp = String(pa.value).localeCompare(String(pb.value), 'id', { numeric: true, sensitivity: 'base' });
      }

      return direction === 'asc' ? cmp : -cmp;
    });

    rows.forEach(function(r){ tbody.appendChild(r); });
  }

  function initDatatables() {
    document.querySelectorAll('table.sp-datatable').forEach(function(table){
      const headers = Array.from(table.querySelectorAll('thead th'));
      headers.forEach(function(th, idx){
        th.style.cursor = 'pointer';
        th.title = 'Klik untuk urutkan';
        th.addEventListener('click', function(){
          const current = th.getAttribute('data-sort') === 'asc' ? 'asc' : (th.getAttribute('data-sort') === 'desc' ? 'desc' : '');
          headers.forEach(function(other){ other.removeAttribute('data-sort'); });
          const next = current === 'asc' ? 'desc' : 'asc';
          th.setAttribute('data-sort', next);
          sortTable(table, idx, next);
        });
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDatatables);
  } else {
    initDatatables();
  }
})();
