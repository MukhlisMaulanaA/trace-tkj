# Technical Reference

## Architecture

This is a Laravel 13 application using Filament 5 for the authenticated admin panel and Vite/Tailwind for frontend assets. Operational use is through the admin UI; the app does not expose a public business API.

| Layer | Location | Responsibility |
| --- | --- | --- |
| Routes | `routes/web.php` | Redirects `/` to `/admin` and protects the generated PO document route. |
| Admin UI | `app/Filament/Resources` | Project/PO CRUD and related record screens. |
| Domain | `app/Models` | Relationships, automatic IDs, and financial/progress calculations. |
| Persistence | `database/migrations` | Database schema. |
| Views | `resources/views` | Printable PO document and custom timeline components. |

## Data model

```text
Project (string ID)
  ├── ProjectProgress
  └── PurchaseOrder
        ├── PurchaseOrderItem
        └── PurchaseOrderProgress (invoice)
```

| Table | Key responsibilities |
| --- | --- |
| `projects` | Project master details. Uses a generated string primary key. |
| `project_progress` | Timestamped percentage updates; `is_system` protects the initial row in the UI. |
| `purchase_orders` | PO identity, project snapshot fields, status, financial flags, and totals. |
| `purchase_order_items` | Sectioned lines and calculated line totals. |
| `purchase_order_progress` | Invoice details, document path, amount, and calculated progress. |

Deleting a project nulls its related PO `project_id` and deletes project progress. Deleting a PO deletes its items and invoice progress.

## Financial calculation rules

`PurchaseOrder::calculateTotals()` runs when a PO or PO item is saved, and when an item is deleted. PO totals round to whole rupiah.

Let `S` = item total sum, `D` = discount amount, and `A` = `max(0, S - D)`.

| Rule | Calculation |
| --- | --- |
| Percentage discount | `D = round(S × percentage / 100)`; range 0–100. |
| Nominal discount | `D = max(0, min(S, round(input)))`. |
| DPP enabled | `DPP = round(A × 0.916666666666667)`. |
| PPN with DPP | `PPN = round(DPP × 12%)`. |
| PPN without DPP | `PPN = round(A × 11%)`. |
| PPN disabled | `PPN = 0`. |
| Grand total | `max(0, round(A + PPN))`. |

The DPP amount is displayed but not added to grand total. Discount already affects `A`, so it is not deducted again.

Example, subtotal Rp100,000, 10% discount, DPP and PPN enabled:

```text
Discount = 10,000; after discount = 90,000
DPP = 82,500; PPN = 9,900; grand total = 99,900
```

## Lifecycle behavior

- Project creation assigns `PYYMNNN` (`YY` year, `M` month letter A–L, `NNN` sequence) and creates `Project Created` at 0%.
- PO creation assigns a PO code and defaults status to `draft`.
- Item creation assigns a per-PO sequence; item saving calculates quantity × unit price.
- Invoice saving calculates the cumulative percentage using invoices dated on or before the invoice date.

PO-code generation currently uses the literal pattern `PO{YY}H...`, unlike the project ID’s dynamic month letter. Treat that as an existing numbering convention unless a deliberate business change is made.

## Files and generated document

| File | Public-disk directory | Limit |
| --- | --- | --- |
| Client PO | `purchase-orders/` | 10 MB |
| Invoice PDF | `invoices/` | 20 MB |

Run `php artisan storage:link` in every environment. Back up storage files with the database.

`PurchaseOrderDocumentController` renders the authenticated browser-printable PO view. Browser edits to its editable title/vendor/summary areas do not persist.

## Deployment checklist

1. Set production environment/database values, `APP_DEBUG=false`, a strong `APP_KEY`, and correct `APP_URL`.
2. Install optimized dependencies, build assets, and run `php artisan migrate --force` after a backup.
3. Run `php artisan storage:link`; set web root to `public/`; serve via HTTPS.
4. Create real administrator accounts and remove development seed credentials.
5. Back up and test restoration of database and uploaded documents.
6. Monitor logs and run a durable worker if queued work is introduced.

## Quality and known considerations

```bash
composer test
./vendor/bin/pint --test
```

The repository currently has Laravel example tests only. Add tests before changing calculation rules, especially both discount types, DPP/PPN combinations, and cumulative invoices.

- The app has authentication but no roles/permissions.
- `submitted` exists in the PO status enum, but the standard UI offers no status transition.
- The invoice form shows remaining balance but does not prevent cumulative over-invoicing.
- System timeline protection is application-level rather than a database constraint.

When changing PO fields, calculation rules, uploads, or access controls, update models, migrations, Filament forms, printed views, tests, and the relevant user-facing documentation together.

