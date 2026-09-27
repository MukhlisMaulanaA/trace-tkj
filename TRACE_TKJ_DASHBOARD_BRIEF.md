# Dashboard Brief — Trace TKJ

## 1. Tujuan

Dashboard harus menjadi **executive/operational monitoring dashboard**, bukan sekadar halaman kumpulan angka.

Tujuan:
1. Memberikan gambaran kondisi project secara cepat.
2. Menunjukkan posisi Purchase Order dan nilai finansial.
3. Membantu user menemukan project/PO yang membutuhkan perhatian.
4. Menampilkan profit dan kondisi budget secara konsisten dengan Project Summary.
5. Menyediakan drill-down menuju data detail.
6. Mengikuti scope user:
   - **Pusat** dapat melihat seluruh data.
   - **Distrik 8** hanya melihat project dengan `project_source = distrik_8`.
7. Tidak mengubah business logic existing hanya untuk kebutuhan dashboard.

---

## 2. Context Aplikasi

Stack:
- Laravel 13
- Filament v5.7.6
- MySQL

Model utama:
- `User`
- `Project`
- `ProjectProgress`
- `PurchaseOrder`
- `PurchaseOrderItem`
- `PurchaseOrderProgress`
- `ProjectSummary`
- `ProjectExpenditure`

Access control:
```text
User.project_scope
├── pusat
└── distrik_8
```

Project:
```text
Project.project_source
├── pusat
└── distrik_8
```

Aturan:
```text
Pusat
└── seluruh project

Distrik 8
└── hanya project_source = distrik_8
```

Purchase Order dan Project Summary mengikuti scope melalui relasi Project.

**Dashboard wajib mengikuti aturan akses yang sama.**

---

## 3. Prinsip Dashboard

Dashboard harus menjawab:

1. **What is happening?** — kondisi project, PO, finansial, profit.
2. **Where is attention needed?** — project/PO yang membutuhkan perhatian.
3. **What is changing?** — progress, invoice, expenditure, profit.
4. **What is at risk?** — anomali berdasarkan rule yang benar-benar tersedia.
5. **What should be inspected next?** — drill-down ke detail.

---

## 4. Global Filter

```text
┌──────────────────────────────────────────────────────────────────────┐
│ Dashboard                                                            │
│                                                                      │
│ Sumber Project       Periode                 Refresh                 │
│ [ Semua Sumber ▼ ]  [ Bulan ▼ ]             [↻]                    │
└──────────────────────────────────────────────────────────────────────┘
```

### Source Filter

Pusat:
```text
Semua Sumber
Pusat
Distrik 8
```

Distrik 8:
```text
Distrik 8
```

Filter UI bukan security. Backend query tetap wajib menerapkan scope.

### Period Filter

Candidate:
```text
Bulan ini
Bulan lalu
Quarter ini
Tahun ini
Custom
```

Jangan memaksakan filter waktu pada metric yang tidak memiliki tanggal yang relevan.

---

## 5. Main Wireframe

```text
┌────────────────────────────────────────────────────────────────────────────┐
│ TRACE TKJ — DASHBOARD                                      [User] [Alert] │
├────────────────────────────────────────────────────────────────────────────┤
│ Sumber Project [ Semua ▼ ]     Periode [ Bulan ▼ ]     [Refresh]           │
├────────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│ KPI CARDS                                                                 │
│                                                                            │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌───────────────────┐ │
│ │ TOTAL PROJECT│ │ ACTIVE PO    │ │ CONTRACT     │ │ FINAL PROFIT      │ │
│ │      24      │ │      18      │ │ Rp ...       │ │ Rp ...            │ │
│ │ ...          │ │ ...          │ │ ...          │ │ ... %             │ │
│ └──────────────┘ └──────────────┘ └──────────────┘ └───────────────────┘ │
│                                                                            │
│ ┌──────────────────────────────────┐ ┌─────────────────────────────────┐ │
│ │ PROJECT STATUS                   │ │ FINANCIAL OVERVIEW              │ │
│ │                                  │ │                                 │ │
│ │ Status / progress distribution   │ │ Contract Value                  │ │
│ │                                  │ │ PO Value                        │ │
│ │                                  │ │ Expenditure                      │ │
│ │                                  │ │ Profit                           │ │
│ └──────────────────────────────────┘ └─────────────────────────────────┘ │
│                                                                            │
│ ┌──────────────────────────────────┐ ┌─────────────────────────────────┐ │
│ │ PROJECT PROGRESS                 │ │ PROFIT / BUDGET                 │ │
│ │ Trend / distribution             │ │ Profit % / nominal              │ │
│ │                                  │ │ Remaining Budget                 │ │
│ │                                  │ │ Balance                          │ │
│ └──────────────────────────────────┘ └─────────────────────────────────┘ │
│                                                                            │
│ ┌──────────────────────────────────────────────────────────────────────┐ │
│ │ ATTENTION / ANOMALIES                                                │ │
│ │ ⚠ Project ...                                      [View]             │ │
│ │ ⚠ PO ...                                           [View]             │ │
│ └──────────────────────────────────────────────────────────────────────┘ │
│                                                                            │
│ ┌──────────────────────────────────────────────────────────────────────┐ │
│ │ RECENT PROJECT / PO ACTIVITY                                        │ │
│ └──────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────────┘
```

