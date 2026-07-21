# Changelog

All notable changes to this project will be documented in this file.

## [2026-07-21]

### Changed (Chuẩn hóa vòng đời lập thực đơn)
- Rút gọn vòng đời thực đơn còn đúng ba trạng thái tiến một chiều: **Nháp → Đã gửi khách hàng → Đã chốt**; migration chuyển dữ liệu confirmed cũ về sent và model chặn trạng thái lạ hoặc hạ trạng thái.
- Trang lập thực đơn ngày/tuần cho phép chọn trạng thái ngay khi tạo, chỉ hiện **Xuất Excel** sau khi đã lưu, chỉ yêu cầu lý do khi sửa thực đơn đã chốt và khóa cứng thực đơn đã chốt trong quá khứ.
- Bỏ nút/trạng thái “Khách đã xác nhận”, bỏ hai thẻ tóm tắt/lưu ý ở form ngày, bỏ ngày khỏi tiêu đề, đổi số suất mặc định thành 1 và bổ sung validation phía server cho bếp, ngày, món, số suất và trạng thái.
- Chuẩn hóa luồng lưu thực đơn ngày: Đồng bộ kiểm tra `isMenuBlocked` với model hook `Menu::saving()`, tự động làm sạch ô rỗng và loại bỏ lỗi ngầm khi lưu thực đơn ngày.
- Ẩn trường nhập lý do sửa khi tạo mới (`mode=create`), chỉ hiển thị khi chỉnh sửa (`mode=edit`) thực đơn đã chốt.
- Tối ưu hóa sắp xếp ca theo thứ tự `sort_order` & `id` từ danh mục ca làm việc DB.
- Nâng cấp thanh phân trang danh sách thực đơn, bổ sung tùy chọn `5, 10, 20, 50` bản ghi trên mỗi trang và cho phép điều hướng mượt mà.
- Nâng cấp file xuất Excel thực đơn ngày (`MenuExport.php`) khớp 100% mẫu CJ Catering / BlueFire: Tự động đa ngôn ngữ (VI/EN) cho tên file (`menu-YYYY-MM-DD.xlsx`), tên sheet, tên công ty, tiêu đề banner (`SAVORY MENU 46`), tiêu đề cột (`STRUCTURE` | `DISH NAME`) và tiền tố món (`DISH 1`), tự động lấy ảnh logo hệ thống từ Cài đặt (`Setting::get('site_logo')` tại `/admin/system-settings?tab=-thuong-hieu-tab`), mở rộng độ rộng Cột A (width = 30) giúp logo hiển thị thoáng đẹp, nhúng trực tiếp qua PhpSpreadsheet Drawing vào ô A1:A3, xuất ĐÚNG số món ăn thực tế có trong ngày (không in dư dòng trống), gom toàn bộ món ăn không phân biệt ca và viền xanh cyan `#00B0F0`.
- Nâng cấp file xuất Excel thực đơn tuần (`MenuExport.php` ➔ Sheet `TD`) khớp 100% Ảnh mẫu 2: Áp dụng mã màu chuẩn 100% từ file mẫu (`B2/A2/B3` fill xám `#BFBFBF` chữ đỏ, `C2:J2` fill xám nhạt `#D8D8D8` chữ đỏ, `C3` fill kem đào `#FBE4D5` chữ xanh, viền xanh cyan `#00B0F0`), nhúng trực tiếp Logo hệ thống từ Cài đặt (`Setting::get('site_logo')`), tự động lấy tên ca động từ DB (`Shift`) và **tự động ẩn hoàn toàn các khối ca không có thực đơn trong tuần**.
- Fix lỗi nhận diện nhầm mẫu xuất & Đặt tên file xuất Excel rõ ràng: Phân định rõ thực đơn ngày (`thuc-don-ngay-YYYY-MM-DD.xlsx` / `daily-menu-YYYY-MM-DD.xlsx`) và thực đơn tuần (`thuc-don-tuan-YYYY-MM-DD.xlsx` / `weekly-menu-YYYY-MM-DD.xlsx`), hỗ trợ đổi tên chuẩn theo ngôn ngữ hệ thống VI/EN và đảm bảo khi bấm xuất từ tuần sẽ luôn tải đúng Mẫu Ma trận Thực đơn Tuần (Sheet `TD`).

## [2026-07-20]

### Changed (Chuẩn hóa giao diện Filament v3 tràn rộng MaxWidth::Full)
- Đã cấu hình `AdminPanelProvider.php` sử dụng `maxContentWidth(MaxWidth::Full)`, `sidebarWidth('15rem')` và `sidebarCollapsibleOnDesktop()`.
- Chuẩn hóa toàn bộ 19 Blade Custom Pages và Resource Pages với wrapper `w-full space-y-6`, loại bỏ các giới hạn chiều rộng hẹp (`max-w-*`) ở cấp trang.
- Giữ nguyên khoảng cách lề chuẩn 24–32px từ sidebar và mép phải trên màn hình desktop, đồng bộ header, KPI, bộ lọc và bảng dữ liệu.

### Changed (Chuẩn hóa tùy chọn đơn giá món ăn)
- Đổi `recipes.price_option_id` dạng boolean sang `recipes.price_option` dạng mã chuỗi cố định, gồm `none`, `by_unit`, `by_contract`; không tạo bảng danh mục riêng.
- Bổ sung đầy đủ ba lựa chọn `Không`, `Theo đơn vị`, `Theo hợp đồng` trên form tạo/sửa và đồng bộ trang chi tiết cùng bản dịch VI/EN.
- Migration giữ dữ liệu cũ an toàn: `0 → none`, `1 → by_contract`; thêm validation và kiểm thử lưu đủ ba mã.

### Changed (Cập nhật cột Ngày tạo và Bộ lọc giá món ăn)
- Đổi cột "Cập nhật" (`updated_at`) thành "Ngày tạo" (`created_at`) trên bảng danh sách món ăn (`/admin/recipes`) và trong file xuất Excel (`RecipeExport.php`).
- Tách bộ lọc giá thành 2 nhóm lọc khoảng (Từ - Đến) độc lập: "Giá bán" (`selling_price_per_portion`) và "Cost chuẩn" (`cost_per_portion`). Đồng bộ bộ lọc cả trên Filament Resource và trang danh sách Livewire custom.
- Bổ sung key dịch còn thiếu `recipe.empty.no_filtered_dishes` và `recipe.empty.no_ingredient_quantities` trong `lang/vi/recipe.php` và `lang/en/recipe.php` khi bảng không có kết quả lọc.
- Áp dụng định dạng tiền tự động dạng `4.444` (phân cách hàng nghìn) trực tiếp khi người dùng gõ vào các ô lọc khoảng "Giá bán" và "Cost chuẩn", đồng thời duy trì lọc dữ liệu thời gian thực.

### Added (Tech Lead Code Review & Knowledge Memory Update)
- Rà soát toàn bộ luồng code dự án, kiến trúc hệ thống, quy tắc nghiệp vụ 9 menu, scoping bếp, phân quyền Shield/Gate, chuẩn hóa ca làm việc, bóc tách danh mục DB và hệ thống i18n.
- Cập nhật toàn bộ tri thức kỹ thuật và bản đồ mã nguồn tại `.codex/project-memory.md` và `.codex/techlead-code-map.md` với đầy đủ thông tin cập nhật tính đến tháng 07/2026.

