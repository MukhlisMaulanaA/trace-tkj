**Repository Report**

### 1. Project resource and navigation

- [ProjectResource.php](app/Filament/Resources/Projects/ProjectResource.php)
  - Model: `Project`
  - Pages: `ListProjects`, `CreateProject`, `ViewProject`, `EditProject`
  - Relations: `ProjectProgressRelationManager`, `ProjectSummaryRelationManager`
  - Query scope: `Project::accessibleBy(auth()->user())`
- [ListProjects.php](app/Filament/Resources/Projects/Pages/ListProjects.php) uses `ListRecords` with `CreateAction`.
- [ViewProject.php](app/Filament/Resources/Projects/Pages/ViewProject.php) uses `ViewRecord`, `EditAction`, and explicitly renders both relation managers.
- [ProjectsTable.php](app/Filament/Resources/Projects/Tables/ProjectsTable.php) provides searchable project fields and a `project_source` filter visible to Pusat users.
- [AdminPanelProvider.php](app/Providers/Filament/AdminPanelProvider.php) discovers resources and pages under `app/Filament`; authenticated access is enforced by Filament middleware.
- Existing custom-page pattern: [TraceDashboard.php](app/Filament/Pages/TraceDashboard.php), registered through `discoverPages()` and explicitly in `->pages()`.

### 2. Project and work-progress models

- [Project.php](app/Models/Project.php)
  - String primary key: `id`
  - Fields: `nama_project`, `kustomer`, `kontak_person`, `lokasi`, `nomor_quotation`, `pic`, `project_source`
  - Relations:
    - `progresses(): HasMany<ProjectProgress>`
    - `purchaseOrders(): HasMany<PurchaseOrder>`
    - `summary(): HasOne<ProjectSummary>`
  - Creation automatically adds a system `ProjectProgress` row at `0%` and creates a `ProjectSummary`.
  - Access scope: `scopeAccessibleBy()`
- [ProjectProgress.php](app/Models/ProjectProgress.php)
  - Fields: `project_id`, `waktu_progres`, `persentase`, `keterangan`, `is_system`
  - `waktu_progres` is cast to datetime; `persentase` is an integer database field.
  - Latest non-system progress is treated as current progress in the existing timeline header.
- [ProjectProgressRelationManager.php](app/Filament/Resources/Projects/RelationManagers/ProjectProgressRelationManager.php)
  - Relationship: `progresses`
  - Chronological display with `defaultSort('waktu_progres', 'asc')`
  - Uses custom timeline/header Blade views.
- [ProjectSummary.php](app/Models/ProjectSummary.php)
  - One-to-one financial summary.
  - Relations: `project()` and `expenditures()`.
  - Derived fields include contract value, final contract value, final profit, remaining budget, and balance.
- [ProjectExpenditure.php](app/Models/ProjectExpenditure.php)
  - Fields: `project_summary_id`, `type`, `description`, `amount`, `expenditure_date`
  - Types: `material`, `labour`.

### 3. Purchase order and payment progress

- [PurchaseOrder.php](app/Models/PurchaseOrder.php)
  - Fields include:
    - Identity: `po_code`, `po_number`, `po_date`, `project_id`
    - Snapshot data: `customer`, `location`, `quotation_no`, `pic`
    - State: `status`, `notes`
    - Financials: `subtotal`, discount fields, DPP fields, PPN fields, `grand_total`
  - Relations:
    - `project(): BelongsTo<Project>`
    - `items(): HasMany<PurchaseOrderItem>`
    - `progresses(): HasMany<PurchaseOrderProgress>`
  - `scopeAccessibleBy()` applies project-source access filtering.
  - `calculateTotals()` recalculates `grand_total` from PO items, discounts, DPP, and PPN.
- [PurchaseOrderItem.php](app/Models/PurchaseOrderItem.php)
  - Fields: `item_no`, `section`, `description`, `quantity`, `sat`, `unit_price`, `total_price`
  - `total_price` is calculated on save.
- [PurchaseOrderProgress.php](app/Models/PurchaseOrderProgress.php)
  - Fields: `purchase_order_id`, `title`, `description`, `invoice_date`, `pdf_file`, `amount`, `percentage`, `is_system`
  - `percentage` is automatically calculated as the individual invoice amount divided by PO `grand_total`.
  - Relationship: `purchaseOrder(): BelongsTo<PurchaseOrder>`