---

## 6. KPI Cards

Minimum:

### Total Project
Source: `Project`

### Active Project
Gunakan status existing. Jangan membuat definisi status baru tanpa memeriksa model/database.

### Total Purchase Order
Source: `PurchaseOrder`

### Total Contract Value
Gunakan nilai Project Summary sesuai business logic existing.

### Final Profit
Gunakan `ProjectSummary.final_profit`.

### Profit Percentage
Harus konsisten dengan Project Summary.

---

## 7. Project Monitoring

Tampilkan:
- total project
- project berdasarkan source
- project berdasarkan status jika tersedia
- project berdasarkan progress
- project terbaru
- project tanpa progress terbaru jika rule tersedia
- project yang membutuhkan perhatian

Candidate distribution:
```text
0–25%
26–50%
51–75%
76–99%
100%
```

Jangan menyimpulkan project bermasalah hanya dari progress tanpa business rule.

---

## 8. Purchase Order Monitoring

Tampilkan:
- total PO
- total PO value
- PO berdasarkan project source
- PO berdasarkan status jika tersedia
- PO terbaru
- PO progress/invoice

Drill-down:
```text
Dashboard
  ↓
Purchase Orders
  ↓
Purchase Order Detail
```

Semua query PO mengikuti scope Project.

---

## 9. Invoice / Purchase Order Progress

Source:
`PurchaseOrderProgress`

Candidate metric:
- jumlah invoice/progress
- total nominal invoice
- percentage progress
- cumulative nominal amount
- remaining amount

**Penting:** `PurchaseOrderProgress.percentage` adalah percentage invoice terhadap `PurchaseOrder.grand_total` sesuai business logic existing. Jangan mengembalikan logic cumulative percentage lama.

---

## 10. Financial Overview

```text
┌──────────────────────────────────────────────────────────────────────┐
│ FINANCIAL OVERVIEW                                                   │
├──────────────────────┬──────────────────────┬────────────────────────┤
│ Contract Value       │ PO / Commitment      │ Expenditure            │
│ Rp ...               │ Rp ...               │ Rp ...                 │
├──────────────────────┼──────────────────────┼────────────────────────┤
│ Final Profit         │ Remaining Budget     │ Balance                │
│ Rp ...               │ Rp ...               │ Rp ...                 │
└──────────────────────┴──────────────────────┴────────────────────────┘
```

Gunakan formula existing dari Project Summary.

Jangan membuat formula baru hanya untuk dashboard.

---

## 11. Project Summary Monitoring

Fields existing yang relevan:
```text
owner_customer
project_name
po_numbers
contract_value
pph_amount
final_contract_value
final_profit
remaining_budget
balance
profit_mode
profit_percentage
nominal_profit
```

Candidate aggregation:
- Total Final Contract Value
- Total Final Profit
- Total Remaining Budget
- Total Balance
- Profit Percentage

Untuk aggregate percentage, jangan langsung average jika kebutuhan bisnis sebenarnya weighted percentage. Jika belum ada definisi client, tandai **NEEDS BUSINESS CONFIRMATION**.

---

## 12. Profit Monitoring

Profit harus dapat dibaca dalam dua perspektif:

### Nominal
```text
Total Final Profit
Rp ...
```

### Percentage
```text
Profit Percentage
... %
```

Project Summary memiliki dua mode:
```text
Percentage
Nominal
```

Keduanya harus menghasilkan `final_profit` sesuai business logic existing.

Dashboard tidak boleh membuat formula profit alternatif.

---

## 13. Expenditure Monitoring

Source:
`ProjectExpenditure`

Jenis existing:
```text
Material
Labour
```

Candidate metric:
```text
Total Expenditure
Material Expenditure
Labour Expenditure
```

Visual:
```text
Material ███████████████
Labour   █████████
```

Jika ada `expenditure_date`, dapat dibuat trend.

---

## 14. Attention / Anomaly Center

Section:
```text
ATTENTION REQUIRED
```