### Changed (Tạm ẩn cost điều chỉnh món ăn)
- Tạm ẩn trường `Cost điều chỉnh (override)` và `Lý do điều chỉnh cost` trên form tạo/sửa món ăn; giữ nguyên dữ liệu và logic nền để có thể bật lại khi cần.
- Đổi tên kỹ thuật hai cột giá món ăn thành `standard_price_per_portion` và `selling_price_per_portion`; giữ nguyên nhãn tiếng Việt, làm rõ nhãn tiếng Anh theo giá tiêu chuẩn và giá bán thực tế mỗi suất.
- Chuẩn hóa tùy chọn đơn giá vào `recipes.price_option_id` dạng boolean (`0 = Theo đơn vị`, `1 = Theo hợp đồng`), không còn lưu chuỗi hay dùng bảng danh mục riêng; bỏ helper text dưới mức giá và đổi trường mô tả thành textarea.
- Gỡ hai thẻ phụ `Tóm tắt cost` và `Thông tin tìm kiếm` khỏi trang chi tiết món; dọn toàn bộ biến, CSS và key dịch chỉ phục vụ hai thẻ này, giữ nguyên lịch sử giá và logic tính cost.
- Bổ sung `Tùy chọn đơn giá` và `Mô tả` trên trang chi tiết món để khớp form tạo/sửa; giá vốn luôn hiển thị bằng số tiền, không còn bị lựa chọn Có/Không thay thế. Bỏ khối “Người tạo” giả lập vì món chưa lưu trường người tạo.
- Giới hạn mô tả dài trên trang chi tiết ở 160 ký tự, dùng nút `Xem thêm/Thu gọn` thuần HTML để mở nội dung đầy đủ; đổi `Ngày cập nhật` thành `Ngày tạo` và lấy đúng `created_at`.
- Căn lại dòng mô tả thu gọn thành một hàng: biểu tượng mở, nội dung co giãn có dấu ba chấm và nút `Xem thêm` cố định cuối dòng; nội dung đầy đủ chỉ xuống hàng sau khi mở.
- Đổi ghi chú từng nguyên liệu trên form món thành textarea giới hạn 255 ký tự; trang chi tiết thu gọn ghi chú dài trong cột cố định và hỗ trợ `Xem thêm/Thu gọn`, không làm vỡ bảng trên mobile.
- Căn lại ngữ nghĩa giá theo xác nhận nghiệp vụ: `selling_price_per_portion` là giá bán cho khách, `cost_per_portion` là giá vốn mỗi suất; sửa lỗi mask DECIMAL khiến giá 2.000.000 hiển thị thành 200.000.000 trên form edit.
- Form món ăn cho nhập mã thủ công và kiểm tra trùng; hai trường giá chuyển từ danh sách cố định sang ô nhập tiền mặc định 20.000đ; bỏ hành động lưu nháp trên trang tạo món.
- Bỏ tiêu đề section `Bảng nguyên liệu & cost trên 1 phần`; nhãn từng dòng định lượng đổi từ tên nguyên liệu sang `STT 1, 2, 3...` và tự cập nhật theo vị trí.
- Căn STT, nội dung dòng nguyên liệu và nút xóa trên cùng một hàng; tạm ẩn khối cảnh báo lãi/lỗ khỏi form món ăn.
- Tách nhãn `STT` và số dòng thành hai tầng thẳng cột với label/ô nhập; giữ nhãn `Thành tiền` một dòng và căn giá trị cùng hàng với nội dung các ô.
- Chuẩn hóa đơn vị tiền theo locale trên form món ăn: tiếng Việt hiển thị `đ`, tiếng Anh hiển thị `VND`; giữ `Line total` là bản dịch nghiệp vụ của `Thành tiền` theo từng dòng nguyên liệu.

### Changed (Chuẩn hóa dữ liệu khung giờ ca làm việc)
- Thêm `shifts.time_from` và `shifts.time_to` kiểu `TIME` làm dữ liệu chuẩn; migration tự chuyển các chuỗi `HH:MM - HH:MM` hiện có rồi xóa cột text `time_range`. Model cung cấp accessor `time_range` chỉ để các màn hình cũ tiếp tục hiển thị.
- Form bắt buộc nhập `time_from`/`time_to`, cho phép chọn chính xác đến từng phút, không cho hai giờ trùng nhau, giới hạn tối đa 12 giờ và chấp nhận ca qua ngày như `16:00 → 00:00`. Bỏ dữ liệu seed `Ca đặc biệt` không có khung giờ; migration không backfill các khung giờ cũ sai quy tắc.
- Migration tiếp theo dọn các ca sai quy tắc chưa được sử dụng và chuyển hai cột giờ sang `NOT NULL`; nếu ca sai đang được nghiệp vụ tham chiếu, migration dừng với thông báo để tránh xóa dữ liệu đang dùng.

### Changed (Gom nhóm menu Cung ứng & Kho)
- Gom `Danh sách hàng`, `Nhà cung cấp` và `Đặt hàng` vào cùng nhóm `CUNG ỨNG & KHO`; sắp thứ tự 1–2–3 và đưa nhóm lên ngay sau `NGUYÊN LIỆU & KHO`. Đồng bộ tên nhóm tiếng Anh thành `SUPPLY & WAREHOUSE` để không bị tách menu khi đổi locale.

### Changed (Chuẩn hóa đa ngôn ngữ VI/EN toàn bộ giao diện quản trị)
- Rà soát toàn bộ Filament resources, custom Livewire pages và Blade views; chuyển text giao diện hardcode sang key trong `lang/vi` và `lang/en` cho các phân hệ nhân sự, thực đơn, nhà cung cấp, đặt hàng, kho, kiểm thực, báo cáo, dashboard và chat.
- Bổ sung các file dịch namespaced theo từng domain, đồng bộ tập key giữa VI/EN và hoàn thiện parity cho `vi.json`/`en.json`; giữ nguyên các giá trị trạng thái nghiệp vụ lưu DB và nội dung biểu mẫu pháp lý QĐ 1246.
- Thêm kiểm thử parity file/key VI/EN và cập nhật kiểm thử giao diện nhà cung cấp để kiểm theo locale. Verify: Pint, Blade cache và Vite build passed; 86/87 test passed, còn lỗi fixture kiểm kê đã tồn tại (`expected 95`, thực tế `100`).

## [2026-07-19]

### Changed (Đồng bộ màu trang Kho theo brand BlueFire)
- Chuyển nút tạo tồn kho, icon KPI và icon lựa chọn luồng nhập trực tiếp/điều chuyển về màu primary của hệ thống; giữ màu riêng chỉ cho các trạng thái nghiệp vụ và hỗ trợ đồng nhất light/dark mode.

### Changed (Dọn màu các trang nghiệp vụ theo nguyên tắc "màu = mã hóa trạng thái")
- **KPI/stat card icon gộp về xanh brand đồng nhất** trên các trang: Kho (warehouse), Đặt hàng (purchase-orders), Nhà cung cấp, Khu vực/Bếp (areas), Kiểm thực (food-safety), Thực đơn (menus), Nhân viên (employees) — trước đây mỗi thẻ tô 1 màu ngẫu nhiên (xanh/lá/cam/tím). Widget `IngredientStatsOverview` (Nguyên liệu): 3 chỉ số đếm đổi từ success/warning/info về primary.
- **Nút hành động về đúng phân cấp:** nút chính (Tạo tồn kho, Xác nhận nhận hàng) → primary; nút phụ (Nhập kho ngoài, Import/Export món) → gray. Bỏ nút xanh lá/xanh dương info ngẫu nhiên.
- **Giữ nguyên màu MÃ HÓA TRẠNG THÁI:** badge "Hoàn thành/Đang làm việc" (lá), "Sắp hết/Chờ/Đã chốt" (amber), cảnh báo/quá hạn (đỏ), flash message thành công (lá); widget `EmployeeOverview` giữ lá=đang làm/đỏ=nghỉ việc; avatar màu theo tên NV (phân biệt cá nhân). Ca sáng/đêm giữ màu phân loại.
- Verify: build OK, test 84 pass/1 fail fixture, Playwright chụp Đặt hàng + Nhân viên + Thực đơn xác nhận KPI/nút xanh brand đồng nhất, trạng thái vẫn đúng màu.

### Changed (Dọn màu Dashboard + sidebar theo nguyên tắc "màu = mã hóa trạng thái")
- **Sidebar icon về XÁM trung tính** (Slate-500/light, Slate-400/dark), bỏ block gán 5 màu theo nhóm nghiệp vụ (sky/cam/lá/indigo/tím). Menu active giữ nền primary + icon trắng; hover đổi icon sang primary. Bỏ quy tắc "thêm màn mới phải thêm slug thủ công".
- **Dashboard bỏ tô màu ngẫu nhiên:** 4 thẻ KPI (amber/lá/xanh/indigo) và 4 lối tắt (amber/lá/xanh/indigo) gộp về xanh brand đồng nhất; icon header section (Thực đơn/PO/Kiểm thực) và bolt tiêu đề về primary; blob trang trí banner về trắng mờ.
- **Giữ nguyên màu MÃ HÓA TRẠNG THÁI:** cảnh báo tồn kho (đỏ), hồ sơ NCC hết hạn (amber sắp/đỏ quá hạn), badge PO "Hoàn thành" (lá), empty-state kiểm thực (amber chờ). Dọn CSS dead (icon-box màu amber/lá/indigo không còn dùng).
- Nguyên tắc identity: chỉ **xanh dương primary + cam ở logo**. Verify: build OK, test 84 pass/1 fail fixture, Playwright chụp dashboard xác nhận sidebar xám + KPI/lối tắt xanh đồng nhất, trạng thái vẫn đúng màu.

