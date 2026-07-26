# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 application (codename `PM_XUATANKIEUMY`) with a **Filament v3 admin panel** as the primary interface.
* **Database**: MySQL (`DB_PORT=3307`, database `pm_xuatankieumy`).
* **Current State**: Fully scaffolded and developed — models in `app/Models/` and Filament resources in `app/Filament/Resources/` cover Human Resources, Menu Management, Purchasing (PO), Warehouse/Inventory, and Food Safety Audit.
* **Core Documentation**: `README.md` contains the full local setup guide (in Vietnamese). Detailed business/technical maps live in `.codex/` (untracked, may be absent on fresh clones) — see the Domain Pipeline section below.
* **Changelog**: `CHANGELOG.md` is actively maintained in Vietnamese with dated entries (Added/Changed/Fixed) — append an entry there when finishing a user-visible change, following the existing format.

## Commands

```bash
composer run setup        # First-time setup: install deps, .env, key, migrate, build assets
composer run dev          # Run everything: serve + queue + pail (logs) + vite + Reverb
composer run test         # Clear config, then run full test suite
php artisan test --compact                       # Run all tests
php artisan test --compact --filter=testName     # Run a single test by name
php artisan test --compact tests/Feature/X.php   # Run one test file
vendor/bin/pint --dirty --format agent           # Format changed PHP files (run before finalizing)
npm run dev              # Vite dev server (only if not using composer run dev)
npm run build           # Build frontend assets for production
```

- Create an admin login user: `php artisan make:filament-user`.

### Testing

- Tests run against **SQLite `:memory:`** (`phpunit.xml`), even though the app itself runs on MySQL. Migrations and query code must stay SQLite-compatible or the suite breaks while the app still works — a class of failure that never reproduces locally against MySQL. **MySQL-only SQL functions are the recurring offender**: `DATE_FORMAT`, `YEARWEEK`, `DATE_SUB`/`WEEKDAY` have each broken the suite. Use `substr(date, 1, 7)` for `'YYYY-MM'`, compute ISO weeks in PHP, and branch on the driver when there is no portable expression.
- **Smoke-testing against the real MySQL dev DB** (useful to reproduce a bug that only exists with real data): override the connection in `setUp()` (`config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'pm_xuatankieumy', …])` + `DB::purge('mysql')`), do **not** use `RefreshDatabase`, and wrap the test body in `DB::beginTransaction()` / `DB::rollBack()` in `tearDown()` so the developer's data is never mutated. Delete such tests after use — they hard-depend on a local DB and will fail in CI.
- ⚠️ **`tests/` is currently in `.gitignore`** (alongside `/.codex`) — the whole suite is untracked, so a fresh clone / CI has no tests and teammates never receive new ones. Likely unintentional; confirm with the maintainer before relying on committed tests, and remember that a new test file you add will not show in `git status` until this is fixed.
- Only `User` has a factory. Domain models are built via **fixture helpers on `tests/TestCase.php`** (`createKitchen()`, `createIngredient()`, `createSupplier()`, `createEmployee()`), which fill the NOT NULL columns. Extend those helpers rather than hand-rolling `Model::create()` in each test.
- Coverage is concentrated on the risky inventory/authorization/import/business-invariant paths: `PurchaseOrderInboundTest`, `StockTransferTest`, `WarehouseChecksTest` (end-day reason + PO price lock), `WarehouseGuardsTest` (no negative stock, empty-PO filter, no implicit kitchen), `AuthorizationGuardTest`, `UserManagementTest` (privilege-escalation + last-super-admin guards), `NineMenuComplianceTest` / `CatalogAndPurchaseSuggestionTest` (the BA business rules for the 9 menus), `TimekeepingOvertimeTest`, `IngredientImportPreviewTest`, `RecipesImportTest`, `SupplierExportTest`, `MenuStateMachineTest`, `RecipeCostOverrideTest`, `ListHangLockedFilterTest`, `FoodSafetyAuditItemsTest`, `NineMenuRound2Test` (kiểm thực chỉ lấy `locked`, báo cáo scope theo bếp, KPI tách `confirmed`, export `.xlsx`), `SupplierIngredientPickerTest` (loại-thực-phẩm suy từ nguyên liệu đã tích), `SuperAdminPermissionsTest` (mọi resource phải có policy; tên super-admin lấy từ config), `BaoCaoCostReportTest`, `ListHangAutoPoTest`, `MenuWeekGridTest`, `SupplierPriceLogTest`, `SupplierTypeFilterTest`, `LoginPageSmokeTest`. New inventory/menu-lifecycle/cost mutations belong here.
- Asserting an `.xlsx` download: `Excel::fake()` then `Excel::assertDownloaded('name.xlsx', fn ($export) => $export instanceof XExport)`. Livewire's `->call('export…')` has no `getFileResponse()`.
- **Testing a Filament page** requires two things that are easy to miss: call `Filament::setCurrentPanel(Filament::getPanel('admin'))` in `setUp()`, and give the acting user the Shield permissions the page authorizes against (`Permission::findOrCreate('view_any_ingredient', 'web')` → role → `assignRole`), otherwise `Livewire::test(Page::class)` fails at mount. See `AuthorizationGuardTest` and `IngredientImportPreviewTest` for the pattern.

