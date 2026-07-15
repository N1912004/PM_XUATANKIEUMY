<?php

namespace App\Filament\Resources\FoodSafetyAuditResource\Pages;

use App\Exports\FoodSafetyAuditReportExport;
use App\Filament\Resources\FoodSafetyAuditResource;
use App\Models\Employee;
use App\Models\FoodSafetyAudit;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\PurchaseOrderItem;
use App\Models\Setting;
use App\Support\FoodSafetyExcelTemplateRenderer;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListFoodSafetyAudits extends ListRecords
{
    protected static string $resource = FoodSafetyAuditResource::class;

    protected static string $view = 'filament.pages.food-safety-audit';

    protected const STEPS = ['Bước 1', 'Bước 2', 'Bước 3', 'Lưu mẫu', 'Hủy mẫu'];

    protected const SHEET_CONFIG = [
        'Bước 1' => [
            'sheet' => 'B1',
            'icon' => 'fa-clipboard-check',
            'title' => 'BƯỚC 1: KIỂM TRA TRƯỚC KHI CHẾ BIẾN THỨC ĂN',
            'subtitle' => 'Kiểm tra nguyên liệu nhập trong ngày theo mẫu B1',
            'columns' => 13,
            'headRows' => [
                [
                    ['text' => 'TT', 'rowspan' => 2, 'width' => '48px'],
                    ['text' => 'Tên thực phẩm', 'rowspan' => 2, 'width' => '210px'],
                    ['text' => 'Thời gian nhập (giờ)', 'rowspan' => 2, 'width' => '100px'],
                    ['text' => 'Khối lượng (kg/lít...)', 'rowspan' => 2, 'width' => '110px'],
                    ['text' => 'Nơi cung cấp thực phẩm, gia vị các loại', 'colspan' => 3],
                    ['text' => 'Chứng từ, hóa đơn', 'rowspan' => 2, 'width' => '130px'],
                    ['text' => 'Giấy ĐK VS thú y', 'rowspan' => 2, 'width' => '100px'],
                    ['text' => 'Giấy kiểm dịch (trong tỉnh)', 'rowspan' => 2, 'width' => '105px'],
                    ['text' => 'Kiểm tra cảm quan (Đ/K)', 'rowspan' => 2, 'width' => '130px'],
                    ['text' => 'Xét nghiệm nhanh (Đ/K)', 'rowspan' => 2, 'width' => '105px'],
                    ['text' => 'Biện pháp xử lý (ghi chú)', 'rowspan' => 2, 'width' => '150px'],
                ],
                [
                    ['text' => 'Tên cơ sở cung cấp', 'width' => '180px'],
                    ['text' => 'Địa chỉ, điện thoại', 'width' => '140px'],
                    ['text' => 'Tên người giao hàng', 'width' => '135px'],
                ],
            ],
            'keys' => ['name', 'time', 'quantity_display', 'supplier', 'supplier_contact', 'deliverer', 'invoice', 'vet_check', 'quarantine', 'sensory', 'quick_test', 'action'],
        ],
        'Bước 2' => [
            'sheet' => 'B2',
            'icon' => 'fa-utensils',
            'title' => 'BƯỚC 2: KIỂM TRA KHI CHẾ BIẾN MÓN ĂN',
            'subtitle' => 'Món ăn, nguyên liệu chính, điều kiện vệ sinh và cảm quan theo mẫu B2',
            'columns' => 12,
            'headRows' => [
                [
                    ['text' => 'TT', 'rowspan' => 2, 'width' => '48px'],
                    ['text' => 'Ca/bữa ăn', 'rowspan' => 2, 'width' => '150px'],
                    ['text' => 'Tên món ăn', 'rowspan' => 2, 'width' => '180px'],
                    ['text' => 'Nguyên liệu chính để chế biến (tên, số lượng...)', 'rowspan' => 2, 'width' => '320px'],
                    ['text' => 'Số lượng/số suất ăn', 'rowspan' => 2, 'width' => '105px'],
                    ['text' => 'Thời gian sơ chế xong', 'rowspan' => 2, 'width' => '115px'],
                    ['text' => 'Thời gian chế biến xong', 'rowspan' => 2, 'width' => '115px'],
                    ['text' => 'Kiểm tra điều kiện vệ sinh', 'colspan' => 3],
                    ['text' => 'Kiểm tra cảm quan thức ăn (Đ/K)', 'rowspan' => 2, 'width' => '130px'],
                    ['text' => 'Biện pháp xử lý (ghi chú)', 'rowspan' => 2, 'width' => '140px'],
                ],
                [
                    ['text' => 'Người tham gia chế biến (Đ/K)', 'width' => '120px'],
                    ['text' => 'Trang thiết bị dụng cụ (Đ/K)', 'width' => '120px'],
                    ['text' => 'Khu vực chế biến và phụ trợ (Đ/K)', 'width' => '135px'],
                ],
            ],
            'keys' => ['shift', 'name', 'main_ingredients', 'portions', 'prep_time', 'finish_time', 'staff_check', 'equipment_check', 'area_check', 'sensory', 'action'],
        ],
        'Bước 3' => [
            'sheet' => 'B3',
            'icon' => 'fa-users',
            'title' => 'BƯỚC 3: KIỂM TRA TRƯỚC KHI ĂN',
            'subtitle' => 'Món ăn, số suất, giờ chia món, giờ ăn, dụng cụ và cảm quan theo mẫu B3',
            'columns' => 9,
            'headRows' => [[
                ['text' => 'TT', 'width' => '48px'],
                ['text' => 'Ca/bữa ăn', 'width' => '150px'],
                ['text' => 'Tên món ăn', 'width' => '220px'],
                ['text' => 'Số lượng suất ăn', 'width' => '110px'],
                ['text' => 'Thời gian chia món ăn xong', 'width' => '130px'],
                ['text' => 'Thời gian bắt đầu ăn', 'width' => '125px'],
                ['text' => 'Dụng cụ (chia, chứa đựng, che đậy, bảo quản)', 'width' => '220px'],
                ['text' => 'Kiểm tra cảm quan món ăn (Đ/K)', 'width' => '150px'],
                ['text' => 'Biện pháp xử lý (ghi chú)', 'width' => '155px'],
            ]],
            'keys' => ['shift', 'name', 'portions', 'time', 'eat_time', 'utensil', 'sensory', 'action'],
        ],
        'Lưu mẫu' => [
            'sheet' => 'B4',
            'icon' => 'fa-box-archive',
            'title' => 'BIỂU MẪU THEO DÕI LƯU VÀ HỦY THỨC ĂN LƯU',
            'subtitle' => 'Sheet B4: theo dõi lưu và hủy mẫu thức ăn lưu ca trưa/chính',
            'columns' => 12,
            'headRows' => [[
                ['text' => 'TT', 'width' => '45px'],
                ['text' => 'Bữa ăn (giờ ăn...)', 'width' => '145px'],
                ['text' => 'Tên món ăn', 'width' => '190px'],
                ['text' => 'Số lượng suất ăn', 'width' => '95px'],
                ['text' => 'Khối lượng/thể tích mẫu (khô: 100g, nước: 150ml)', 'width' => '145px'],
                ['text' => 'Dụng cụ chứa mẫu', 'width' => '115px'],
                ['text' => 'Nhiệt độ bảo quản mẫu (2-8°C)', 'width' => '110px'],
                ['text' => 'Thời gian lấy mẫu', 'width' => '135px'],
                ['text' => 'Thời gian hủy mẫu', 'width' => '135px'],
                ['text' => 'Ghi chú chất lượng mẫu (Đ/K)', 'width' => '140px'],
                ['text' => 'Người lưu mẫu', 'width' => '145px'],
                ['text' => 'Người hủy mẫu', 'width' => '145px'],
            ]],
            'keys' => ['shift', 'name', 'portions', 'sample_amount', 'container', 'temp', 'time', 'destroy_at', 'notes', 'staff', 'destroyer'],
        ],
        'Hủy mẫu' => [
            'sheet' => 'B5',
            'icon' => 'fa-ban',
            'title' => 'BIỂU MẪU THEO DÕI LƯU VÀ HỦY THỨC ĂN LƯU',
            'subtitle' => 'Sheet B5: theo dõi lưu và hủy mẫu thức ăn lưu ca chiều/còn lại',
            'columns' => 12,
            'headRows' => [[
                ['text' => 'TT', 'width' => '45px'],
                ['text' => 'Bữa ăn (giờ ăn...)', 'width' => '145px'],
                ['text' => 'Tên món ăn', 'width' => '190px'],
                ['text' => 'Số lượng suất ăn', 'width' => '95px'],
                ['text' => 'Khối lượng/thể tích mẫu (khô: 100g, nước: 150ml)', 'width' => '145px'],
                ['text' => 'Dụng cụ chứa mẫu', 'width' => '115px'],
                ['text' => 'Nhiệt độ bảo quản mẫu (2-8°C)', 'width' => '110px'],
                ['text' => 'Thời gian lấy mẫu', 'width' => '135px'],
                ['text' => 'Thời gian hủy mẫu', 'width' => '135px'],
                ['text' => 'Ghi chú chất lượng mẫu (Đ/K)', 'width' => '140px'],
                ['text' => 'Người lưu mẫu', 'width' => '145px'],
                ['text' => 'Người hủy mẫu', 'width' => '145px'],
            ]],
            'keys' => ['shift', 'name', 'portions', 'sample_amount', 'container', 'temp', 'kept_at', 'time', 'notes', 'keeper', 'staff'],
        ],
    ];

    public ?string $date = '2026-05-18'; // Default date from seeder for easy review

    public ?int $selectedShift = 1; // Default CA 1

    public string $activeStep = 'Bước 1';

    public string $canteen = '';

    public string $inspector = '';

    public function mount(): void
    {
        parent::mount();

        // Địa điểm tự nhận theo bếp của tài khoản đang đăng nhập (BA: không nhập tay)
        $kitchenId = auth()->user()?->currentKitchenId();
        $this->canteen = ($kitchenId ? Kitchen::find($kitchenId)?->name : null)
            ?? Kitchen::first()?->name
            ?? 'Bếp ăn';

        // Người kiểm tra mặc định: nhân viên liên kết với tài khoản (chọn lại từ danh mục trên UI)
        $this->inspector = auth()->user()?->employee?->name ?? '';
    }

    /**
     * Danh mục nhân viên để chọn Người kiểm tra (BA: chọn từ danh mục, không gõ tay).
     *
     * @return array<int, string>
     */
    public function getInspectorOptions(): array
    {
        return Employee::query()
            ->where('status', 'Đang làm việc')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tạo dữ liệu'),
        ];
    }

    public function getStats(): array
    {
        if (! $this->date) {
            return [
                'ingredients' => 0,
                'portions' => 0,
                'dishes' => 0,
                'forms' => 5,
            ];
        }

        // Hồ sơ kiểm thực (QĐ 1246) chỉ được lập trên thực đơn ĐÃ CHỐT — thực đơn nháp/đang gửi
        // chưa phải bữa ăn thực tế, đưa vào biểu mẫu là sai hồ sơ pháp lý.
        $query = Menu::with(['recipe.ingredients'])
            ->where('status', 'locked')
            ->whereDate('date', $this->date);

        if ($this->selectedShift) {
            $query->where('shift_id', $this->selectedShift);
        }

        $menus = $query->get();

        $portions = $menus->sum('estimated_portions');
        $dishes = $menus->pluck('recipe_id')->unique()->count();

        $ingredientIds = [];
        foreach ($menus as $menu) {
            if ($menu->recipe) {
                foreach ($menu->recipe->ingredients as $ingredient) {
                    $ingredientIds[$ingredient->id] = true;
                }
            }
        }
        $ingredients = count($ingredientIds);

        return [
            'ingredients' => $ingredients,
            'portions' => $portions,
            'dishes' => $dishes,
            'forms' => 5,
        ];
    }

    /**
     * @return array<int, array{key: string, sheet: string, icon: string}>
     */
    public function getStepTabs(): array
    {
        return collect(self::STEPS)
            ->map(fn (string $step): array => [
                'key' => $step,
                'sheet' => self::SHEET_CONFIG[$step]['sheet'],
                'icon' => self::SHEET_CONFIG[$step]['icon'],
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSheetView(): array
    {
        $config = self::SHEET_CONFIG[$this->activeStep] ?? self::SHEET_CONFIG['Bước 1'];
        $items = $this->getAuditItems();

        return [
            ...$config,
            'items' => $items,
            'rows' => $this->sheetRows($items, $config),
            'companyName' => Setting::get('company_name', 'CÔNG TY TNHH DỊCH VỤ CJ CATERING VIỆT NAM'),
            'companyAddress' => Setting::get('company_address', 'TỔ 15, ẤP 2 XÃ LONG THỌ, HUYỆN NHƠN TRẠCH, TỈNH ĐỒNG NAI'),
            'dateText' => $this->date ? Carbon::parse($this->date)->format('d/m/Y') : '',
        ];
    }

    public function getExcelTemplateHtml(): string
    {
        $sheet = $this->getSheetView();

        return app(FoodSafetyExcelTemplateRenderer::class)->render($this->activeStep, [
            'dateText' => (string) $sheet['dateText'],
            'canteen' => $this->canteen,
            'inspector' => $this->inspector,
            'companyName' => (string) $sheet['companyName'],
            'companyAddress' => (string) $sheet['companyAddress'],
            'items' => $sheet['items'],
        ]);
    }

    public function getAuditItems(): array
    {
        if (! $this->date) {
            return [];
        }

        // Chỉ thực đơn ĐÃ CHỐT (xem chú thích ở getStats)
        $query = Menu::with(['recipe.ingredients.supplier', 'shift'])
            ->where('status', 'locked')
            ->whereDate('date', $this->date);

        if ($this->selectedShift) {
            $query->where('shift_id', $this->selectedShift);
        }

        $menus = $query->get();

        if ($this->activeStep === 'Bước 1') {
            // Step 1: Input raw ingredients check
            $ingredientIds = $menus
                ->flatMap(fn ($menu) => $menu->recipe?->ingredients->pluck('id') ?? collect())
                ->unique()
                ->values();

            // NCC + chứng từ lấy theo PO ĐÃ NHẬP KHO gần nhất của từng nguyên liệu
            // (BA: theo PO/ngày nhập thực tế, không dùng NCC cố định của nguyên liệu)
            $poInfoByIngredient = PurchaseOrderItem::query()
                ->whereIn('ingredient_id', $ingredientIds)
                ->whereHas('purchaseOrder', fn ($q) => $q->whereNotNull('stocked_at'))
                ->with(['purchaseOrder:id,code,supplier_id,stocked_at', 'purchaseOrder.supplier:id,name,phone,contact_name'])
                ->get(['id', 'purchase_order_id', 'ingredient_id'])
                ->sortByDesc(fn ($item) => $item->purchaseOrder?->stocked_at)
                ->unique('ingredient_id')
                ->keyBy('ingredient_id');

            $seenIngredients = [];
            foreach ($menus as $menu) {
                $recipe = $menu->recipe;
                if (! $recipe) {
                    continue;
                }
                foreach ($recipe->ingredients as $ingredient) {
                    $qty = $menu->estimated_portions * $ingredient->pivot->quantity_per_portion;
                    if (isset($seenIngredients[$ingredient->id])) {
                        $seenIngredients[$ingredient->id]['quantity'] += $qty;

                        continue;
                    }

                    $poInfo = $poInfoByIngredient->get($ingredient->id);
                    $quantityDisplay = in_array($ingredient->unit, ['Quả', 'Trái', 'Cái'], true)
                        ? number_format($qty, 0).' '.$ingredient->unit
                        : number_format($qty, 2, ',', '.').' kg';

                    $seenIngredients[$ingredient->id] = [
                        'name' => $ingredient->name,
                        'type' => $ingredient->type,
                        'time' => $poInfo?->purchaseOrder?->stocked_at?->format('H:i') ?? '',
                        'quantity' => $qty,
                        'quantity_display' => $quantityDisplay,
                        'unit' => $ingredient->unit,
                        'supplier' => $poInfo?->purchaseOrder?->supplier?->name
                            ?? $ingredient->supplier->name
                            ?? 'Cơ sở tự do',
                        'supplier_contact' => $poInfo?->purchaseOrder?->supplier?->phone ?? '',
                        'deliverer' => $poInfo?->purchaseOrder?->supplier?->contact_name ?? '',
                        // Chứng từ = mã PO thật đã nhập kho; chưa có PO thì để trống thay vì chuỗi tự chế
                        'invoice' => $poInfo?->purchaseOrder?->code ?? '—',
                        'vet_check' => ($ingredient->type === 'Động vật') ? 'Đạt' : '—',
                        'quarantine' => ($ingredient->type === 'Động vật') ? 'Có' : '—',
                        'sensory' => 'Đạt',
                        'quick_test' => '—',
                        'action' => '',
                        'notes' => '',
                    ];
                }
            }

            // Gom theo đúng thứ tự nhóm trong sheet B1, không theo type thô trong danh mục.
            uasort($seenIngredients, fn (array $a, array $b): int => [
                $this->stepOneGroupRank($a),
                $a['name'],
            ] <=> [
                $this->stepOneGroupRank($b),
                $b['name'],
            ]);

            return array_values($seenIngredients);
        }

        // Steps 2 to 5: Dishes list check — lấy dữ liệu THẬT đã ghi nhận (nếu có)
        $records = $this->auditRecordsByRecipe();

        $dishes = [];
        foreach ($menus as $menu) {
            $recipe = $menu->recipe;
            if (! $recipe) {
                continue;
            }

            $dishName = $recipe->name;
            /** @var FoodSafetyAudit|null $audit */
            $audit = $records->get($recipe->id);

            // Các key bổ sung (shift/portions/main_ingredients/...) phục vụ Excel theo
            // biểu mẫu B1–B5; các key cũ giữ nguyên cho bảng hiển thị trên trang.
            $shiftLabel = $menu->shift->name ?? '';
            $portions = (int) $menu->estimated_portions;

            if ($this->activeStep === 'Bước 2') {
                $mainIngredients = $recipe->ingredients
                    ->map(fn ($ing) => $ing->name.' '.rtrim(rtrim(number_format($ing->pivot->quantity_per_portion * $portions, 1, ',', '.'), '0'), ',').'kg')
                    ->take(6)
                    ->implode(', ');

                $dishes[] = [
                    'name' => $dishName,
                    'shift' => $shiftLabel,
                    'main_ingredients' => $mainIngredients,
                    'portions' => $portions,
                    // cook_start_at là chuỗi TIME (không cast datetime) — cắt HH:MM như timeRange()
                    'prep_time' => $audit?->cook_start_at ? substr((string) $audit->cook_start_at, 0, 5) : '',
                    'finish_time' => $audit?->cook_end_at ? substr((string) $audit->cook_end_at, 0, 5) : '',
                    'time' => $this->timeRange($audit?->cook_start_at, $audit?->cook_end_at),
                    'staff_check' => 'Đạt',
                    'equipment_check' => 'Đạt',
                    'area_check' => 'Đạt',
                    'sensory' => $audit?->status ?? '',
                    'temp' => $audit?->temperature ?? '',
                    'cook' => $audit?->inspected_by ?? '',
                    'kitchen' => $shiftLabel,
                    'action' => '',
                    'notes' => $audit?->notes ?? '',
                ];
            } elseif ($this->activeStep === 'Bước 3') {
                $dishes[] = [
                    'name' => $dishName,
                    'shift' => $shiftLabel,
                    'portions' => $portions,
                    'time' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'eat_time' => $audit?->sample_kept_at?->copy()->addMinutes(30)?->format('H:i') ?? '',
                    'utensil' => $audit?->utensil ?? '',
                    'sensory' => $audit?->status ?? '',
                    'sample_kept' => $audit && $audit->sample_kept_by ? 'Có ('.$audit->sample_kept_by.')' : '',
                    'temp' => $audit?->temperature ?? '',
                    'action' => '',
                    'notes' => $audit?->notes ?? '',
                ];
            } elseif ($this->activeStep === 'Lưu mẫu') {
                $dishes[] = [
                    'name' => $dishName,
                    'shift' => $shiftLabel,
                    'portions' => $portions,
                    'sample_amount' => $this->sampleAmount($dishName),
                    'container' => $audit?->utensil ?: 'Hũ Inox',
                    'time' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'destroy_at' => $audit?->sample_kept_at?->copy()->addDay()?->format('H:i (d/m)') ?? '',
                    'quantity' => '',
                    'sample_code' => $audit?->sample_code ?? '',
                    'temp' => $audit?->temperature ?: '2-8°C',
                    'staff' => $audit?->sample_kept_by ?: $this->inspector,
                    'destroyer' => '',
                    'notes' => $audit?->notes ?: 'Đ',
                ];
            } elseif ($this->activeStep === 'Hủy mẫu') {
                $dishes[] = [
                    'name' => $dishName,
                    'shift' => $shiftLabel,
                    'portions' => $portions,
                    'sample_amount' => $this->sampleAmount($dishName),
                    'container' => $audit?->utensil ?: 'Hũ Inox',
                    'temp' => $audit?->temperature ?: '2-8°C',
                    'kept_at' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'time' => $audit?->sample_kept_at?->copy()->addDay()?->format('H:i (d/m)') ?? '',
                    'retention' => '24 giờ',
                    'status' => $audit?->status ?? '',
                    'keeper' => $audit?->sample_kept_by ?: $this->inspector,
                    'staff' => '',
                    'notes' => $audit?->notes ?: 'Đ',
                ];
            }
        }

        return $dishes;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $config
     * @return array<int, array<string, mixed>>
     */
    protected function sheetRows(array $items, array $config): array
    {
        $rows = [];
        $lastGroup = null;

        foreach ($items as $index => $item) {
            if ($this->activeStep === 'Bước 1') {
                $group = $this->stepOneGroupTitle($item);
                if ($group !== $lastGroup) {
                    $rows[] = [
                        'type' => 'group',
                        'label' => $group,
                        'colspan' => $config['columns'],
                    ];
                    $lastGroup = $group;
                }
            }

            $cells = [['value' => $index + 1, 'class' => 'text-center fsa-muted']];
            foreach ($config['keys'] as $key) {
                $cells[] = [
                    'value' => $this->displayValue($item[$key] ?? null),
                    'class' => $this->cellClass($key),
                ];
            }

            $rows[] = ['type' => 'data', 'cells' => $cells];
        }

        return $rows;
    }

    protected function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return (string) $value;
    }

    protected function cellClass(string $key): string
    {
        return match ($key) {
            'quantity_display', 'portions' => 'text-right font-bold',
            'time', 'prep_time', 'finish_time', 'eat_time', 'destroy_at', 'kept_at',
            'vet_check', 'quarantine', 'sensory', 'quick_test', 'staff_check',
            'equipment_check', 'area_check', 'sample_amount', 'container', 'temp' => 'text-center',
            'name', 'staff', 'keeper', 'destroyer' => 'font-bold',
            'action', 'notes' => 'fsa-muted',
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function stepOneGroupRank(array $item): int
    {
        $label = $this->stepOneGroupTitle($item);

        return match (true) {
            str_starts_with($label, 'I.') => 10,
            str_starts_with($label, 'II.') => 20,
            str_starts_with($label, 'III.') => 30,
            str_starts_with($label, 'VI.') => 60,
            default => 90,
        };
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function stepOneGroupTitle(array $item): string
    {
        $type = (string) ($item['type'] ?? '');
        $name = (string) ($item['name'] ?? '');
        $normalized = mb_strtolower($type.' '.$name);

        return match (true) {
            str_contains($normalized, 'động vật'), str_contains($normalized, 'thịt'), str_contains($normalized, 'cá') => 'I. Thực phẩm tươi sống, đông lạnh: thịt, cá, gà,...',
            str_contains($normalized, 'thực vật'), str_contains($normalized, 'rau'), str_contains($normalized, 'củ'), str_contains($normalized, 'quả'), str_contains($normalized, 'trái cây'), str_contains($normalized, 'gia vị'), str_contains($normalized, 'sả'), str_contains($normalized, 'hành') => 'II. Rau củ, quả, trái cây, các loại,...',
            str_contains($normalized, 'thực phẩm khô'), str_contains($normalized, 'thực phẩm chế biến'), str_contains($normalized, 'lương thực'), str_contains($normalized, 'bún'), str_contains($normalized, 'đậu'), str_contains($normalized, 'gạo'), str_contains($normalized, 'mỳ') => 'III. Bún, đậu hủ,...',
            str_contains($normalized, 'trứng') => 'VI. Trứng các loại,...',
            default => $type,
        };
    }

    protected function sampleAmount(string $dishName): string
    {
        $name = mb_strtolower($dishName);

        if (str_contains($name, 'canh') || str_contains($name, 'súp') || str_contains($name, 'bún') || str_contains($name, 'nước')) {
            return '150ml';
        }

        return '100g';
    }

    /**
     * Lấy các bản ghi kiểm thực đã lưu cho ngày/ca/bước hiện tại, keyed theo recipe_id.
     *
     * @return Collection<int, FoodSafetyAudit>
     */
    protected function auditRecordsByRecipe(): Collection
    {
        $query = FoodSafetyAudit::whereDate('date', $this->date)
            ->where('stage', $this->activeStep)
            ->whereNotNull('recipe_id');

        if ($this->selectedShift) {
            $query->where('shift_id', $this->selectedShift);
        }

        return $query->get()->keyBy('recipe_id');
    }

    /**
     * Định dạng khoảng thời gian chế biến "HH:MM - HH:MM" từ 2 mốc giờ.
     */
    protected function timeRange(?string $start, ?string $end): string
    {
        $start = $start ? substr($start, 0, 5) : '';
        $end = $end ? substr($end, 0, 5) : '';

        if ($start && $end) {
            return $start.' - '.$end;
        }

        return $start ?: $end;
    }

    /**
     * Xuất báo cáo kiểm thực 3 bước theo biểu mẫu chuẩn Bộ Y tế (QĐ 1246/QĐ-BYT) — file .xlsx.
     * Mỗi bước là 1 sheet có tiêu đề gộp ô; dữ liệu B2/B3 lấy từ bản ghi kiểm thực đã lưu.
     */
    public function exportExcel(): BinaryFileResponse
    {
        $fileName = 'BaoCao_KiemThuc_3Buoc_'.str_replace('-', '', (string) $this->date).'.xlsx';

        $steps = ['Bước 1', 'Bước 2', 'Bước 3', 'Lưu mẫu', 'Hủy mẫu'];
        $itemsByStep = [];
        $previousStep = $this->activeStep;

        foreach ($steps as $step) {
            $this->activeStep = $step;
            $itemsByStep[$step] = $this->getAuditItems();
        }

        $this->activeStep = $previousStep;

        return Excel::download(
            new FoodSafetyAuditReportExport($itemsByStep, (string) $this->date, $this->canteen, $this->inspector),
            $fileName,
        );
    }
}