- [PurchaseOrderProgressRelationManager.php](app/Filament/Resources/PurchaseOrders/RelationManagers/PurchaseOrderProgressRelationManager.php)
  - Relationship: `progresses`
  - Uses invoice timeline display and calculates total payment by summing `amount`.
  - Existing header computes:
    `sum(progress.amount) / grand_total * 100`
  - Existing UI supports invoice date, description, amount, percentage, PDF, and remaining balance.
- Existing timeline views:
  - [po-progress-header.blade.php](resources/views/filament/tables/headers/po-progress-header.blade.php)
  - [po-progress-timeline.blade.php](resources/views/filament/tables/columns/po-progress-timeline.blade.php)
  - [project-progress-header.blade.php](resources/views/filament/tables/headers/project-progress-header.blade.php)
  - [project-progress-timeline.blade.php](resources/views/filament/tables/columns/project-progress-timeline.blade.php)

### 4. Policies and access

- [ProjectPolicy.php](app/Policies/ProjectPolicy.php)
- [PurchaseOrderPolicy.php](app/Policies/PurchaseOrderPolicy.php)
- [ProjectSummaryPolicy.php](app/Policies/ProjectSummaryPolicy.php)

All currently allow authenticated users to `viewAny`, `create`, and access records when:

- User is Pusat, or
- Record’s related project has `project_source === user.project_scope`.

The authoritative query scopes are:

- `Project::accessibleBy()`
- `PurchaseOrder::accessibleBy()`
- `ProjectSummary::accessibleBy()`

A review page should query `Project::accessibleBy(Filament::auth()->user())` directly. Do not rely only on policy checks for a card collection.

### 5. Tests and route conventions

Existing tests:

- [TraceDashboardTest.php](tests/Feature/TraceDashboardTest.php)
  - Tests stale PO/payment activity and dashboard calculations.
- [ProjectSummaryTest.php](tests/Feature/ProjectSummaryTest.php)
  - Tests project, PO, item, expenditure, and financial calculations.
- [UserResourceAccessTest.php](tests/Feature/UserResourceAccessTest.php)
  - Tests resource-level access.
- No current tests cover Filament project pages, card grids, navigation visibility, sorting, or filtering.

Routes in [web.php](routes/web.php) only redirect `/` to `/admin` and expose authenticated document routes. Filament pages should use Filament page routing rather than a new controller route.

## Recommended minimal implementation

Create a standalone read-only Filament custom page under `app/Filament/Pages`, for example:

- `ProjectReview.php`
- `resources/views/filament/pages/project-review.blade.php`

Recommended page behavior:

1. Query projects using:
   ```php
   Project::query()
       ->accessibleBy(Filament::auth()->user())
       ->with([
           'progresses',
           'purchaseOrders.progresses',
       ]);
   ```

2. Build one card per project containing:
   - Project ID, name, customer, location, source.
   - Latest non-system work progress:
     - `persentase`
     - `waktu_progres`
     - `keterangan`
   - PO totals:
     - PO count
     - Sum of `grand_total`
   - Payment progress:
     - Sum of all invoice `amount`
     - Remaining amount
     - Cumulative percentage against total PO value
     - Latest invoice date
   - Optional per-PO payment rows for detailed payment visibility.

3. Use Livewire page properties for:
   - Search by project ID, name, customer, or location.
   - `project_source` filter for Pusat users.
   - Sort field/direction, such as latest activity, work percentage, payment percentage, PO value, or project name.

4. Keep the page read-only and link cards to:
   - `ProjectResource::getUrl('view', ['record' => $project])`
   - Individual PO view pages where appropriate.

5. Reuse the existing progress status rules and timeline visual language from the current Blade views, but avoid trusting `PurchaseOrderProgress::percentage` for project-level payment totals. Calculate cumulative payment from `amount`.

### Ambiguities and missing data

- `PurchaseOrderProgress::percentage` is per-invoice in the model, while repository documentation/memory describes cumulative percentage behavior. The review should calculate cumulative values independently.
- PO payment totals can exceed `grand_total`; some existing UI clamps displayed percentage while other code permits values above 100. The review should define whether to show overpayment explicitly or clamp only the visual bar.
- There is no explicit project status field. “Status” must be inferred from latest work percentage or payment activity.
- No dedicated review page or tests currently exist.
- `ProjectSummary::PPH_RATE` is `0.0200`, despite a commented `0.0265` constant and some historical wording referring to 2.65%; avoid introducing that calculation into the review unless required.