### Changed (Chuẩn hóa màu sắc toàn hệ thống theo brand BlueFire — xử lý phản hồi "màu lộn xộn")
- **Thống nhất màu chủ đạo về `#267DC1` (brand BlueFire, bluefire.vn).** Nguyên nhân lộn xộn: `settings.primary_color` rỗng nên 4 nguồn dùng 4 default khác nhau cùng lúc (`#2563eb` Filament core, `#1256C4` login, `#f59e0b` form settings, `#1267E8` custom). Đồng bộ default ở `AdminPanelProvider`, `SystemSettings`, `login.blade`, và thay toàn bộ họ biến primary trong 10 file style (`--po-bl`/`--sup-bl`/`--bl`): `-d #1F669E` (hover), `-s #E9F2F8` (soft), `-m #A8CBE6` (viền), rgba `38,125,193`.
- **Nền + bo góc nhất quán:** `--po-bg` gộp về `#F8FAFC` (bỏ `#F4F7FB`); radius thống nhất `12px`.
- **Sửa lỗi giao diện:** recipes light thiếu biến `--bg` (nền rỗng) → thêm `#F8FAFC`; badge trạng thái đơn `.os-*` thiếu dark mode (chói trên nền tối) → thêm override alpha; font `Inter` ở 10 file custom → `IBM Plex Sans` cho khớp toàn app (hết lệch chữ ở chat, NCC, báo cáo, chấm công...).
- Tự kiểm chứng: `npm run build` OK; test `84 pass / 1 fail` (fixture cũ, không đổi); Playwright chụp login + dashboard + Đặt hàng — màu BlueFire áp nhất quán (nút/link/sidebar active/badge). Cập nhật sheet **Design System** trong `NV_XUATANKIEUMY.xlsx`.

## [2026-07-16]

### Fixed (Kiểm thực 3 bước — vá rò rỉ dữ liệu chéo bếp + tối ưu tốc độ nhiều menu)
- **🔴 Bảo mật (phân quyền): trang Kiểm thực 3 bước lập hồ sơ chéo bếp.** `ListFoodSafetyAudits::lockedMenus()` chỉ lọc `status='locked'` mà quên khóa theo bếp → người dùng thường thấy và lập hồ sơ pháp lý QĐ 1246 trên món của MỌI bếp. Thêm scope fail-closed theo mẫu `BaoCao`: người dùng thường khóa cứng vào `currentKitchenId()`, chưa gắn bếp thì không thấy thực đơn nào (`whereRaw('1 = 0')`), cấp quản lý xem toàn hệ thống. Thêm 2 test khóa (`NineMenuRound2Test`: khóa theo bếp + fail-closed khi chưa gắn bếp).
- **Tốc độ — sửa 5 điểm nặng nhất:**
  - **N+1 trang Nguyên liệu**: cột "Nhà cung cấp" đọc `$record->suppliers` mỗi dòng nhưng query không eager-load → `getEloquentQuery()` thêm `->with('suppliers')` (bỏ +1 query/dòng).
  - **Thiếu index `timekeepings.date`**: bộ lọc mặc định theo ngày đơn không dùng được composite `(employee_id, date)` → thêm index đơn cho `date` (migration mới).
  - **Trang Nhân viên `stats()` quét toàn bộ cột `documents` JSON mỗi lần re-render** → memoize trong request (`$statsCache`).
  - **Ngân hàng thực đơn chạy 1 query/card** (~10 query/render) → gom 1 query cho cả trang rồi chọn menu đại diện trong PHP (`loadRepresentativeMenus()`).
  - **Trang Nhà cung cấp `stats()`**: gộp 2 count trên bảng `ingredients` thành 1 aggregate + memoize.
- Tự kiểm chứng: `84 passed` (chỉ `NineMenuComplianceTest::chot_kiem_ke` fail sẵn — lỗi fixture: user test không gắn bếp nên `currentKitchenId()` null, không liên quan thay đổi); Pint passed.

### Removed (Gỡ hẳn danh mục gộp `catalogs` — thay hoàn toàn bằng các bảng riêng)
- **Xóa `Catalog` model, `CatalogResource` + Pages, `CatalogPolicy`**: cả 4 nhóm cũ (kitchen_type/department/position/leave_type) đã chuyển sang bảng riêng và không còn mã nào đọc `catalogs`. Bỏ luôn route ẩn `/admin/catalogs` (hết nguy cơ 500 khi mở thẳng URL sau khi bảng bị bỏ).
- **Thêm migration `drop_catalogs_table`** (idempotent, chạy sau migration tách 3 bảng nhân sự để bước seed vẫn đọc được `catalogs`): deploy sạch sẽ tạo rồi bỏ `catalogs` gọn gàng; prod đã lỡ mất bảng vẫn chạy an toàn nhờ `dropIfExists`.
- Migration `create_catalogs_table` gỡ `use App\Models\Catalog` và thay hằng `Catalog::*` bằng literal string để không phụ thuộc model đã xóa (migration là snapshot độc lập). Test `CatalogAndPurchaseSuggestionTest` chuyển sang kiểm `KitchenType`/`Department`/`Position`/`LeaveType::options()` thay cho `Catalog::options()`.

### Changed (Phòng ban / Chức danh / Loại nghỉ phép–tăng ca — tách từ `catalogs` sang bảng riêng)
- **Thêm 3 bảng `departments`, `positions`, `leave_types` + 3 resource quản trị riêng** (nhóm "NHÂN SỰ"): CRUD chuẩn Filament, có `sort`/`active` (riêng `leave_types` có `is_ot` phân biệt nghỉ phép/tăng ca), chặn xóa mục đang được dùng (cả xóa đơn/hàng loạt và nút Xóa trang Edit). `employees.department`/`position` (chuỗi) → FK `department_id`/`position_id`; `leave_overtimes.type` → FK `leave_type_id`. Migration gom nốt giá trị chuỗi thực tế đang có vào bảng mới trước khi drop cột (không mất dữ liệu).
- **Giữ tương thích ngược**: `Employee` có mutator ghi `department`/`position` (ánh xạ chuỗi → FK, `firstOrCreate` nếu chưa có); `LeaveOvertime` có accessor đọc + mutator ghi `type` (tự đặt `is_ot` theo tên) — seeder/test/export cũ vẫn chạy đúng. Cập nhật mọi nơi đọc chuỗi cũ (`$emp->department` → `?->department?->name`) ở bảng chấm công, thẻ điểm danh, export CSV; thêm eager-load `employee.department` cho export chấm công (tránh N+1).
- **Form/bộ lọc chỉ hiện mục đang bật** (`active = true`, theo `sort`) đồng bộ giữa Resource Filament, bộ lọc bảng và các dropdown ở trang custom (nhân viên, nghỉ phép/tăng ca, chấm công). Thay các query inline trong Blade bằng helper `Model::options()` (`Department`/`Position`/`LeaveType`, biến thể `LeaveType::options(?bool $isOt)`).
- **Giữ nguyên bảng `catalogs` và dữ liệu 3 nhóm** để `CatalogResource` và mã cũ vẫn chạy — 3 nhóm nay quản song song ở cả bảng riêng lẫn `catalogs`. Migration dùng Query Builder (không raw SQL nháy kép) để chạy được cả MySQL lẫn SQLite. Sinh policy/permission Shield cho 3 resource mới.
- Tự kiểm chứng: `82 passed` (chỉ còn `NineMenuComplianceTest::chot_kiem_ke` fail sẵn từ trước, không liên quan); Pint passed.

### Changed (Loại bếp / nhà ăn — tách từ danh mục `catalogs` sang bảng riêng `kitchen_types`)
- **Thêm bảng `kitchen_types` + resource quản trị riêng** (`/admin/kitchen-types`, nhóm "KHU VỰC & NHÀ ĂN"): CRUD chuẩn Filament, có `sort`/`active`, chặn xóa loại đang được nhà ăn/bếp sử dụng (cả xóa đơn lẫn hàng loạt và nút Xóa ở trang Edit). `kitchens.type` (chuỗi) chuyển thành FK `kitchens.kitchen_type_id`; migration gom nốt các giá trị `type` thực tế đang có vào bảng mới trước khi drop cột (không mất dữ liệu).
- **Form/bộ lọc chọn loại chỉ hiện loại đang bật** (`active = true`, theo `sort`) — đồng bộ giữa `KitchenResource`, filter bảng và dropdown lọc ở trang danh sách custom; loại đã tắt không còn chọn được, đúng như mô tả nút bật/tắt.
- **Giữ tương thích ngược `$kitchen->type`**: thêm accessor/mutator trên `Kitchen` (ghi chuỗi `type` tự ánh xạ sang `kitchen_type_id`, `firstOrCreate` loại nếu chưa có) — giống pattern của `Ingredient`/`Recipe`, nên seeder/test/export cũ vẫn chạy đúng. Gỡ nhóm `kitchen_type` khỏi `Catalog` (`GROUP_LABELS`, hằng số) và dọn dữ liệu `catalogs` tương ứng.
- Trang danh sách nhà ăn/bếp: cột "Loại" và các cột quan hệ (khu vực, phụ trách) **để trống khi chưa có dữ liệu** thay vì hiển thị "—". Sinh policy/permission Shield cho resource mới (`kitchen::type`).
- Tự kiểm chứng: `CatalogAndPurchaseSuggestionTest` + `SuperAdminPermissionsTest` passed; Pint passed.

