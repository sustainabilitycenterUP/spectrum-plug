# Tutorial Plotting Metric ke Fungsi (Mandatory & Recommended)

Dokumen ini untuk mengisi data nyata (bukan dummy) agar:
- metrik aktif per tahun tersimpan di `wp_spectrum_year_metric`,
- metrik diplot ke fungsi/unit di `wp_spectrum_function_metric_assignment`,
- form evidence menampilkan kategori `MANDATORY`, `RECOMMENDED`, dan `GENERAL` sesuai setup.

---

## 1) Struktur tabel yang dipakai

1. **Master metric**
   - `wp_spectrum_metric`
   - isi: `sdg_number`, `metric_code`, `metric_type`, `metric_title`, dst.

2. **Aktif per tahun**
   - `wp_spectrum_year_metric`
   - relasi: `metric_id`, `year`, `is_active`.

3. **Plot metric ke fungsi/unit**
   - `wp_spectrum_function_metric_assignment`
   - relasi: `unit_code`, `metric_id`, `year`, `category` (`MANDATORY` / `RECOMMENDED`).

---

## 2) Langkah isi data

### Step A — insert/update master metric

```sql
INSERT INTO wp_spectrum_metric
  (sdg_number, metric_code, metric_type, metric_title, metric_question, metric_note, is_active_default, created_at, updated_at)
VALUES
  (3, '3.2.1', 'initiatives', 'Student health programme', 'Does your university provide ...?', 'Year: 2026', 1, NOW(), NOW()),
  (4, '4.3.2', 'numeric', 'Graduate employability', NULL, 'Year: 2026', 1, NOW(), NOW());
```

> Tips: gunakan `metric_code` konsisten dengan dokumen THE untuk memudahkan tracing.

### Step B — aktifkan metric untuk tahun pelaporan

```sql
-- contoh aktifkan metric id 101 & 102 untuk tahun 2026
INSERT INTO wp_spectrum_year_metric (year, metric_id, is_active, created_at, updated_at)
VALUES
  (2026, 101, 1, NOW(), NOW()),
  (2026, 102, 1, NOW(), NOW());
```

Jika data sudah ada dan hanya update:

```sql
UPDATE wp_spectrum_year_metric
SET is_active = 1, updated_at = NOW()
WHERE year = 2026 AND metric_id IN (101,102);
```

### Step C — plot metric ke fungsi (mandatory/recommended)

```sql
-- dirdik: 1 mandatory + 1 recommended
INSERT INTO wp_spectrum_function_metric_assignment
  (unit_code, metric_id, year, category, created_at, updated_at)
VALUES
  ('dirdik', 101, 2026, 'MANDATORY', NOW(), NOW()),
  ('dirdik', 102, 2026, 'RECOMMENDED', NOW(), NOW());
```

---

## 3) Query validasi cepat

### Cek metrik aktif per tahun
```sql
SELECT ym.year, m.id, m.metric_code, m.metric_title, m.sdg_number
FROM wp_spectrum_year_metric ym
JOIN wp_spectrum_metric m ON m.id = ym.metric_id
WHERE ym.year = 2026 AND ym.is_active = 1
ORDER BY m.sdg_number, m.metric_code;
```

### Cek plotting per unit
```sql
SELECT f.unit_code, f.year, f.category, m.metric_code, m.metric_title
FROM wp_spectrum_function_metric_assignment f
JOIN wp_spectrum_metric m ON m.id = f.metric_id
WHERE f.year = 2026
ORDER BY f.unit_code, f.category, m.sdg_number, m.metric_code;
```

### Cek metrik yang belum diplot
```sql
SELECT m.id, m.metric_code, m.metric_title
FROM wp_spectrum_year_metric ym
JOIN wp_spectrum_metric m ON m.id = ym.metric_id
LEFT JOIN wp_spectrum_function_metric_assignment f
  ON f.metric_id = m.id AND f.year = ym.year
WHERE ym.year = 2026 AND ym.is_active = 1
  AND f.id IS NULL
ORDER BY m.sdg_number, m.metric_code;
```

---

## 4) Dampak di aplikasi

Setelah data benar:

1. Halaman **Buat Evidence Baru**:
   - kategori `MANDATORY` menampilkan metric assignment mandatory,
   - kategori `RECOMMENDED` menampilkan assignment recommended,
   - kategori `GENERAL` menampilkan metric aktif yang tidak diassign ke unit tsb.

2. Dashboard **Progress per Unit**:
   - denominator (`mandatory_total`) dihitung dari assignment mandatory per unit.

---

## 5) Checklist operasional

- [ ] Semua metric tahun aktif sudah terisi di `wp_spectrum_year_metric`.
- [ ] Tiap unit minimal punya mapping mandatory sesuai kebijakan.
- [ ] Tidak ada duplicate assignment (`unit_code`,`metric_id`,`year`,`category`).
- [ ] Sampling 2–3 unit: form evidence tampil sesuai assignment.
- [ ] Dashboard progress per unit masuk akal (0–100%).

