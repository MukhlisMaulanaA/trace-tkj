# TKJ Project & Purchase Order Tracker

An internal application for PT. Tanjung Karya Jaya to manage projects, purchase orders (POs), project milestones, and invoice progress.

It gives operations, project, and finance teams one place to keep project details, PO line items and totals, source PDFs, and payment history. The browser-based administration area is powered by Laravel and Filament.

## What the application supports

- Project records with customer, location, quotation, contact person, and PIC details.
- A dated progress timeline for each project.
- Purchase orders linked to projects, with copied details that can be adjusted per PO.
- PO line items, discounts, DPP, PPN, and automatic totals.
- Client PO and invoice PDF uploads.
- Invoice/payment progress against a PO’s grand total.
- A print-friendly TKJ PO document.

## Documentation

| Audience | Document |
| --- | --- |
| Operations, project managers, finance, and general users | [User guide](docs/USER_GUIDE.md) |
| Developers and technical administrators | [Technical reference](docs/TECHNICAL_REFERENCE.md) |

## Workflow

```text
Create project → update project progress → create PO → add PO items
       → verify totals/documents → record invoices → monitor payment progress
```

Projects and POs are separate records. Selecting a project copies its customer, location, quotation number, and PIC into the PO; changing the copied PO values does not change the project.

## Quick start

Requirements: PHP 8.3+, Composer, Node.js/npm, and a supported database. The default environment configuration uses SQLite.

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Configure the database in `.env`; for the default SQLite setup, create `database/database.sqlite`. Then run:

```bash
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Open `http://localhost:8000`; it redirects to `/admin`. On macOS/Linux, use `cp` instead of `copy`.

`composer dev` starts the local server, queue listener, log viewer, and Vite. The development seeder creates `test@example.com`; use it only for local development and replace it with a real administrator account before deployment.

## Common commands

| Purpose | Command |
| --- | --- |
| Run tests | `composer test` |
| Check style | `./vendor/bin/pint --test` |
| Apply style | `./vendor/bin/pint` |
| Build assets | `npm run build` |
| Start development services | `composer dev` |

## Repository map

| Location | Purpose |
| --- | --- |
| `app/Models` | Domain data rules and calculations |
| `app/Filament/Resources` | Admin pages, forms, tables, and relation managers |
| `app/Http/Controllers/PurchaseOrderDocumentController.php` | Generated PO document endpoint |
| `resources/views` | Print layout and timeline UI |
| `database/migrations` | Database schema history |
| `docs` | Stakeholder documentation |

## Operations and security

- The admin panel requires sign-in, but the current app has no role-based access control: authenticated users can access the available resources.
- Uploads use the public filesystem disk: POs are in `purchase-orders/` and invoice PDFs in `invoices/`. Run `php artisan storage:link` and back up both files and database.
- Generated PO documents and uploaded files contain business data. Share them only through approved channels.

Keep these documents updated when workflows, totals, files, access controls, or deployment requirements change.
