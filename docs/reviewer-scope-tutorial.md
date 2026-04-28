# Tutorial Menambahkan Reviewer Scope (untuk implementasi berikutnya)

Dokumen ini menjelaskan cara menambahkan pembatasan evidence yang bisa direview per reviewer (berdasarkan SDG, metric, dan/atau unit).

> Status saat ini: fitur reviewer scope **belum diaktifkan** di flow review.

---

## 1) Konsep scope yang dipakai

Tabel yang sudah tersedia:

- `wp_spectrum_reviewer_scope`
  - `reviewer_id`
  - `sdg_number` (opsional)
  - `metric_id` (opsional)
  - `unit_code` (opsional)

Satu baris dapat dianggap sebagai satu aturan akses.

Contoh:
- Reviewer A boleh SDG 1,2,3 → insert 3 baris (`sdg_number=1/2/3`).
- Reviewer B boleh unit `dirdik` saja → insert 1 baris (`unit_code='dirdik'`).
- Reviewer C boleh metric tertentu → insert baris `metric_id`.

---

## 2) Query SQL untuk isi data scope

```sql
-- reviewer_id = 10 boleh review SDG 1,2,3
INSERT INTO wp_spectrum_reviewer_scope (reviewer_id, sdg_number, created_at)
VALUES (10,1,NOW()), (10,2,NOW()), (10,3,NOW());

-- reviewer_id = 11 boleh review unit dirdik
INSERT INTO wp_spectrum_reviewer_scope (reviewer_id, unit_code, created_at)
VALUES (11,'dirdik',NOW());
```

---

## 3) Tambahkan repository khusus scope

Saran file: `includes/Repositories/ReviewerScopeRepository.php`

Method minimal:
- `listByReviewer($reviewer_id)`
- `hasAnyScope($reviewer_id)`
- `listAllowedEvidenceIds($reviewer_id)` (opsional)

Logika matching bisa dengan join:
- scope.sdg_number ↔ metric.sdg_number
- scope.metric_id ↔ evidence_metric.metric_id
- scope.unit_code ↔ evidence.unit_code

---

## 4) Ubah query review queue

Target ubah: `EvidenceRepository::listForReview()`

Saat ini: ambil semua `status='SUBMITTED'`.

Nanti:
- jika reviewer tidak punya scope → bisa pilih fallback:
  - **A. deny all** (lebih aman), atau
  - **B. allow all** (sementara/dev mode).
- jika punya scope → tambahkan filter WHERE berbasis EXISTS ke tabel scope.

---

## 5) Guard di level service

Target ubah: `ReviewService::applyDecision()`

Tambahkan validasi:
1. reviewer punya akses ke evidence ini?
2. kalau tidak, return `WP_Error('forbidden_scope', ...)`.

Ini penting supaya walau user memanipulasi URL/form, evidence di luar scope tetap tidak bisa diproses.

---

## 6) UI Admin (opsional tahap berikut)

Untuk memudahkan operasional:
- buat halaman pengaturan reviewer scope (hanya admin),
- pilih reviewer + checklist SDG/metric/unit,
- simpan ke `wp_spectrum_reviewer_scope`.

---

## 7) Checklist testing

1. Reviewer A hanya lihat evidence sesuai scope.
2. Reviewer A tidak bisa approve/reject evidence di luar scope (test manual POST juga).
3. Reviewer tanpa scope mengikuti kebijakan fallback yang dipilih.
4. Perubahan scope langsung berdampak ke queue.