## Architecture Notes

- **Filament Panel**: configured in `app/Providers/Filament/AdminPanelProvider.php` (served at `/admin`). Auto-discovers Resources, Pages, and Widgets under `app/Filament/`. The **primary color is read at runtime from the `settings` table** (`Setting::get('primary_color')`), not hardcoded — and the provider injects a large inline `<style>` block there (sidebar icon colors grouped by domain, an `!important` rule that repaints every `button[type=submit]` in the primary color, sidebar badge styling). Two consequences: a new sidebar entry whose slug is not listed in that block falls back to grey, and a page that wants an off-primary submit button needs a higher-specificity selector to win (see the login page's `.lg-page button.lg-submit[type="submit"]`).
- **Database Rules**: Always use **MySQL** as the default DB (configured in `.env`). Do not write raw SQL queries in controllers/resources; leverage Filament's automatic ORM queries.
- **Convention**: Before finalizing any code changes in PHP files, you MUST run `vendor/bin/pint --dirty --format agent` to maintain formatting standards.

### Domain Pipeline (big picture)

This is a **catering/kitchen operations** system. The core flow chains resources and custom pages together — changing one link usually ripples downstream:

1. **Master data** → Areas, Kitchens, Shifts, Suppliers, Ingredients, Recipes (ingredient qty per portion **stored in kg**; only `active` recipes are usable — imported/price-changed recipes go `pending` for chef review). Units, ingredient types, **and recipe/dish groups** are all **DB tables with their own resources** (`Unit`, `IngredientType`, `RecipeType`), not hardcoded lists — reference them by relation. `recipe.type`, `ingredient.unit`/`type` are backward-compat string accessors over the `*_id` FKs; writing the string via the mutator `firstOrCreate`s the catalog row.
2. **Weekly menu** → the `ListMenus` week grid writes `Menu` rows through the state machine `draft → sent → confirmed → locked` (see the Menu invariants watch point below). Only **`locked`** menus feed downstream (List hàng, production outbound, food-safety records and the cost report all filter `status = 'locked'`).
3. **Ingredient list** → `ListHang` page aggregates `estimated_portions × recipe_ingredient.quantity_per_portion` across menus, **subtracts available stock** (`quantity − frozen_quantity`) so nothing already in the warehouse gets re-ordered, then generates **draft Purchase Orders** grouped by supplier + split (P1/P2/P3).
4. **Warehouse inbound** → `StockResource/Pages/ListStocks` (modes `po` / `production`) confirms PO receipt or direct voucher inbound, updating `Stock` + `StockTransaction` ledger.
5. **Production outbound / transfers / end-day reconciliation** → all mutate `Stock` and write ledger transactions.
6. **Reports** → `BaoCao` (cost), `Dashboard`, `RecipeExport`, Food Safety 3-step exports.

`.codex/techlead-code-map.md`, `.codex/project-flow-notification-map.md`, `.codex/project-memory.md` and `.codex/9-menu-business-gap-review.md` are detailed maps of models, hooks, pages, and business flows — read them before non-trivial changes when present (the `.codex/` directory is untracked, so it may be missing on fresh clones).

**The BA spec is the acceptance criteria.** `NV_XUATANKIEUMY.xlsx` (untracked, repo root) lists every requirement for the **9 operational menus** (Ngân hàng thực đơn, Kho, Danh sách hàng, Danh sách nguyên liệu, Nhà cung cấp, Đặt hàng, Lập thực đơn, Kiểm thực 3 bước, Báo cáo) with per-row status columns (Phân tích / Lập trình / Kiểm thử) and notes. When closing a gap, update the matching row **and** append a dated entry to `CHANGELOG.md` (Vietnamese, existing format). Back the file up before writing to it — row offsets are easy to get wrong.

### Cross-cutting Architecture

- **Kitchen scoping is fail-closed and must be ENFORCED server-side, never left to a UI filter.** `app/Filament/Concerns/BelongsToKitchen.php` scopes resource queries to the logged-in user's kitchen (`User::currentKitchenId()`, resolved via the linked `Employee`); a non-admin with no kitchen sees **nothing** (`whereRaw('1 = 0')`), not everything. Custom pages must reproduce this by hand: `BaoCao::enforcedKitchenId()` forces the kitchen for ordinary users (its public `$kitchenId` is only an *extra* filter for `super_admin`/`Quản trị viên`), and `ListStocks::operatingKitchenId()` returns `null` rather than falling back to `Kitchen::first()` — an implicit "first kitchen" fallback silently lets someone operate another kitchen's warehouse.
- **Permission model** (see also the Permissions bullet below): `User` **implements `FilamentUser`** — without that interface Filament's `Authenticate` middleware ignores `canAccessPanel()` and only lets people in when `APP_ENV=local`, i.e. every user 403s in staging/production. `User::superAdminRole()` reads the role name from `config('filament-shield.super_admin.name')` — never hardcode `'super_admin'`. Guards live in `UserResource` + `User::booting()`: only a super admin may grant the super-admin role, nobody may edit their own roles, and the **last** super admin can be neither demoted nor deleted.
- **Business catalogs are DB-driven, not hardcoded arrays**: `Catalog` (one `catalogs` table, one row per value, grouped by `Catalog::KITCHEN_TYPE|DEPARTMENT|POSITION|LEAVE_TYPE`) backs kitchen type, department, job title and leave type; read them with `Catalog::options(Catalog::X)` and manage them at `/admin/catalogs`. Same rule already applies to `Unit`, `IngredientType`, `RecipeType`. **Statuses and workflow enums stay hardcoded** (PO status, food-safety step, stock transaction type, employee status) — they are wired into logic, and making them editable breaks the process.
- **Business logic lives in model `booted()` hooks**, not observers/services. Key ones: `Ingredient` updated → sets related recipes `pending` on price change; `Recipe` saving → logs `cost_override` old→new into `recipe_cost_logs`; `PurchaseOrder` updated → one-time stock auto-import when status becomes `done` (guarded by `stocked_at`); `Menu` updated/deleted → writes `MenuAuditLog` (with `$auditReason`); `Supplier` saved → syncs comma `type` string to the `ingredient_type_supplier` pivot; `Stock::recordExternalInbound()` and `StockTransfer::{freezeSourceStock,confirmReceived,cancel}()` hold inventory logic. **Inventory mutations must stay one-time and transaction-safe** — see the double-receiving watch point in the flow map.
- **Permissions**: Filament **Shield** generates `app/Policies/*Policy.php` with permission names like `view_any_recipe`, `create_stock` (multi-word models use `::`, e.g. `view_any_stock::transfer`). After adding a resource, always run:
  ```bash
  php artisan shield:generate --resource=XResource --panel=admin   # policy + permissions
  ```
  Skipping it is silent and nasty: the role screen still renders checkboxes for the resource (Shield builds them from the code), but ticking them **saves nothing** because no `permissions` row exists — and with no policy, ordinary roles can never be granted access. `SuperAdminPermissionsTest::test_moi_resource_deu_da_duoc_sinh_policy` fails when a resource has no policy, so the suite catches the omission. Note `super_admin` bypasses permission checks via Shield's Gate (`define_via_gate` in `config/filament-shield.php`), which is why its permission count can look wrong on the roles screen while it can still do everything; grant new permissions to `Quản trị viên` explicitly.
- **Custom pages are split PHP + Blade**: page class under `app/Filament/Pages/` (or a Resource's `Pages/`), matching view under `resources/views/filament/pages/` or `resources/views/filament/resources/<resource>/pages/`. Inspect **both** when changing page UI/behavior.
- **Hand-rolled resource pages**: several resources (Employee, Timekeeping, PurchaseOrder list/create/edit, Supplier, Stock/warehouse) **replace Filament's CRUD pages with plain Livewire `Page` classes** + custom Blade — Filament's automatic authorization/validation does NOT apply there. When editing these, keep the established conventions:
  - Guard every mutating/exporting Livewire action with `abort_unless(XResource::can{Create,Edit,Delete,View}(...), 403)` (Shield policies).
  - Share one `baseQuery()` between the table and its CSV export so exports honor the same filters **and role scoping** as the visible table.
  - Custom pagination must be windowed (`collect([1, cur-1, cur, cur+1, last])->filter->unique->sort` pattern) — never render every page number.
  - `avatar_url` can hold either a storage path **or** an absolute URL (seeders use Unsplash) — branch with `filter_var($url, FILTER_VALIDATE_URL)` before prefixing `asset('storage/…')`.
  - Helper methods called from Blade inside row loops (e.g. `getIngredientsList()`) must be memoized in a protected property, or they fire one query per row.
  - Validate `request()->query(...)` values against a whitelist before assigning to component state (see `ListStocks::mount()`).
- **Custom-page theming uses `var(--po-*)` CSS variables**: each hand-rolled resource view family defines its palette in a `partials/styles.blade.php` with `:root { … }` / `:root.dark { … }` blocks (see `purchase-orders/partials/styles.blade.php`), and all 16+ custom views consume those variables. Never hardcode hex colors in a custom Blade view — add/reuse a `--po-*` variable in both light and dark blocks, or dark mode silently breaks on that page.
- **SPA mode is ON** (`->spa()` in `AdminPanelProvider`): navigation uses `wire:navigate`, so inline `<script>` in views must be SPA-safe — use `data-navigate-once` + a global registration guard + register immediately if Livewire is already booted (see `chat-nhom.blade.php` for the reference pattern). Never rely on `DOMContentLoaded`. Prefer real `<a>`/`wire:navigate` links over `onclick="window.location=…"` hacks (use the stretched-link pattern for clickable rows).
- **No standalone `Choices.js`**: it is not in `package.json`/`node_modules`, and Filament does not expose its bundled copy as `window.Choices`. Any view that does `new Choices(...)` will silently fall back to a plain `<select>` after retrying. Use the shared **`filament.components.search-select`** partial instead (Alpine + `@entangle`): `@include('filament.components.search-select', ['name' => 'selectedPOId', 'live' => true, 'options' => [['value' => …, 'label' => …, 'sub' => …], …]])`. Its dropdown panel is **`x-teleport="body"` + `position: fixed`, re-anchored to the trigger every frame via `window.requestAnimationFrame` while open** — three separate bugs forced this and each is easy to reintroduce: (1) an `absolute`/`fixed`-inside-card panel gets clipped by the `overflow` of parent tables/cards, hence teleport; (2) Filament scrolls an inner container, not `window`, so `@scroll.window` never fires — the rAF loop is what keeps it glued; (3) `requestAnimationFrame`/`cancelAnimationFrame` must be called as `window.requestAnimationFrame` (bare calls throw `Illegal invocation` under Alpine), the flip-up decision must measure the panel's **real** height (a hardcoded height sends it flying off-screen), and multi-line comments cannot live inside the `x-data` attribute (breaks parsing). `[x-cloak]` is defined in `resources/css/app.css`. Verify UI changes like this in a real browser (Playwright via `npx`, no dep added) — three prior CSS-only guesses did not fix it.
- **Real-time chat**: `ChatNhom` page broadcasts `MessageSent` over the private `chat-nhom` channel via **Reverb** (requires `php artisan reverb:start`, included in `composer run dev`) with Laravel Echo (`resources/js/app.js`).
- **Localization — two coexisting systems**: legacy flat strings in `lang/en.json` + `lang/vi.json` (`__('Some text')`), and newer **namespaced PHP files** `lang/{en,vi}/{ingredient,settings,validation}.php` (`__('ingredient.name')`). Newer resources use the PHP files; keep both locales in sync when adding a key. `/lang/{locale}` (route `lang.switch`, whitelist `en`/`vi`) switches session locale.
- Domain terms are Vietnamese (e.g. status `Đang chuyển`/`Hoàn thành`/`Hủy`, transaction types `Nhập kho`/`Xuất kho`) — these are stored **as enum-like DB values, not display labels**, so preserve the exact strings and never translate them in place.
- **Runtime settings**: `Setting` model provides cached `get`/`set` key/value (branding, colors), edited via the hidden `SystemSettings` page.
- **Storage paths — never rebuild them by hand**: the `local` disk root is `storage_path('app/private')` (Laravel 11+ layout), not `storage_path('app')`. Filament `FileUpload` returns a path *relative to the disk*, so resolve it with `Storage::disk('local')->path($path)` and delete with `$disk->delete($path)`. Hardcoding `storage_path('app/'.$path)` yields a path that never exists — and a `file_exists()` cleanup guard around it silently never fires, so failed uploads pile up.
- **`Ingredient` and `Recipe` are soft-deleted; their unique indexes do NOT include `deleted_at`** (`ingredients_code_unique` on `code`; recipes on `code`). A trashed row still occupies the unique index but the default scope hides it, so `updateOrCreate` / `firstOrCreate` / any lookup-by-name will not see it, will attempt an `INSERT`, and MySQL raises `1062 Duplicate entry` — or silently creates a duplicate that reappears on restore. Every by-code/by-name lookup that may hit a deleted record must use `withTrashed()`/`onlyTrashed()` and decide explicitly to restore or skip (see `IngredientsImport` restoring trashed ingredients, and `RecipesImport` *skipping* trashed recipes with a restore hint). Their custom list pages replicate the full Filament soft-delete UX by hand (trash filter, restore/force-delete row + bulk actions, each `abort_unless`-guarded); their `Resource::getEloquentQuery()` drops `SoftDeletingScope` so View/Edit can open trashed records.
- **Only `locked` menus feed downstream, and every consumer must filter it.** List hàng, production outbound, `BaoCao` **and `ListFoodSafetyAudits`** all `->where('status', 'locked')`; a consumer that forgets it silently builds paperwork (or costs, or purchase orders) from drafts. `ListMenus::getStats()` must count `confirmed` separately — `pending` is `total − sent − confirmed − locked`, otherwise customer-confirmed menus are reported as drafts.
- **Menu lifecycle is a 4-state machine enforced in the model, not just pages**: `Menu::STATUS_ORDER` (`draft<sent<confirmed<locked`), `STATUS_LABELS`, `editBlockReason($newStatus,$reason)` (returns `'past'|'downgrade'|'need_reason'|null`), and `isPastLocked()` centralize the rules; the writer surfaces (`ListMenus` week grid/day, the standard `EditMenu` page) call `editBlockReason()`. The week grid (`ListMenus`, view `week`) is the **canonical** weekly editor — the older repeater page `LapThucDonTuan` is hidden from navigation (`$shouldRegisterNavigation = false`) but its route still exists; don't send new work there. Rules: never downgrade status, past-dated `locked` menus are hard-locked, editing a `locked` menu requires a reason. The reason rides on the transient `Menu::$auditReason` property, which the `updated`/`deleted` `booted` hooks persist to `menu_audit_logs.reason` (only for finalized statuses). There is a DB unique index on `(kitchen_id, date, shift_id, recipe_id)` — any `Menu::create` in a cell that may already hold that dish must look it up and update instead of inserting (see `saveDayMenu`).
- **Recipe cost has one canonical source: `Recipe::effectiveCostPerPortion()`** — returns `cost_override` if set, else `Σ(quantity_per_portion × ingredient.reference_price)`. The list table, `RecipeExport`, `BaoCao`, and the detail view all call it; any new cost display must too, or numbers diverge across screens. Setting/changing `cost_override` requires a reason (form-enforced) and the model's `saving` hook logs old→new into `recipe_cost_logs`. Supplier price edits + Excel imports log into `supplier_price_logs`; supplier legal docs (contract/ATTP cert + expiry + file) live in the `suppliers.documents` JSON column, managed by the `ManagesSupplierDocuments` trait shared by Create/EditSupplier (Dashboard warns on ≤30-day/overdue expiry).
- **Warehouse invariants** (`ListStocks` + `Stock`/`StockTransaction`): the ledger is the source of truth for *history* — `stocks.quantity` is only the **current** balance, so the stock as of a past day is `current − Σ(transactions after that day)` (`getSystemQuantities()`; transactions are signed, inbound `+` / outbound `−`). Consequently the end-of-day stocktake **adjusts** (`quantity += diff`, voucher `KK-YYYYMMDD`) instead of overwriting, or counting a past day would erase every movement made since. **Stock may never go negative** — reject a negative count and reject an adjustment that would drive the balance below zero. Quantities everywhere are **kg**: a view that divides by 1000 before printing "kg" is a bug (it has happened twice, in `BaoCao` and the food-safety B1 table).
- **`FoodSafetyAudit.cook_start_at` / `cook_end_at` are TIME columns returned as raw strings** — only `date` and `sample_kept_at` are cast to Carbon. Never call `->format()` on the cook-time fields (throws `format() on string`); slice `substr($v, 0, 5)` for `HH:MM` (see the `timeRange()` helper). This bit the food-safety page once already.
- **Tailwind v4 (project) overrides Filament v3's (Tailwind v3) responsive grid utilities**, so Filament forms collapse wrong on mobile unless patched. `resources/css/app.css` carries the fixes (e.g. the `--cols-lg` media-query block and `.fi-fo-toggle` transform reset); when a Filament form/layout breaks only below a breakpoint, the fix belongs there, not in a per-page style. Rebuild assets (`npm run build`) after touching `app.css`.
- **Filament notifications render HTML**: `Notification::body()` is typed `string`, but the view runs `str($body)->sanitizeHtml()->toHtmlString()`. Use `<br>` for line breaks (`\n` is swallowed) and `e()` any user/file-derived content before concatenating.
- **Excel import/export uses `maatwebsite/excel`, not Filament's importer. Every user-facing export must be `.xlsx`** — the BA spec rejects CSV as a deliverable, so `streamDownload` + `fputcsv` is not an acceptable shortcut (it was removed from the PO, menu and food-safety pages for exactly that reason). Filament's built-in `ImportAction` reads **CSV only** (`League\Csv`) and needs the `imports` / `failed_import_rows` tables, which this project has never migrated — do not reach for it to handle `.xlsx`. Instead:
  - Exports (`app/Exports/`) and imports (`app/Imports/`) are hand-written. `IngredientsImport` reads columns **positionally** (`$row[5]` is the price) and skips the first 2 rows as titles, so `IngredientsExport`'s column order is load-bearing: renaming a header is safe, moving a column breaks export→re-import round-trips.
  - Auto-created lookups must satisfy NOT NULL columns that have no default (e.g. `suppliers.type`), or every row referencing a new supplier fails.
  - Wrap each row in its own `DB::transaction` and collect per-row outcomes rather than letting one bad row abort the file; surface skipped rows with their **real Excel row number** (data starts at row 3).
  - A preview step is built by running the real import inside `DB::beginTransaction()` … `rollBack()` (`dryRun()`), exposed through a two-step wizard — `Filament\Actions\Action` supports `->steps([Step::make(…)])` via the `HasWizard` trait. Per-row transactions nest as savepoints, so the outer rollback still discards everything. Both Ingredient and Recipe list pages share this exact pattern (dry-run + `import-preview.blade.php` partial). The preview partial **caps rendered rows (200/group)** and shows the skipped group first — imports can be thousands of rows and un-capped modals hang the browser; the per-group badge counts stay exact.
  - `RecipesImport` reads the `ĐỊNH LƯỢNG MÓN ĂN.xlsx` template positionally: a row with a dish name opens a dish, blank-name rows below are its ingredients, the group column (C) carries forward, and quantities are **gram → kg** (`/1000`). Imported dishes land `pending`.
  - Excel **exports mirror the real business templates in `docs-mau/`** (gitignored). `RecipeExport` and `IngredientsExport` share the header style (`0F4C81` fill, merged title, thin borders); `PurchaseOrderTemplateExport` follows `MẪU ĐƠN ĐẶT HÀNG.xlsx` (per-ingredient-type sections + totals + signature block). **Food-safety 3-step export** now goes through `app/Support/FoodSafetyExcelTemplateRenderer.php`, which loads the real template `MẪU KIỂM THỰC 3 BƯỚC_SHOW.xlsx` **from the repo root** (`base_path()`, QĐ 1246) and shares one `prepareSheet()` between the on-page HTML render and the `.xlsx` export (`exportWorkbook()` merges the 5 sheets B1–B5, `applyExportStyling()` fixes fonts/zoom/borders only for the downloaded file) — so a single edit keeps screen and file in sync. The older `app/Exports/FoodSafetyAuditReportExport.php` + `app/Exports/Sheets/FoodSafetyStepSheet.php` (hand-built columns) are **no longer used**; edit the renderer, not those. Match the template when editing exports, not ad-hoc columns.
  - **PhpSpreadsheet styling is quadratically expensive per setter**: each individual style setter (`->getFont()->setName()`, `->setSize()`, …) is a full range-scan plus an md5 re-hash of the whole cellXf collection, so chains of setters on large ranges took the food-safety export to ~12s. The renderer batches everything into **one `applyFromArray` per range** (11,7s → 1,5s); keep that pattern in any new styling code, never re-split into setter chains. Template header cells are **RichText** whose runs carry their own 36pt font — cell-level font settings do not win; the renderer flattens RichText to plain text first. Rendered HTML and exported bytes are both cached ~10 min keyed by data hash (`exportBytes()` stores **base64** because the cache driver is the MySQL `cache` table, which can't hold raw binary).
- **Money inputs — separator convention must match end-to-end**: any masked price field must use ONE separator convention across mask, `formatStateUsing`, `stripCharacters`/dehydrate, and any Alpine formatter in Blade. A VN-style mask (`.` thousands) combined with a US-style strip (`,` only) silently divides values by ~1000 on save (dot survives into the `(float)` cast). Also note `Create/EditSupplier::syncIngredients()` writes pivot prices back onto `ingredients.reference_price` — fixing a price form in one place is not enough.

### Deployment & Environments

- **The local `.env` may point straight at a live/shared DB.** `DB_HOST` is not always `127.0.0.1`: the working copy has connected directly to the dev/live MySQL (`vps-dev.citgroup.vn`, database `db_chuanmy`) used by the deployed site `https://chuanmy.khodemoapp.online`. **Check `grep '^DB_' .env` before running any mutating `artisan`/`tinker` command** — a "test" migration or seeder can hit production data. To change live data deliberately, use `php artisan tinker --execute '…'` with `Hash::make()` for passwords (never write a bare string to `users.password`).
- **A dead Filament UI (buttons do nothing, no validation) with a healthy backend is almost always Livewire not booting client-side**, not a server 500. Confirm the backend independently by replaying the Livewire login over `curl` (GET `/admin/login` for cookies + `wire:snapshot`, then POST the snapshot to `/livewire/update` calling `authenticate`) — a `redirect` effect to `/admin/dashboard` proves auth/DB/roles are fine. If that works but the browser can't log in, the fault is asset delivery.
- **Production nginx gotcha — `/livewire/livewire.js` returning `404` while serving the correct JS body.** A regex `location ~* \.(js|css)$ { try_files $uri =404; }` block (or `fastcgi_intercept_errors on` + `error_page 404`) stamps 404 onto Livewire's dynamically-served asset; the browser then refuses to execute the `<script src>` and Livewire/Alpine never initialize. Fix on the server: make the static-asset location fall back to `try_files $uri /index.php?$query_string;` (or remove it so `location / { try_files $uri $uri/ /index.php?$query_string; }` handles it) and ensure `fastcgi_intercept_errors off`. Verify with `curl -s -o /dev/null -w '%{http_code}' https://…/livewire/livewire.js` → must be `200`, not `404`. `/livewire/update` returning `405` (not `404`) confirms the routes themselves are registered.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2
- filament/filament (FILAMENT) - v3
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

## Bluefire UI/UX Design System Standard (`bluefire_demo.html`)

All Views, Filament Pages, Custom Blade Templates, and Components in `PM_XUATANKIEUMY` MUST strictly adhere to the `bluefire_demo.html` mockup specification:

### 1. Typography & Hierarchy
- **Font Family**: `'Inter', system-ui, sans-serif` only (`<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">`).
- **Font Scaling & Weights**:
  - **H1 / Page Title**: `22px`, `font-weight: 800`, `letter-spacing: -0.025em`, `#0F172A`.
  - **Sub-header / Description**: `13px`, `font-weight: 400`, `#64748B`.
  - **KPI Values**: `28px`, `font-weight: 800`, `letter-spacing: -0.03em`, `#0F172A`.
  - **Card / Section Titles**: `15px`, `font-weight: 700`, `#0F172A`.
  - **Form Labels**: `12.5px`, `font-weight: 600`, `#334155`.
  - **Inputs & Selects**: `13px`, `font-weight: 400/500`, `#0F172A`.
  - **Buttons**: `13px`, `font-weight: 600`, height `40px`, border-radius `9px`.
  - **Table Headers (TH)**: `11px`, `font-weight: 700`, uppercase, `letter-spacing: 0.07em`, `#94A3B8`.
  - **Table Data (TD)**: `13px`, `#0F172A`, vertical alignment `middle`.
  - **Codes & IDs**: `12px`, `font-weight: 600`, `#64748B`.
  - **Units & Currencies**: MUST be bold and share the exact same color as their preceding quantity or value (e.g. `3 Kg`, `12.500.000 đ`). Do NOT split into a separate light-gray span.

### 2. Color Palette Tokens
- **Primary Accent**: `linear-gradient(135deg, #1474FF, #0059DD)` or `rgb(var(--primary-600))`.
- **Text Color Scale**: `--tx` (`#0F172A`), `--su` (`#334155`), `--mu` (`#64748B`), `--fa` (`#94A3B8`).
- **Backgrounds & Borders**: Nền chính `#F4F7FB`, Card `#FFFFFF`, Viền `#E2E8F0`, Viền nhạt `#F1F5F9`.

### 3. Component & Layout Structure
- **Card Padding & Radius**: Cards have `22px 24px` padding, radius `14px`.
- **Form Action Footer**: Fixed at bottom (`position: fixed; bottom: 0; left: var(--sidebar-width); right: 0;`), backdrop blur, top border, 3 action buttons (`Hủy`, `Lưu nháp`, `Lưu`) horizontal right-aligned with `gap: 12px`.
- **Status Toggle**: Label `Trạng thái`, toggle switch button, and active text (`Đang hoạt động` / `Ngừng hoạt động`) rendered on **1 single horizontal row**. Switch button MUST include `.fi-fo-toggle` class to prevent Tailwind v4 double-translate overflow.
- **Kitchen Selector Bar**: `px-5 py-3.5 gap-3.5 rounded-xl shadow-sm`, label `"Bếp ăn:"` (never `"Đang chọn Bếp ăn:"`).
- **Table Numbers**: Thousand separators for numbers (e.g. `444.444`), no `.00` trailing zeros on integers.

</laravel-boost-guidelines>
