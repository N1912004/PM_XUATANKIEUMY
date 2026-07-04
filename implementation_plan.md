# KẾ HOẠCH TRIỂN KHAI — PM_XUATANKIEUMY (Catering Management)

> Laravel 12 + Filament v3. Đối chiếu file `NV_XUATANKIEUMY.xlsx` (Sheet *Nghiệp vụ dự án* + *Cấu trúc CSDL*) với codebase hiện tại (nhánh `quocnghi_dev`).
> Cập nhật: 2026-07-05. **Trạng thái: CHỜ DUYỆT — chưa code.**

---

## A. TÓM TẮT ĐỐI CHIẾU (Excel ⇄ Codebase)

25 bảng trong Excel gần như đã có migration. Các module "✔ Hoàn thành" trong Excel đã chạy được ở mức cơ bản. Phần cần làm là **các mục Excel đánh dấu `✖ Chưa có` / backlog / rủi ro**. Đã xác minh trực tiếp trên code:

| # | Hạng mục (theo Excel) | Trạng thái Excel | Thực tế trong code | Ưu tiên |
|---|---|---|---|---|
| 1 | Menu status `sent` ("Đã gửi khách hàng") | Cần bổ sung | Chỉ `draft/locked` — `menus` migration | **P1** |
| 2 | Lịch sử chỉnh sửa thực đơn (audit log) | ✖ Chưa có | Không có bảng/model | **P1** |
| 3 | Lập thực đơn TUẦN (grid theo thứ) | ✖ Chưa có | Chỉ CRUD từng dòng ngày/ca | **P1** |
| 4 | Recipe tự về `pending` khi giá NL đổi | Backlog | Recipe & Ingredient **không có** `booted()` | **P1** |
| 5 | Guard chống nhập kho lặp (PO done) | Rủi ro | Chỉ check `wasChanged('status')`, done→x→done sẽ nhập lặp | **P1** |
| 6 | Nhập kho ngoài (không qua PO) + đính kèm chứng từ | ✖ Chưa có | Không có cột attachment, không có page | **P2** |
| 7 | Xuất chuyển kho giữa bếp + `frozen_quantity` | ✖ Chưa có | Không có `stock_transfers`, không có `frozen_quantity` | **P2** |
| 8 | Kiểm thực B2/B3 ghi nhận THẬT (đang mock) | ◐/✖ | Giờ chế biến, người/giờ lưu mẫu hard-code | **P2** |
| 9 | Xuất Excel biểu mẫu BYT (hiện là CSV) | ✖ Chưa có | `fputcsv` → `.csv` | **P2** |
| 10 | Báo cáo: tổng giá vốn (Cost) + xuất Excel tài chính | ✖ Chưa có | `getStats()` không nhân giá; nút Excel không có `wire:click` | **P2** |
| 11 | Pivot `supplier_ingredient` (báo giá đa NCC) | Backlog | Đang 1-N qua `ingredients.supplier_id` | **P3** |
| 12 | Phân quyền vai trò (Admin/Thủ kho/Bếp trưởng…) | ✖ Chưa có | Không có spatie/shield, không `canAccessPanel()` | **P3** |
| 13 | Phân cấp Khu vực→Bếp→Kho (`kitchen_id`) | Ngầm định | **Không có `kitchen_id`** trên stocks/menus/POs | **P3** |
| 14 | Ghi lý do chênh lệch (kiểm hàng PO / kiểm kê) | Backlog | Không có cột `reason` | **P3** |
| 15 | Dọn nợ kỹ thuật `employees.area` (string) → `area_id` | Rủi ro | Cần xác minh migration | **P3** |
| 16 | Chat nhóm (realtime/lịch sử/phân kênh) | ◐ Một phần | Mới ở mức UI | **P4** (chờ chốt phạm vi) |

> **Quyết định cần bạn xác nhận trước** (xem mục F).

---

## B. MIGRATIONS CẦN THÊM / SỬA

Tạo bằng `php artisan make:migration ... --no-interaction`. Khi **sửa cột** phải khai báo lại đầy đủ thuộc tính cũ để không mất dữ liệu (yêu cầu Laravel 12).