Hanya tampilkan kondisi yang memiliki dasar data jelas.

Candidate rules:

### Project tanpa progress
Project memiliki progress 0 dan sudah dibuat cukup lama.

### Project progress stagnan
Tidak ada progress terbaru dalam periode tertentu.

### Profit tidak konsisten
`profit_percentage` dan `nominal_profit` tidak konsisten terhadap `final_contract_value`.

### Expenditure tinggi
Contoh: expenditure > budget, hanya jika definisi budget jelas.

### Remaining Budget rendah
Threshold harus dikonfirmasi.

**Jangan hardcode threshold bisnis tanpa persetujuan client.**

---

## 15. Alert Severity

Candidate:
```text
INFO
WARNING
CRITICAL
```

Contoh:
```text
WARNING
Project ABC
Progress tidak berubah selama X hari
[View Project]

CRITICAL
Project XYZ
Balance negatif
[View Summary]
```

Nilai `X` harus menjadi business rule yang dikonfirmasi, bukan asumsi.

---

## 16. Recent Activity

Source candidate:
- Project created
- Project progress added
- PO created
- PO progress/invoice created
- Project Summary updated
- Expenditure created

Wireframe:
```text
┌────────────────────────────────────────────────────────────┐
│ RECENT ACTIVITY                                            │
├────────────────────────────────────────────────────────────┤
│ 22 Sep 2026  Project ABC created             [View]        │
│ 22 Sep 2026  PO-001 added                    [View]        │
│ 21 Sep 2026  Project ABC progress → 75%     [View]        │
│ 21 Sep 2026  Invoice added                   [View]        │
└────────────────────────────────────────────────────────────┘
```

Gunakan timestamp existing.

---

## 17. Drill-down

Dashboard bukan pengganti menu detail.

```text
Total Project
  ↓
Projects index
  ↓
Project View

Total PO
  ↓
Purchase Orders index

Financial / Profit
  ↓
Project Summaries

Expenditure
  ↓
Project Summary
  ↓
Material and Labour Expenditure
```

Jika memungkinkan, teruskan filter/context ke halaman tujuan.

---

## 18. Data Scope — WAJIB

Dashboard tidak boleh memakai query global tanpa scope.

Gunakan konsep existing:
```text
Project::accessibleBy(auth()->user())
PurchaseOrder::accessibleBy(auth()->user())
ProjectSummary::accessibleBy(auth()->user())
```

Aturan:
```text
Pusat
→ seluruh data

Distrik 8
→ project_source = distrik_8
```

Filter UI bukan security mechanism.

---

## 19. Jangan Duplikasi Business Logic

Dashboard hanya membaca dan mengagregasi hasil existing.

Jangan:
```php
$profit = $contractValue * 0.3;
```

Gunakan:
```text
ProjectSummary.final_profit
```

Demikian pula:
- Grand Total → existing PurchaseOrder
- DPP → existing PurchaseOrder
- PPN → existing PurchaseOrder
- Invoice percentage → existing PurchaseOrderProgress
- Project progress → existing ProjectProgress

---

## 20. Hindari Double Counting

Jangan mengagregasi Project Summary melalui join child yang menyebabkan duplikasi.

Contoh risiko:
```text
Project
 ├── PO 1
 │    ├── Item
 │    └── Item
 └── PO 2
```

Contract value yang dijoin ke item dapat terhitung berulang.

Gunakan aggregation pada grain entity yang benar:
```text
Project
ProjectSummary
PurchaseOrder
PurchaseOrderProgress
ProjectExpenditure
```

---

## 21. Responsive Design

Desktop:
```text
4 KPI cards
2-column charts
full-width tables
```

Tablet:
```text
2 KPI cards per row
adaptive charts
```

Mobile:
```text
1 KPI card per row
1 chart per row
horizontal scroll untuk tabel
```

---

## 22. Loading / Empty / Error

Loading:
```text
Loading...
```
atau skeleton.

Empty:
```text
No project data available.
```

Error:
```text
Unable to load this section.
[Retry]
```

Satu widget gagal sebaiknya tidak menjatuhkan seluruh dashboard jika arsitektur memungkinkan.

---

## 23. Visual Hierarchy

Prioritas:
```text
1. Critical / Attention
2. Financial KPI
3. Project / PO status
4. Trend
5. Recent activity
```

Jangan memenuhi dashboard dengan chart yang tidak actionable.

---

## 24. Suggested Filament Architecture

Target:
```text
Filament Dashboard
├── Dashboard Page
└── Widgets
    ├── KPI
    ├── Project Overview
    ├── Financial Overview
    ├── PO Overview
    ├── Profit Overview
    ├── Project Progress
    ├── Expenditure
    ├── Attention Required
    └── Recent Activity
```