### Changed (Ca làm việc / Nhóm món / Đơn vị tính / Loại thực phẩm — thêm/sửa chuyển từ modal sang trang riêng)
- **4 resource danh mục (`shifts`, `recipe-types`, `units`, `ingredient-types`) chuyển từ kiểu `ManageRecords` (create/sửa bằng modal) sang trang riêng** `List`/`Create`/`Edit` chuẩn Filament — thêm/sửa nay mở sang trang `/create` và `/{id}/edit` (đồng bộ với Nguyên liệu, Khu vực, Nhà ăn/bếp). Nút "Tạo mới" ở header và nút Sửa trên bảng tự điều hướng sang trang tương ứng; xong thì quay về danh sách.
- **Giữ nguyên guard chặn xóa khi danh mục đang được dùng** và **nhân bản guard đó lên nút Xóa của trang Edit** để không tạo lối xóa vòng qua kiểm tra: Đơn vị/Loại thực phẩm chặn khi còn `ingredients()`, Nhóm món chặn khi còn `recipes()` (Ca làm việc không có ràng buộc). Giữ các title tuỳ biến (`Nhóm món`, `Đơn vị tính`, `Loại thực phẩm`) trên trang danh sách.
- Xóa 4 page-class `Manage*` không còn dùng.
- Tự kiểm chứng (`Livewire::test`): mount OK cả 12 trang (list/create/edit × 4); guard trên trang Edit chặn đúng bản ghi đang dùng (Unit/IngredientType/RecipeType còn nguyên sau khi gọi xóa) và cho xóa bản ghi không dùng; 12 route đăng ký đủ, `view:cache` compile sạch, Pint passed.

### Changed (Khu vực & Nhà ăn/bếp — thêm/sửa chuyển sang trang riêng chuẩn Filament như trang Nguyên liệu)
- **Thêm/sửa nay mở sang trang riêng** `/{areas,kitchens}/create` và `/{...}/{id}/edit` dùng **form Filament chuẩn** (giống `/admin/ingredients/create`), thay cho form inline/modal trước đó. Trang danh sách custom (KPI + bảng + tìm kiếm/lọc) giữ nguyên; nút **"Thêm khu vực" / "Thêm nhà ăn / bếp"** ở header và nút **bút chì** mỗi dòng nay là link `wire:navigate` tới trang create/edit; nút **xóa** vẫn xử lý tại danh sách (có xác nhận; khu vực còn nhà ăn trực thuộc thì chặn xóa).
- Rút gọn 2 page class danh sách: bỏ toàn bộ state/method của form inline & modal, chỉ còn danh sách + lọc + xóa. Trường **"Quản lý" đổi thành không bắt buộc** ở cả `AreaResource`/`KitchenResource` (trước để `required()` sẽ chặn sửa các bản ghi cũ đang bỏ trống quản lý).
- Tự kiểm chứng: `Livewire::test` mount OK cả 6 trang (list/create/edit của areas & kitchens); tạo mới Area **và** Kitchen qua trang chuẩn lưu thành công với quản lý để trống (NULL); link create/edit render đúng trong bảng; `view:cache` compile sạch, 6 route nguyên vẹn, Pint passed. (Cảnh báo DEPRECATED khi test là của Filament vendor trên PHP 8.2, không phải mã dự án.)

### Changed (Khu vực & Nhà ăn/bếp — đồng bộ form theo phong cách trang Nguyên liệu)
- **Làm lại phần form của 2 trang cho giống form trang Nguyên liệu (`/admin/ingredients`)** — chỉ đổi form, **giữ nguyên luồng CRUD, validation, vị trí form/bảng**. Thêm bộ style `.ff-*` (Section có tiêu đề + phụ đề, lưới 2 cột, hàng nút căn phải có đường kẻ trên) và tinh chỉnh `.ctrl`/`.field` sang kiểu input Filament (bo góc 8px, viền dạng ring + đổ bóng nhẹ, focus ring primary, select mũi tên tuỳ biến) — có bản dark. Style nằm trong `partials/styles.blade.php` dùng chung nên chỉ tác động 2 trang này.
  - **Khu vực**: form ở card trái chuyển thành `ff-section` (tiêu đề "Thông tin khu vực" + phụ đề), input/label/nút kiểu Filament; giữ bố cục trái-form / phải-bảng.
  - **Nhà ăn / bếp**: gỡ dải form 6 cột chật, thay bằng `ff-section` **lưới 2 cột** (khu vực, tên, phân loại, công suất, quản lý, trạng thái) + hàng nút Lưu/Hủy — khớp bố cục `columns(2)` của form Nguyên liệu; phần lọc + bảng tách xuống card riêng bên dưới.
- Tự kiểm chứng: div cân bằng (areas 50/50, kitchens 49/49), `view:cache` compile sạch toàn bộ blade, 6 route areas/kitchens nguyên vẹn, `Livewire::test` mount cả 2 trang OK dưới quyền super_admin (markup `ff-section` render đúng, không lỗi).

### Changed (Menu điều hướng — tách "Khu vực & Nhà ăn" thành 1 nhóm menu chính với 2 mục con)
- **Đưa menu "Khu vực" ra khỏi nhóm "CHAT NHÓM"** (trước đây đặt sai chỗ) và tạo **nhóm menu chính mới "KHU VỰC & NHÀ ĂN"** (chèn ngay sau "TỔNG QUAN") gồm **2 mục con**: **Khu vực** và **Nhà ăn / bếp**.
- **Tách trang gộp 2-tab cũ thành 2 trang riêng**, giữ nguyên giao diện custom sẵn có (KPI, form CRUD, bảng, tìm kiếm/lọc, phân trang, theming `--po-*`):
  - **Khu vực** (`AreaResource` / `ListAreas`): chỉ quản lý khu vực — bỏ tab và pane bếp; thống kê xoay quanh khu vực.
  - **Nhà ăn / bếp** (`KitchenResource` / `ListKitchens`): bật lại navigation (trước bị ẩn), thay trang danh sách chuẩn của Filament bằng **trang Livewire custom** dùng lại toàn bộ form inline + bộ lọc (khu vực/loại/trạng thái) + bảng của tab bếp cũ; view dùng chung `partials/styles.blade.php` của Khu vực.
- Icon sidebar 2 slug `/areas` `/kitchens` đã có sẵn màu trong block CSS của panel; `KitchenPolicy` đã tồn tại nên test policy vẫn đạt. Hai resource này vốn chỉ mở cho super_admin (đi tắt qua Gate) — hành vi giữ nguyên.

### Changed (Kiểm thực 3 bước — tăng tốc xuất Excel 8 lần: 11,7s → 1,5s; xuất lại ~50ms)
- **Đo thực tế nút "Xuất Excel" mất 11,7 giây** (không phải ~0,5s như ghi nhận trước — số cũ đo thiếu). Profiling từng đoạn chỉ ra 97% thời gian nằm ở `applyExportStyling`: PhpSpreadsheet 1.x coi **MỖI setter style lẻ** (`setName()` → `setSize()` → `setARGB()`…) là **một lượt quét toàn range**, và mỗi biến thể style phải **linear-scan + tính lại md5 hash toàn bộ cellXf collection** — file mẫu vốn nhiều style nên chi phí phình theo cấp số (range lớn nhất trả giá 3–7 lần cho cùng một việc).
- **Sửa: gộp mọi cụm setter lẻ thành MỘT `applyFromArray` duy nhất mỗi range** (font toàn bảng, header, body, dòng nhóm, banded rows, footer, meta, `styleCell`) — ngữ nghĩa merge giữ nguyên nên file xuất **không đổi một byte logic nào**: verify lại đủ 5 sheet B1–B5, font [10,11,13,16]pt, 0 ô >16pt, 0 màu #333333, zoom 100%, con trỏ A1, freeze đúng, B1 đủ 16 dòng dữ liệu. `applyExportStyling` B1: 4.747ms → 434ms (~11×).
- **Thêm cache bytes file .xlsx theo hash dữ liệu** (`exportBytes()`, cùng pattern cache HTML của `render()`, TTL 10 phút): bấm xuất lại cùng ngày/ca khi dữ liệu kiểm thực chưa đổi trả file ngay từ cache (~1ms) thay vì dựng lại workbook; dữ liệu đổi ⇒ hash đổi ⇒ tự dựng mới. Lưu **base64** vì cache driver là bảng MySQL `cache` (cột text không chứa được bytes nhị phân). Kết quả: lần xuất đầu ~1,5s, các lần sau ~50ms (240× so với trước). Test `FoodSafetyAuditItemsTest` + `NineMenuRound2Test`: 7 passed.