**P1**
1. `alter menus`: đổi comment/logic status cho phép `draft / sent / locked`. (Cột `status` là `string`, không cần đổi DDL — chỉ cần cập nhật enum ở Model + Filament form.) Bổ sung cột `kitchen_id` (nullable, FK → kitchens, SET NULL) *nếu* làm P3 phân cấp bếp — hoặc để P3.
2. `create menu_audit_logs`: `id, menu_id (FK menus, cascade)`, `user_id (FK users, set null)`, `field (string)`, `old_value (text null)`, `new_value (text null)`, `action (string: created/updated/deleted)`, `edited_at (timestamp)`, `timestamps`.

**P2**
3. `alter stock_transactions`: thêm `attachment_url (string null)` — link file chứng từ nhập kho ngoài. (type vẫn string, thêm giá trị `'Nhập kho ngoài'`, `'Xuất kho sản xuất'`, `'Kiểm kê cuối ngày'`, `'Xuất chuyển kho'`, `'Nhập chuyển kho'`.)
4. `alter food_safety_audits`: thêm dữ liệu thật B2/B3:
   `recipe_id (FK recipes, set null, nullable)`, `cook_start_at (time null)`, `cook_end_at (time null)`, `sample_kept_by (string null)`, `sample_kept_at (datetime null)`, `sample_code (string null)`, `utensil (string null)`.
5. `alter purchase_orders`: thêm `stocked_at (timestamp null)` — cờ idempotency chống nhập kho lặp.

**P2/P3 — Kho đa bếp & điều chuyển**
6. `alter stocks`: thêm `frozen_quantity decimal(15,3) default 0`; (P3) thêm `kitchen_id` + đổi unique `ingredient_id` → composite unique `(kitchen_id, ingredient_id)`.
7. `create stock_transfers`: `id, code (unique), source_kitchen_id (FK kitchens restrict), dest_kitchen_id (FK kitchens restrict), status (string: 'Đang chuyển'/'Hoàn thành'/'Hủy'), created_by (FK users set null), received_by (FK users set null), note (text null), timestamps`.
8. `create stock_transfer_items`: `id, stock_transfer_id (FK cascade), ingredient_id (FK cascade), quantity decimal(15,3), received_quantity decimal(15,3) null`.

**P3**
9. `create supplier_ingredient` pivot: `id, supplier_id (FK cascade), ingredient_id (FK cascade), price decimal(15,2) default 0, unique(supplier_id, ingredient_id)`. Giữ `ingredients.supplier_id` như "NCC mặc định" (cho auto-PO fallback).
10. `alter purchase_order_items`: thêm `received_note / discrepancy_reason (string null)`.
11. `alter areas` (kiểm kê thực tế): xác minh cột `manager_id`; dọn `employees.area` string thừa nếu còn.
12. Cài **spatie/laravel-permission** → chạy migration của package (nếu duyệt P3 phân quyền).

---

## C. MODELS CẦN CẬP NHẬT

- **Menu** (`app/Models/Menu.php`): thêm hằng enum status (`draft/sent/locked`); `hasMany(MenuAuditLog)`; `booted()` — ghi `MenuAuditLog` khi `updated`/`deleted` một menu đã `locked` (so sánh `getChanges()` vs `getOriginal()` cho `recipe_id`, `estimated_portions`, `shift_id`, `status`). Dùng `Auth::id()`.
- **MenuAuditLog** (mới): `$fillable`, `casts(['edited_at' => 'datetime'])`, `belongsTo(Menu)`, `belongsTo(User)`.
- **Recipe** (`app/Models/Recipe.php`): thêm `booted()` — không đổi; logic chính đặt ở **Ingredient**.
- **Ingredient** (`app/Models/Ingredient.php`): `booted()` → `static::updated()`: nếu `wasChanged('reference_price')` thì set tất cả `recipes` chứa nguyên liệu này (qua `recipe_ingredients`) về `status='pending'` (Chờ rà soát). Thêm `belongsToMany(Supplier::class, 'supplier_ingredient')->withPivot('price')` (P3).
- **Supplier**: thêm `belongsToMany(Ingredient::class,'supplier_ingredient')->withPivot('price')` (P3, song song `hasMany` cũ giữ tương thích).
- **PurchaseOrder**: sửa `booted()` — guard idempotency: chỉ nhập kho khi `status==='done' && is_null($stocked_at)`, sau khi nhập set `stocked_at = now()`; thêm `$fillable` `stocked_at`, cast datetime.
- **Stock**: thêm `frozen_quantity` vào `$fillable`+cast; accessor `available_quantity = quantity - frozen_quantity`.
- **StockTransaction**: thêm `attachment_url` vào `$fillable`.
- **FoodSafetyAudit**: thêm các cột mới vào `$fillable` + `casts()` (`cook_start_at`,`cook_end_at` → `datetime:H:i`; `sample_kept_at`→datetime); `belongsTo(Recipe)`.
- **StockTransfer / StockTransferItem** (mới): quan hệ `belongsTo(Kitchen source/dest)`, `hasMany(items)`, `belongsTo(Ingredient)`; `booted()` xử lý đóng băng/cộng tồn theo trạng thái.
- **User**: (P3) `use HasRoles`; `canAccessPanel()` nếu giới hạn.

