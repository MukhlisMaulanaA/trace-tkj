# Project Summary — Real-time Inline Profit Editing

Implement the **Real-time Inline Profit Editing** feature on the **View Project Summary** page in Laravel + Filament.

## Objective

Profits can be edited directly on the View page without navigating to the Edit page. Changes must be reflected in real time in the UI, but **the database is only updated once the user clicks `Save Changes`**.

> **Core principle: Real-time UI, Explicit Database Commit.**
>
> Do not use autosave or database updates whilst the user is typing.

---

## 1. Inspect Existing Code First

Before modifying the code, inspect the repository and identify:

- Project Summary Page/View/Blade
- `Project` model
- existing profit field
- existing profit calculation
- Remaining Budget calculation
- Balance calculation
- Final Contract Value calculation
- Existing ‘Edit Project’ implementation
- Filament/Livewire state
- Authorisation/policy
- Database schema/migrations
- Existing validation

**Do not guess field names or create new formulas if existing logic can be reused.**

Use the existing business logic as the single source of truth.

---

## 2. Retain the Existing UI

Do not redesign the Project Summary.

Retain:

- Header/project information
- Contract Value
- Final Contract Value
- M + L Expenditure
- Remaining Budget
- Material Expenditure
- Labour Expenditure
- Profit card
- Balance
- Timeline/other sections
- styling, spacing, typography, colours, and responsive layout

New features should focus solely on the **Profit card**.

The existing ‘Edit Project’ function must remain operational.

---

## 3. Inline Edit Mode

Normal:

```text
PROFIT

30.00%

FINAL PROFIT AMOUNT
Rp42,497,100

Mode: Percentage

[ Edit ]