Gunakan arsitektur widget Filament v5.7.6 dan sesuaikan dengan struktur aplikasi existing.

Hindari satu Blade raksasa untuk seluruh dashboard.

---

## 25. Data yang SUDAH tersedia

```text
Project
- project_source
- created_at
- project information
- progress relationship

ProjectProgress
- persentase
- keterangan
- waktu_progres
- is_system

PurchaseOrder
- project_id
- subtotal
- discount
- DPP
- PPN
- grand_total
- status
- dates

PurchaseOrderProgress
- purchase_order_id
- invoice_date
- amount
- percentage
- PDF
- description

ProjectSummary
- project relation
- contract_value
- pph_amount
- final_contract_value
- final_profit
- remaining_budget
- balance
- profit_mode
- profit_percentage
- nominal_profit

ProjectExpenditure
- type
- description
- amount
- expenditure_date

User
- project_scope
```

---

## 26. Data yang masih perlu dikonfirmasi

Jangan mengarang threshold:

```text
Project terlambat setelah X hari
Progress stagnan setelah X hari
Remaining budget rendah jika < X%
Expenditure abnormal jika > X%
Profit abnormal jika < X%
Invoice overdue setelah X hari
```

Jika belum ada business rule:
```text
PENDING BUSINESS CONFIRMATION
```

Metric dasar tetap boleh dibuat tanpa alert threshold.

---

## 27. Candidate Data untuk Fase Berikutnya

Jangan langsung membuat migration.

Potential fields:
```text
Project:
- deadline / target completion date
- explicit status jika belum tersedia
- priority
- responsible PIC

Purchase Order:
- due date / payment due date
```

Tambahkan hanya jika client/business requirement sudah dikonfirmasi.

---

## 28. Acceptance Criteria

- [ ] Pusat dapat melihat seluruh data.
- [ ] Distrik 8 tidak dapat melihat data Pusat.
- [ ] Global source filter bekerja.
- [ ] Filter tidak dapat bypass access control.
- [ ] Total Project benar.
- [ ] Total PO benar.
- [ ] Contract Value menggunakan sumber existing.
- [ ] Final Profit menggunakan Project Summary.
- [ ] Profit Percentage konsisten dengan Project Summary.
- [ ] DPP/PPN/Grand Total tidak dihitung ulang berbeda.
- [ ] Invoice/PO progress menggunakan nilai existing.
- [ ] Expenditure tidak double-count.
- [ ] Aggregation tidak double-count akibat child relations.
- [ ] Dashboard dapat drill-down.
- [ ] Empty state tersedia.
- [ ] Loading state tersedia.
- [ ] Error state tersedia.
- [ ] Responsive.
- [ ] Tidak ada perubahan business logic existing.
- [ ] Tidak ada migration baru tanpa kebutuhan terkonfirmasi.
- [ ] Tidak ada data Pusat bocor ke Distrik 8 melalui widget/chart/table/query.
- [ ] Dashboard konsisten setelah Project, PO, Project Summary, Progress, Invoice Progress, dan Expenditure berubah.

---

## 29. Implementation Boundary

Copilot **TIDAK BOLEH**:
1. Mengubah formula Project Summary.
2. Mengubah DPP/PPN/Grand Total.
3. Mengembalikan `PurchaseOrderProgress.percentage` menjadi cumulative.
4. Mengubah access control Pusat/Distrik 8.
5. Menghapus `accessibleBy()`.
6. Membuat migration hanya untuk dashboard tanpa kebutuhan.
7. Membuat fake/mock data.
8. Mengubah struktur Project/PO existing.
9. Mengubah UI existing di luar dashboard.
10. Mengubah business rule agar chart terlihat bagus.

Copilot **BOLEH**:
1. Membuat Dashboard page.
2. Membuat widget.
3. Membuat read-only aggregation query.
4. Membuat dashboard filter.
5. Membuat chart.
6. Membuat alert berdasarkan rule yang telah disepakati.
7. Membuat drill-down URL.
8. Mengoptimalkan query tanpa mengubah hasil bisnis.

---

## 30. Prinsip Akhir

Gunakan alur:

```text
DATA
  ↓
INFORMATION
  ↓
ATTENTION
  ↓
ACTION
```

Bukan:

```text
DATABASE
  ↓
SEMUA ANGKA DITAMPILKAN
```

Dashboard harus menjadi **control center** untuk Project, Purchase Order, Project Summary, Progress, Invoice Progress, dan Expenditure tanpa menggantikan halaman detail masing-masing.