---

## D. FILAMENT RESOURCES / PAGES

**P1**
- `MenuResource`: thêm `sent` vào Select status (badge màu). Thêm **RelationManager `MenuAuditLogsRelationManager`** (read-only) hiển thị lịch sử sửa; hoặc dùng chung 1 Resource tra cứu.
- **Page mới `LapThucDonTuan`** (Custom Page, `--type=page`): chọn khoảng ngày + ca → grid T2..CN × ca; mỗi ô là Repeater chọn món từ Recipe + số suất; nút Lưu nháp / Gửi khách / Chốt; **validation chống lặp món 3 tuần** (quét `menus` 21 ngày gần nhất, bắn `Notification::warning`). Tận dụng Filament Form schema + `Grid`, tránh HTML thủ công.
- `MenuAuditLogResource` (chỉ list + filter theo menu/người/thời gian) — hoặc RelationManager ở trên.

**P2**
- `StockResource`: thêm **Action "Nhập kho ngoài"** (form: nguyên liệu, số lượng, đơn giá, `FileUpload` chứng từ **bắt buộc**) → cộng tồn + tạo `StockTransaction` type `Nhập kho ngoài`, code `NX-EXT-{Ymd}-{seq}`, lưu `attachment_url`. Thêm Action "Xuất kho sản xuất" (chọn ngày+ca → auto list nguyên liệu theo thực đơn → trừ tồn, chặn quá tồn). Thêm trang **Kiểm kê cuối ngày**.
- `FoodSafetyAuditResource`: form nhập **thật** B2 (`cook_start_at`,`cook_end_at`), B3 (`sample_kept_by`,`sample_kept_at`,`utensil`,`sample_code`); nâng export CSV → **XLSX theo biểu mẫu BYT** (gộp ô B1/B2/B3) — *phụ thuộc quyết định package Excel, mục F*.
- `BaoCao` page: bổ sung **tổng giá vốn** = Σ(số suất × `recipe.actual_price` hoặc Σ cost dòng NL); nối `wire:click` cho nút **Xuất Excel** báo cáo tài chính.

**P2/P3 — điều chuyển kho**
- `StockTransferResource` (mới, `--generate`): tạo phiếu tại bếp xuất (đóng băng tồn), màn bếp nhận "Xác nhận nhận hàng" (Action) → cộng tồn bếp nhận + status `Hoàn thành`; "Hủy" → hoàn `frozen_quantity`.

**P3**
- `SupplierResource`: Repeater/RelationManager gán nguyên liệu + `price` riêng (pivot). `IngredientResource`: cột hiển thị danh sách NCC (implode tên qua pivot).
- Phân quyền: cài Filament Shield (hoặc policy thủ công) → generate permission cho từng Resource; seeder role (Admin/Thủ kho/Bếp trưởng/Nhân viên).
- `KitchenResource`: cân nhắc bỏ ẩn khỏi sidebar (mục Excel dòng Khu vực & Bếp).

---

## E. QUY TẮC NGHIỆP VỤ ĐẶC THÙ (logic)