### Fixed (Kiểm thực 3 bước — file Excel xuất mở ra chữ khổng lồ + bảng tí xíu)
- **Chữ header khổng lồ (36pt) dù đã set 11pt**: các ô header của file mẫu là **RichText** (nhiều "run" chữ, mỗi run tự giữ font 36pt màu #333333) — set font ở cấp Ô không thắng được font từng run, writer vẫn render 36pt. Nay **làm phẳng RichText về text thường** cho toàn bộ ô trước khi set font ⇒ font 11pt của ô có hiệu lực, đồng thời xoá sạch màu #333333 (đúng yêu cầu #1). Kiểm chứng: 0 ô >16pt, 0 màu #333333 trên cả 5 sheet.
- **Bảng mở ra tí xíu ở góc**: file mẫu lưu **zoom 17–49%** khiến bảng bé xíu khi mở. Nay ép **zoom 100%** mọi sheet.
- **File mở ở cuối bảng**: ô chọn nằm ở dòng cuối (C29…). Nay chốt **con trỏ về A1** cho mọi sheet sau khi gộp workbook + active sheet = B1 ⇒ mở ra thấy đầu bảng.
- **B1 dư ~22 dòng trống** (highestRow 51 trong khi data hết ở 29) làm bảng loãng — nay cắt sạch dòng trống dưới footer; đặt chiều cao dòng gọn (header 34, dữ liệu 20). Verify end-to-end qua server đang chạy: font [10,11,13,16]pt, zoom 100, sel A1, freeze đúng.

### Changed (Kiểm thực 3 bước — chuẩn hoá định dạng file Excel xuất, 11 mục theo yêu cầu BA)
- Thêm bước `applyExportStyling()` áp riêng cho **file .xlsx xuất** (không đụng giao diện trên trang — giao diện giữ nguyên cỡ chữ lớn), nhận diện mốc dòng động bằng cách quét nội dung nên đúng dù số món thay đổi:
  1. **Font**: toàn workbook Times New Roman, chữ đen #000000; tên công ty 16pt đậm nghiêng, tiêu đề "BƯỚC…" 13pt đậm, header bảng 11pt đậm wrap, body 11pt thường, ghi chú/chữ ký 10pt nghiêng.
  2. **Gỡ merge 1×1** vô nghĩa (0 ô còn sót cả 5 sheet).
  3. **Kiểu dữ liệu**: cột chứng từ B1 (H) ép text `@`; cột khối lượng B1 (D) **tách "kg" lên header → giá trị số thực + numFmt `#,##0.00`** (đơn vị khác kg giữ nguyên text); giờ B3 (E) ép text cho đồng bộ.
  4. **Màu section đồng nhất** #F1CEEE cho cả 5 sheet (B3 trước nền xanh #45B0E1 → hồng).
  5. **Freeze panes** giữ meta + header đứng yên (B1 A7, B2/B3 A9/A8, B4/B5 A7).
  6. **Table-style trực quan** (viền + banded rows xen kẽ) — KHÔNG dùng ListObject thật để không phá dòng phân nhóm I/II/III và header gộp của biểu mẫu QĐ 1246.
  7. **Alignment** theo cột: STT/trạng thái/giờ căn giữa, tên/mô tả căn trái, số lượng căn phải, toàn body vertical center + wrap.
  8. **Viền** thin đen đủ 4 cạnh toàn vùng header + body.
  9. Bỏ khoảng trắng thừa trước "- K : Không Đạt" ở footer B4/B5.
  10. **Độ rộng cột** thủ công theo ý nghĩa + auto chiều cao dòng.
  11. Theo xác nhận user: **giữ cả 2 sheet B4/B5** (2 biểu mẫu Lưu/Hủy mẫu, trùng do demo 1 ca); **Table-style trực quan** thay ListObject; **giữ nguyên hậu tố "123"** trong tên công ty. Writer tắt `preCalculateFormulas` (suspend auto-calc). Xuất 5 sheet ~0,5s (sau lần build cache mẫu đầu). Verify từng mục trên file thật đạt.

### Changed (Kiểm thực 3 bước — xuất Excel dùng CHÍNH pipeline render trên trang)
- **Nút "Xuất Excel" nay tải về file .xlsx 5 sheet B1–B5 khớp 100% biểu mẫu đang hiển thị** (format Excel mẫu QĐ 1246 + dữ liệu thật từng bước) — trước đây xuất qua `FoodSafetyStepSheet` dựng cột thủ công nên format lệch với màn hình. Renderer tách `prepareSheet()` dùng chung cho 2 đầu ra (HTML trang + file xuất) và thêm `exportWorkbook()` gộp 5 sheet; sửa 1 chỗ là cả trang lẫn file xuất cùng đổi, không còn lệch nhau. Xuất trọn 5 sheet ~0,5 giây. Lớp export cũ `FoodSafetyAuditReportExport`/`FoodSafetyStepSheet` không còn được dùng.

## [2026-07-15]

### Fixed (Kiểm thực 3 bước — rà số liệu 5 tab: bỏ hardcode, chỉ dùng dữ liệu thật, cache v23)
- **Bỏ toàn bộ giá trị điền sẵn/bịa trên biểu mẫu pháp lý**: B2 không còn mặc định "Đạt" cho 3 cột điều kiện vệ sinh — Đ/K lấy theo bản ghi kiểm thực THẬT (`Đạt`/`K`, chưa kiểm thì để trống); B1 cột cảm quan/thú y/kiểm dịch chỉ ghi khi nguyên liệu có PO đã nhập kho thật; Lưu mẫu/Hủy mẫu bỏ fallback "Hũ Inox"/"2-8°C"/"Đ"/tên người kiểm đang chọn — dụng cụ, nhiệt độ, người lưu, ghi chú lấy nguyên từ bản ghi lưu mẫu; B3 bỏ "giờ bắt đầu ăn = giờ lưu mẫu + 30 phút" tự chế (DB chưa có trường, để trống chờ ghi nhận).
- **Cột người kiểm/người lưu chứa ID nhân viên in thẳng số lên biểu mẫu**: `inspected_by`/`sample_kept_by` là varchar chứa lẫn ID (dữ liệu cũ) và tên — nay resolve ID → tên nhân viên thật (1 query gộp, không N+1).
- **Tên người lưu mẫu THẬT bị hàm thay-chữ-ký-mẫu ghi đè**: `replaceSampleSignatures` quét toàn sheet sau khi đổ dữ liệu nên đè cả ô "Người lưu mẫu" trùng tên người trong file mẫu — nay chạy TRƯỚC khi đổ dữ liệu nghiệp vụ.
- **B1 xếp nhóm sai hàng loạt do danh mục bẩn**: nguyên liệu demo mang loại "CÁ"/"BÒ" gán bậy (Nấm đông cô=CÁ, Hành tây/Sả/Cà rốt/Khổ qua=BÒ…) làm rau củ rơi vào nhóm I — đã sửa loại cho 8 nguyên liệu trong thực đơn demo (⚠️ còn ~110 nguyên liệu khác toàn hệ mang loại CÁ/BÒ đáng ngờ, cần rà ở màn Danh sách nguyên liệu). Cột "Giấy thú y/kiểm dịch" nay xét theo NHÓM I của biểu mẫu (bắt cả cá/gà/thịt) thay vì so cứng loại "Động vật".
- **Cột "Ca/bữa ăn" chỉ hiện ở dòng đầu** (ô gộp dọc của file mẫu nuốt các dòng sau) — nay gỡ merge vùng dữ liệu trước khi đổ, ca hiển thị đủ trên từng dòng ở B2/B3/Lưu mẫu/Hủy mẫu.
- Kiểm chứng trên trình duyệt thật (Playwright, ngày demo 18/05 ca 1): 16 nguyên liệu B1 khớp PO `DEMO-KT3B-PO-22..26`, giờ nấu 07:00–09:30, giờ lưu/hủy mẫu 10:30 → 10:30 (19/05), mã mẫu `LM-20260518-xx`, người lưu "Nguyễn Thị Ánh Ngọc" đều đúng bản ghi DB. Test `FoodSafetyAuditItemsTest` + `NineMenuRound2Test`: 7 passed.

### Changed (Kiểm thực 3 bước — đồng nhất khối meta B2→B5 theo chuẩn B1, cache v18)
- Trước đây mỗi tab một kiểu bố cục phần đầu (B2: tiêu đề trôi giữa, company tĩnh chen bên trái, meta nằm dưới company kèm dải ô trống bên phải; B3/B4/B5 mỗi sheet đặt meta một chỗ). Nay cả 4 tab dựng lại theo ĐÚNG khung B1: **4 dòng meta trái trên cùng** (Tên cơ sở / Thời gian kiểm tra / Địa điểm kiểm tra / Người kiểm tra, merge A:D), **company + địa chỉ góc phải** (ngang dòng 2–3), **tiêu đề biểu mẫu + "Ban hành: QĐ 1246/2017-BYT" một dải riêng** ngay trên header bảng. Mọi text cũ trong vùng meta bị dọn sạch trước khi ghi lại — hết chữ lộn xộn.

### Changed (Kiểm thực 3 bước — tối ưu hiệu năng render, đo trước/sau)
- **Render biểu mẫu từ Excel nhanh hơn ~126–966 lần khi cache miss**: file mẫu khai báo 1000 dòng × 33 cột trong khi nội dung thật chỉ ~65 dòng × 13 cột — mỗi lần render lạnh phải load toàn bộ rồi xóa ~940 dòng thừa qua ReferenceHelper (quét ~30k ô cho MỖI thao tác xóa/chèn) tốn **7,6–20,8 giây/tab**. Nay cắt phần thừa **một lần** và lưu bản mẫu nhỏ (11–15KB, giữ nguyên style/độ rộng cột/chiều cao dòng) vào `storage/framework/cache/fsa-template-<sheet>-<mtime>.xlsx`; các render sau chỉ load bản nhỏ: **21–81ms/tab**. File mẫu gốc đổi → mtime mới → tự build lại; build lỗi thì tự dùng file gốc (chậm nhưng đúng).
- **Bỏ 2/3 số query trùng mỗi request**: blade gọi `getSheetView()` rồi `getExcelTemplateHtml()` (gọi lại `getSheetView()`), cộng `getStats()` — cụm query menus→recipes→ingredients chạy 3 lần/request (DB đặt ở server remote nên mỗi query thừa là một round-trip mạng). Nay memo `lockedMenus()` + `getAuditItems()` theo request: 27→12 query (B1), 21→7 (các tab khác).
- Sửa kèm 2 lỗi PhpSpreadsheet lộ ra khi tối ưu: `removeColumn` trên vùng không còn cột vẫn sinh dải ô rỗng ở cột cuối (M) tới `cachedHighestRow`; `getHighestRow()` cached chỉ tăng không giảm làm `trimSheet` sót row dimension → dòng trắng ma cuối bảng. `trimSheet` nay tính biên thật từ cells + row dimensions và chỉ `removeColumn` khi còn cột thừa.
- **Đo lường** (cùng dữ liệu, cache HTML tắt): tải trang trọn vẹn 5,7–8,3 giây → **36–192ms** (lần đầu tiên sau deploy/đổi file mẫu: 3,4–5 giây/tab để build bản trim, chỉ một lần). Output HTML xác minh **giống hệt từng dòng** trước/sau tối ưu trên cả 8 kịch bản (5 tab × đủ/trống/tràn dòng); `FoodSafetyAuditItemsTest` + `NineMenuRound2Test` 7 passed (30 assertions).

### Fixed (Kiểm thực 3 bước — rà 5 tab B1–B5 render theo Excel mẫu, cache v17)
- **B2/B3 lộ dòng sample của file Excel mẫu**: khi số món ≤ 9, thuật toán xóa dòng thừa xóa theo khối liên tục trong khi vùng dữ liệu của file mẫu có kẽ hở (dòng 19 ngăn 2 khối ca trưa/ca chiều) → dòng mẫu tĩnh "`=ROW()-19 | Cơm | gạo 55kg | 280…`" hiện ngay dưới dữ liệu thật. Nay bỏ dòng kẽ hở trước khi đổ dữ liệu để dải dòng liên tục.
- **Ngày không có thực đơn chốt làm VỠ HEADER biểu mẫu**: `range(9, 8)` trong PHP chạy ngược → xóa nhầm cả dòng tiêu đề cột ("Người tham gia chế biến" ở B2, dòng header B4/B5). Nay ngày trống render biểu mẫu nguyên vẹn với 1 dòng dữ liệu trống.
- **Tab Bước 3 mất tiêu đề "BƯỚC 3: KIỂM TRA TRƯỚC KHI ĂN"**: ô gộp company (C1:H2) nuốt luôn ô C2 chứa tiêu đề — nay company chỉ gộp dòng 1, tiêu đề gộp riêng dòng 2.
- **Tab Bước 2 lộ tên công ty TĨNH của file mẫu** ("CHI NHÁNH-CÔNG TY TNHH DỊCH VỤ CJ CATERING… Tổ 15, ấp 2…") hiển thị song song với company động từ Cài đặt — đã xóa ô D1 của mẫu.
- **Chữ ký mẫu tĩnh ở cả 5 tab** ("NGUYỄN THỊ ÁNH NGỌC", "NGUYỄN THỊ XUÂN TIÊN" — người thật trong file mẫu): nay ô người kiểm tra thay bằng **Người kiểm tra đang chọn trên trang**, ô người giám sát/đại diện công ty để trống chờ ký tay.
- **B1 RỚT nguyên liệu không khớp nhóm** (vd. loại "Khác"): nguyên liệu không nhận diện được nhóm I/II/III/VI trước đây **biến mất khỏi biểu mẫu** (sai hồ sơ pháp lý QĐ 1246) — nay dồn vào nhóm III thay vì rớt.
- **B1 xếp nhầm nhóm "Trứng gà"** vào nhóm I (thịt/cá/gà) vì chuỗi "gà" khớp trước "trứng" — nay xét "trứng" trước.
- **B1 mất số 0 đầu số điện thoại NCC** tùy dòng (0900000005 → 900000005): ô mẫu format General làm HTML writer ép chuỗi số thành số — nay ép format TEXT cho cột SĐT.
- Toàn bộ sửa trong `app/Support/FoodSafetyExcelTemplateRenderer.php`, cache render nâng `v16 → v17`. Test `FoodSafetyAuditItemsTest` 2 passed (13 assertions); render thử cả 8 kịch bản (5 tab × đủ/trống/tràn dòng) không còn leak, không mất món, header nguyên vẹn.

## [2026-07-14]

### Added (đợt cuối — đóng nốt 3 điểm còn lại của 9 menu)
- **Danh mục cấu hình động** (menu mới, nhóm HỆ THỐNG — bảng `catalogs`): **Loại bếp / Phòng ban / Chức danh–chức vụ / Loại nghỉ phép–tăng ca** nay sửa được ngay trên hệ thống có phân quyền, không còn hard-code trong mã nguồn (đúng BA R37 — khách đổi danh mục không phải deploy lại). Migration đã seed toàn bộ giá trị đang dùng **trong dữ liệu thật** nên không mất lựa chọn nào. Chức vụ nhân viên chuyển từ ô nhập tự do sang chọn từ danh mục. Trạng thái & quy trình cố định (trạng thái PO, kiểm thực, loại giao dịch kho, trạng thái nhân viên) **giữ hard-code** theo đúng yêu cầu BA.
- **Đặt hàng — số lượng đề xuất mua nay TRỪ TỒN KHO** khả dụng của bếp (trừ cả lượng đang đóng băng cho phiếu điều chuyển), bảng tạo PO thêm cột **Tồn kho** để người mua thấy vì sao đề xuất giảm — hết cảnh đặt thừa hàng đang có sẵn trong kho.
- **Ẩn màn "Lập thực đơn tuần" bản cũ** (dạng repeater, đặt nút Thêm món trên toolbar chung — trái BA R33) khỏi menu; chỉ còn **một luồng duy nhất** là grid T2→CN có nút (+) từng ô ngày/ca. Route vẫn giữ để không vỡ link cũ.
- Test: `CatalogAndPurchaseSuggestionTest` (3 test). Toàn suite: **71 passed (257 assertions)**.

### Fixed (rà soát 9 menu theo file nghiệp vụ BA — đóng toàn bộ khoảng trống)
- **Báo cáo — SAI SỐ LIỆU nghiêm trọng**: tổng khối lượng nguyên liệu bị **chia nhầm 1000 lần** (500 kg thịt hiển thị thành 0,5 kg) → đã sửa; lỗi tương tự ở màn **Kiểm thực Bước 1** cũng đã sửa.
- **Kho — Kiểm kê cuối ngày chưa đúng luồng BA**: ô "Ngày kiểm kho" trước đây **không ảnh hưởng số liệu** (luôn lấy tồn hôm nay) và **không có khái niệm tồn đầu kỳ**. Nay: tồn hệ thống **tính theo đúng ngày được chọn** (tồn hiện tại trừ các biến động phát sinh sau ngày đó), thêm **cột Tồn đầu kỳ** (= tồn cuối ngày hôm trước), và chốt kiểm kê ghi **điều chỉnh ± kèm mã phiếu `KK-YYYYMMDD`** thay vì ghi đè (kiểm kê ngày quá khứ không còn xóa mất biến động sau đó).
- **Lập thực đơn — mất cảnh báo lặp món**: hàm cảnh báo lặp món 3 tuần chỉ nằm ở trang cũ, **grid tuần đang dùng thật không hề gọi** → nay grid tuần cảnh báo ngay khi chọn món đã chạy trong 21 ngày trước. Tuần gộp/xuất file đủ **7 ngày T2→CN** (trước rơi mất Chủ nhật).

### Changed (theo yêu cầu BA)
- **Nhà cung cấp**: gán nguyên liệu đổi sang **search-select từng nguyên liệu** (gõ tìm → thêm → nhập giá → gỡ), bỏ checkbox trên toàn danh sách giới hạn 100 dòng (nguyên liệu thứ 101 trở đi trước đây không chọn được).
- **Ngân hàng thực đơn**: form món **kiểm soát lãi/lỗ** — so cost thực tế với đơn giá bán, cảnh báo đỏ khi lỗ, cảnh báo cam khi biên lãi < 10%.
- **Báo cáo**: thêm bảng **Tổng khối lượng từng nguyên liệu tiêu thụ cả kỳ** (kèm giá trị, sắp theo chi phí giảm dần) + **bộ lọc theo Bếp**.
- **Kiểm thực**: Bước 1 **gom nguyên liệu theo phân loại** (Động vật/Thực vật/Gia vị…) đúng mẫu QĐ 1246 — trước đây code gom nhóm là code chết không bao giờ chạy; Excel B1 thêm cột Phân loại; tem lưu mẫu in kèm **Ca**; **bỏ nút xuất CSV** (Excel là đầu ra chính).
- **Nhật ký kho**: thêm **lọc theo khoảng thời gian** + nút xem thêm (bỏ giới hạn cứng 50 dòng); giao dịch **Kiểm kê nay có mã phiếu**.
- **Đặt hàng**: nhận **0 (thiếu toàn bộ)** cũng bắt buộc ghi lý do lệch — trước đây chỉ bắt khi số nhận > 0.
- **Lịch sử sửa thực đơn**: ghi vết cả khi **đổi bếp** (trước đây bỏ sót).
- Test: `NineMenuComplianceTest` (5 test). Toàn suite: **68 passed (246 assertions)**.

### Added
- **Menu mới "Quản lý tài khoản" (nhóm NHÂN SỰ)** — mảnh ghép còn thiếu của phân quyền: trước đây đã cài Filament Shield (5 vai trò: super_admin / Quản trị viên / Thủ kho / Bếp trưởng / Nhân viên) nhưng **không có màn hình nào gán vai trò cho tài khoản**, nên 4 vai trò nghiệp vụ đều 0 người dùng. Nay Admin có thể trực tiếp: tạo/sửa tài khoản đăng nhập (mật khẩu để trống khi sửa = giữ nguyên), **liên kết hồ sơ nhân viên** (tài khoản tự nhận Bếp trực thuộc để lọc dữ liệu Kho/Thực đơn/Đặt hàng — hiển thị xem trước ngay trên form), và **gán vai trò phân quyền**. Bảng danh sách: STT / Họ tên / Email / Nhân viên liên kết / Bếp trực thuộc / Vai trò (badge) / Ngày tạo, kèm bộ lọc theo vai trò và theo "đã/chưa liên kết nhân viên".
- **Các lớp bảo vệ đi kèm (bắt buộc trước go-live)**: (1) chỉ tài khoản toàn quyền mới gán được vai trò `super_admin` — chặn tự nâng quyền; (2) không ai được tự sửa vai trò của chính mình; (3) không thể gỡ vai trò/xóa **tài khoản toàn quyền cuối cùng** và không thể tự xóa chính mình — chặn khóa vĩnh viễn hệ thống; (4) **1 nhân viên chỉ gắn 1 tài khoản** (thêm unique index `users.employee_id`, trước đây chỉ có khóa ngoại nên 2 tài khoản có thể trùng một nhân sự); (5) quyền quản lý tài khoản chỉ cấp cho vai trò **Quản trị viên**, Thủ kho/Bếp trưởng/Nhân viên không thấy menu này. Test: `UserManagementTest` (7 test) — toàn suite **61 passed (227 assertions)**.

### Changed
- **Vai trò toàn quyền (super_admin) nay giữ quyền THẬT thay vì chỉ đi tắt qua Gate**: trước đây bảng Vai trò hiển thị `super_admin — 0 quyền` (Shield cho vai trò này bỏ qua mọi kiểm tra quyền qua Gate), khiến người dùng khó hiểu và tiềm ẩn rủi ro: chỉ cần tắt `define_via_gate` trong config là admin mất sạch quyền. Bổ sung lệnh **`php artisan shield:sync-super-admin`** cấp toàn bộ quyền hệ thống cho vai trò này (hiện 228 quyền) — chạy lại sau mỗi lần `shield:generate` khi thêm màn hình mới.
- **Tên vai trò toàn quyền lấy từ `config/filament-shield.php`** (`User::superAdminRole()`) thay cho chuỗi cứng `'super_admin'` rải rác trong model/resource/test — đổi tên vai trò chỉ cần sửa 1 chỗ trong config. Test: `SuperAdminPermissionsTest`. Toàn suite: **63 passed (233 assertions)**.

### Fixed
- **Kiểm thử luồng đăng nhập (tài khoản admin@example.com)**: xác minh đủ 4 bước — trang login render đủ thành phần mẫu, sai mật khẩu báo lỗi ngay trên form, đăng nhập thành công có ghi nhớ (remember), đã đăng nhập thì vào thẳng Dashboard không hiện lại trang login. Qua kiểm thử phát hiện và sửa lỗi tiềm ẩn nghiêm trọng: model `User` thiếu `implements FilamentUser` nên rule "phải có vai trò mới vào được trang quản trị" (`canAccessPanel`) chưa từng được thi hành ở máy dev, và khi triển khai môi trường khác `local` (staging/production) **toàn bộ người dùng sẽ bị chặn 403 sau đăng nhập**. Đã bổ sung interface — rule vai trò có hiệu lực thật (3/3 user hiện có đều có role, không ai bị ảnh hưởng).

### Added
- **Trang đăng nhập mới theo mẫu thiết kế BlueFire** (2 cột): panel thương hiệu bên trái (logo + slogan TASTE BEAUTY, cam kết toàn thể CBCNV, băng rôn "NGON TASTE & ĐẸP BEAUTIFUL", vòng tròn 8 múi ảnh món ăn với tâm ISO 22000:2018, 4 giá trị cốt lõi An toàn/Chất lượng/Chuyên nghiệp/Đồng hành, footer bản quyền) + card đăng nhập bên phải (chào mừng, ô Tên đăng nhập/Mật khẩu có icon và nút hiện/ẩn mật khẩu, Ghi nhớ đăng nhập bật sẵn, Quên mật khẩu, nút ĐĂNG NHẬP). Có chọn ngôn ngữ VI/EN góc phải trên (tái dùng language-switcher), logo lấy từ Cài đặt hệ thống, responsive mobile. Luồng xác thực kế thừa nguyên Filament (validate, rate limit, remember, redirect) — chỉ thay giao diện.

## [2026-07-13] — Đợt chuẩn hóa theo BA Phase 1 MISUMI (9 menu)

### Đợt 5 (cùng ngày) — đóng nốt backlog code của 9 menu
- **Kho**: bảng tồn kho thêm cột **"Cập nhật cuối"** (đủ cột theo spec: Tồn / Tối thiểu / Đơn giá / Giá trị / Cập nhật cuối / Trạng thái).
- **Lập thực đơn tuần**: grid theo Thứ giờ có **nút (+) "Thêm món" từng ô ngày/ca** (nhiều món/ô, xóa từng món) thay cho 1 select cố định — đúng spec R33; giữ nguyên guard vòng đời + unique index (bếp, ngày, ca, món) + audit; xóa đúng món bị gỡ. Test: `MenuWeekGridTest`.
- **Tương thích SQLite (theo CLAUDE.md)**: thay 3 hàm chỉ-MySQL trong trang danh sách thực đơn — `YEARWEEK` (đếm tuần hoạt động → tính ISO week trong PHP), `DATE_SUB/WEEKDAY` (thứ 2 đầu tuần → biểu thức theo driver), `DATE_FORMAT` (`substr` cho 'YYYY-MM') — trang giờ chạy được cả trên SQLite; sửa luôn latent bug so-khớp date bằng khoảng nửa mở khi lưu grid tuần.
- Toàn bộ suite: **51 passed (186 assertions)**.


### Added
- **Lập thực đơn**: trạng thái mới **"Khách đã xác nhận" (confirmed)** hoàn thiện vòng đời Nháp → Gửi xác nhận → Khách đã xác nhận → Đã chốt; khóa cứng thực đơn quá khứ đã chốt; bắt buộc nhập **Lý do sửa** khi sửa thực đơn đã chốt (lưu vào `menu_audit_logs.reason`); guard áp dụng đồng nhất trên cả 3 màn (Lập TĐ tuần, Lập thực đơn, trang Edit chuẩn).
- **Nhà cung cấp**: khối **Hồ sơ NCC** (hợp đồng/chứng nhận ATTP, file đính kèm ảnh/PDF ≤5MB, ngày hết hạn); **cảnh báo hồ sơ sắp hết hạn/quá hạn trên Dashboard**; bảng `supplier_price_logs` ghi lịch sử giá cũ → mới mỗi lần đổi đơn giá NCC ↔ nguyên liệu; sửa bug field Ghi chú không có cột DB.
- **Ngân hàng thực đơn**: **Cost điều chỉnh (override)** có bắt buộc lý do + nhật ký `recipe_cost_logs`; món tự về "Chờ rà soát" khi đổi cost; mọi màn (bảng, chi tiết, Excel, Báo cáo) dùng thống nhất cost hiệu lực.
- **Kho**: màn kiểm hàng PO thêm cột **Chênh lệch** (thừa xanh/thiếu đỏ) + **bắt buộc lý do khi lệch** (cột `purchase_order_items.receive_note`, đối chiếu số đặt từ DB); loại giao dịch **"Kiểm kê"** riêng cho kiểm kê cuối ngày.
- **Kiểm thực 3 bước**: nút **In tem nhãn lưu mẫu** (tem 62mm theo ca); Excel 5 sheet bổ sung khối chữ ký "Đại diện nhà ăn / Người kiểm tra".
- **Báo cáo**: ô KPI **Tổng chi phí giá vốn** hiển thị trên giao diện.

### Changed
- **List hàng / Xuất kho sản xuất / Báo cáo** chỉ tổng hợp từ thực đơn **ĐÃ CHỐT** (locked) theo đúng quy trình BA.
- **Nhập kho từ PO**: đơn giá **khóa theo giá đã chốt trên PO**, không nhận giá sửa từ client.
- **Đặt hàng**: ngày đặt giới hạn **tối đa 2 ngày kế tiếp** (form PO + List hàng, chặn cả server-side); giá item PO ưu tiên **bảng báo giá theo từng NCC** (`ingredient_supplier`).
- **Điều chuyển kho**: chỉ cho phép giữa các bếp **cùng khu vực** (lọc UI + chặn server-side + rule form).
- **Grid thực đơn tuần** đủ 7 ngày T2 → Chủ nhật (form, lưu, và xuất Excel tuần).
- Thêm **DB unique index** `menus (kitchen, date, shift, recipe)` kèm dọn dữ liệu trùng (giữ bản trạng thái cao nhất); form ngày/tuần chống vỡ unique khi thêm món trùng.

### Fixed
- Xuất CSV thực đơn hiển thị đúng nhãn tiếng Việt cho trạng thái mới; cảnh báo hồ sơ NCC sắp hết hạn sắp xếp đúng thứ tự thời gian; seeder hiệu năng không sinh menu trùng.

### Đợt 4 (cùng ngày) — hoàn thiện theo 3 file biểu mẫu thực tế
- **Ngân hàng thực đơn**: Import Excel món ăn theo file mẫu ĐỊNH LƯỢNG MÓN ĂN.xlsx (món + định mức gram tự quy đổi kg, nhóm món carry-forward, nguyên liệu mới tự tạo kèm giá, re-import cập nhật định mức; món import về Chờ rà soát). Nút chọn file + nhập ngay trên trang danh sách món.
- **Đặt hàng**: nút "Xuất Excel NCC" xuất file .xlsx theo MẪU ĐƠN ĐẶT HÀNG.xlsx — section theo nhóm nguyên liệu, cột chuẩn, dòng TỔNG CỘNG, khối chữ ký, format tiền tệ.
- **Kiểm thực 3 bước**: 5 sheet Excel dựng lại theo đúng MẪU KIỂM THỰC 3 BƯỚC_ (B1–B5): header cơ sở/thời gian/địa điểm/người kiểm tra + Ban hành QĐ 1246; B1 đủ 13 cột (cơ sở cung cấp, ĐT, người giao, chứng từ theo PO thật, thú y, kiểm dịch); B2 thêm nguyên liệu chính + số suất + giờ sơ chế; B3 thêm số suất + giờ ăn + dụng cụ; B4/B5 đủ 12 cột lưu/hủy mẫu.
- Test: thêm RecipesImportTest (3 test) — toàn suite 41 passed (146 assertions).

### Đợt 3 (cùng ngày) — đóng backlog còn lại
- **Danh sách hàng**: nút Xuất Excel (Ca→Món→Nguyên liệu) + chế độ in có `@media print` riêng (chỉ in vùng danh sách).
- **Đặt hàng**: BỎ trường "Phiếu" trên toàn bộ UI List hàng theo BA; state machine PO chặn lùi trạng thái.
- **Kho**: kiểm kê cuối ngày bắt buộc lý do từng dòng lệch; tab Nhật ký có bộ lọc loại giao dịch + nguyên liệu.
- **Kiểm thực**: người kiểm tra chọn từ danh mục nhân viên, địa điểm tự nhận theo bếp; NCC + chứng từ Bước 1 lấy theo PO đã nhập kho thật (mã PO + giờ nhập).
- **Lập thực đơn**: menu mới "Lịch sử sửa thực đơn" — tra cứu tổng audit log theo người sửa/hành động/khoảng thời gian (read-only).
- **Nguyên liệu/NCC**: import Excel ghi giá vào bảng báo giá NCC + log lịch sử giá (không còn hổng audit khi import); form định mức món hỗ trợ nhập gram tự quy đổi kg.

### Added (đợt trước cùng ngày)
- Preload ẩn các Font Awesome classes tại [sidebar-footer.blade.php](file:///Users/quocnnghi/Documents/PROJECT_CIT_02-07-2026/PM_XUATANKIEUMY/resources/views/filament/components/sidebar-footer.blade.php) để khắc phục triệt để hiện tượng lỗi icon ô vuông khi chuyển trang dạng SPA (Livewire navigation).

### Changed
- Đồng bộ hóa toàn bộ màu sắc giao diện (màu nền, màu chữ, viền, các nhãn trạng thái và hover) của 16 file custom views và stylesheet partials sang hệ thống biến CSS `var(--po-*)`.
- Khắc phục lỗi nền trắng / chữ mờ khi di chuột (hover) trên các bảng danh sách thuộc **Khu vực (Areas)**, **Kiểm thực ATTP (Food Safety Audits)**, **Báo cáo (Reports)**, và **Đơn đặt hàng (Purchase Orders)**.

### Fixed
- Khắc phục lỗi nút bấm "Lưu" bị đóng băng (disabled) vĩnh viễn trên trang **Tạo phiếu điều chuyển (Stock Transfer)** bằng cách chuyển logic kiểm tra tồn kho sang **Form Schema Validation Rules** thay vì kích hoạt `RuntimeException` thủ công trong các hook của Livewire.