1. **Auto-pending recipe**: `Ingredient::updated` + `wasChanged('reference_price')` → recipes liên quan `status='pending'`. (Kèm Notification cho user biết món nào cần rà soát.)
2. **Idempotent stock-in**: PO `done` chỉ nhập kho 1 lần nhờ `stocked_at`. Test double-import.
3. **Menu audit log**: mọi sửa/xóa menu đã `locked` bắt buộc ghi vết (ai/gì/khi nào) qua `Menu::booted`.
4. **Validation chống lặp món**: khi thêm món (form ngày & grid tuần) quét 3 tuần gần nhất → Warning.
5. **Frozen stock khi điều chuyển**: bếp xuất tạo phiếu → `frozen_quantity += qty` (không cho xuất phần đang chuyển); bếp nhận xác nhận → `quantity` bếp nhận `+= received`, bếp xuất `quantity -= qty` & `frozen_quantity -= qty`; Hủy → chỉ nhả `frozen_quantity`.
6. **Nhập kho ngoài bắt buộc chứng từ**: `FileUpload->required()`; sinh mã `NX-EXT-…`.
7. **Cost báo cáo** = Σ(số suất × giá cost món), giá cost món = Σ(định lượng × `reference_price`).
8. **Kiểm hàng PO**: chênh lệch = đặt − nhận; (P3) bắt buộc lý do khi lệch.

---

## F. QUYẾT ĐỊNH CẦN BẠN CHỐT TRƯỚC KHI CODE

1. **Package xuất Excel**: hiện chưa có (chỉ CSV). Để xuất `.xlsx` biểu mẫu BYT gộp ô + báo cáo tài chính cần thêm dependency (đề xuất `pxlrbt/filament-excel` hoặc `maatwebsite/excel`). CLAUDE.md cấm tự đổi dependency → **cần bạn duyệt**. Nếu không, giữ CSV.
2. **Phân cấp Khu vực→Bếp→Kho (`kitchen_id`)**: đây là thay đổi schema lớn, ảnh hưởng stocks/menus/POs/transactions. Làm ngay (P1) hay tách phase sau?
3. **Phân quyền**: dùng `bezhanSalleh/filament-shield` (UI generate) hay `spatie/laravel-permission` thuần?
4. **Phạm vi Chat nhóm**: realtime (Reverb/Pusher) hay chỉ lưu tin nhắn DB phân kênh theo bếp? (P4 — có thể để sau.)
5. **Thứ tự ưu tiên**: xác nhận làm theo P1 → P2 → P3, hay ưu tiên nhóm cụ thể (vd BYT export + Cost report trước vì là "yêu cầu chính thức BA lượt 2").

---

## G. KẾ HOẠCH KIỂM THỬ (PHPUnit — feature tests)

- `IngredientPriceChangePendingTest`: đổi `reference_price` → recipe chuyển `pending`.
- `PurchaseOrderDoubleImportTest`: done→checking→done **không** nhập kho lần 2 (`stocked_at`).
- `MenuAuditLogTest`: sửa menu `locked` sinh bản ghi audit đúng field old/new.
- `MenuWeekDuplicateWarningTest`: thêm món đã xuất hiện trong 3 tuần → cảnh báo.
- `StockExternalInboundTest`: nhập kho ngoài cộng tồn + tạo transaction + yêu cầu chứng từ.
- `StockTransferTest`: tạo phiếu đóng băng tồn; xác nhận nhận cộng đúng; hủy hoàn tồn.
- `BaoCaoCostTest`: tổng giá vốn tính đúng theo số suất × cost món.
- `FoodSafetyAuditRealFieldsTest`: lưu & xuất B2/B3 lấy dữ liệu thật (không mock).
- Giữ nguyên các test hiện có: `PurchaseOrderStockSyncTest`, `ListHangAutoPOTest`, `FoodSafetyAuditExportTest` (cập nhật nếu đổi CSV→XLSX).
- Chạy `vendor/bin/pint --dirty --format agent` trước khi kết thúc mỗi phần.

---

**➡️ Dừng tại đây chờ bạn duyệt kế hoạch & trả lời mục F trước khi viết code.**
