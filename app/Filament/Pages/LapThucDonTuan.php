<?php

namespace App\Filament\Pages;

use App\Filament\Resources\MenuResource;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LapThucDonTuan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('Lập thực đơn tuần');
    }

    public function getTitle(): string
    {
        return __('Lập thực đơn tuần');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('VẬN HÀNH BẾP');
    }

    protected static string $view = 'filament.pages.lap-thuc-don-tuan';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Danh sách thứ trong tuần (offset ngày từ ngày bắt đầu).
     *
     * @var array<int, string>
     */
    public const WEEKDAYS = [
        0 => 'Thứ 2', 1 => 'Thứ 3', 2 => 'Thứ 4', 3 => 'Thứ 5',
        4 => 'Thứ 6', 5 => 'Thứ 7', 6 => 'Chủ nhật',
    ];

    public function mount(): void
    {
        $this->form->fill([
            'kitchen_id' => Filament::auth()->user()?->currentKitchenId(),
            'week_start' => Carbon::now()->startOfWeek()->toDateString(),
            'entries' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Kỳ thực đơn')
                    ->columns(2)
                    ->schema([
                        Select::make('kitchen_id')
                            ->label('Bếp ăn')
                            ->options(fn () => Kitchen::pluck('name', 'id'))
                            ->searchable(),
                        DatePicker::make('week_start')
                            ->label('Ngày bắt đầu tuần (Thứ 2)')
                            ->required()
                            ->helperText('Các món sẽ được xếp theo thứ tính từ ngày này.'),
                    ]),
                Repeater::make('entries')
                    ->label('Các món trong tuần')
                    ->schema([
                        Select::make('day')
                            ->label('Thứ')
                            ->options(self::WEEKDAYS)
                            ->required(),
                        Select::make('shift_id')
                            ->label('Ca')
                            ->options(fn () => Shift::pluck('name', 'id'))
                            ->required(),
                        Select::make('recipe_id')
                            ->label('Món ăn')
                            ->options(fn () => Recipe::where('status', 'active')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('estimated_portions')
                            ->label('Số suất')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(4)
                    ->addActionLabel('Thêm món vào tuần')
                    ->defaultItems(0),
            ])
            ->statePath('data');
    }

    /**
     * Lưu toàn bộ thực đơn tuần với trạng thái tương ứng, kèm cảnh báo lặp món 3 tuần gần nhất.
     */
    public function save(string $status = 'draft'): void
    {
        // Ghi/chốt thực đơn là đầu pipeline (List hàng → PO → Kho) — phải có quyền theo policy Menu
        abort_unless(MenuResource::canCreate(), 403);

        $state = $this->form->getState();
        $entries = $state['entries'] ?? [];

        if (empty($entries)) {
            Notification::make()->title('Chưa có món nào để lưu!')->warning()->send();

            return;
        }

        $weekStart = Carbon::parse($state['week_start']);
        $kitchenId = $state['kitchen_id'] ?? null;

        // User không phải quản trị chỉ được lập thực đơn cho bếp của chính mình
        // (select bếp trên form là dữ liệu client, không tin được)
        $user = auth()->user();
        if ($user && ! $user->hasRole(['super_admin', 'Quản trị viên'])) {
            $ownKitchenId = $user->currentKitchenId();
            if ($ownKitchenId && (int) $kitchenId !== (int) $ownKitchenId) {
                Notification::make()->title('Bạn chỉ có thể lập thực đơn cho bếp của mình!')->danger()->send();

                return;
            }
        }

        $duplicates = $this->duplicateWarnings($entries, $weekStart, $kitchenId);

        $skippedLocked = 0;

        DB::transaction(function () use ($entries, $weekStart, $kitchenId, $status, &$skippedLocked): void {
            foreach ($entries as $entry) {
                $date = $weekStart->copy()->addDays((int) $entry['day'])->toDateString();

                // Không hạ cấp thực đơn ĐÃ CHỐT về nháp/gửi khách — locked là căn cứ đã sinh PO/List hàng
                $existing = Menu::where('kitchen_id', $kitchenId)
                    ->where('date', $date)
                    ->where('shift_id', $entry['shift_id'])
                    ->where('recipe_id', $entry['recipe_id'])
                    ->first();

                if ($existing && $existing->status === 'locked' && $status !== 'locked') {
                    $skippedLocked++;

                    continue;
                }

                Menu::updateOrCreate(
                    [
                        'kitchen_id' => $kitchenId,
                        'date' => $date,
                        'shift_id' => $entry['shift_id'],
                        'recipe_id' => $entry['recipe_id'],
                    ],
                    [
                        'estimated_portions' => $entry['estimated_portions'],
                        'status' => $status,
                    ],
                );
            }
        });

        if ($skippedLocked > 0) {
            Notification::make()
                ->title("{$skippedLocked} món đã CHỐT được giữ nguyên (không ghi đè về '{$status}')")
                ->warning()
                ->send();
        }

        $label = match ($status) {
            'sent' => 'Đã gửi khách hàng',
            'locked' => 'Đã chốt',
            default => 'Đã lưu nháp',
        };

        Notification::make()
            ->title($label.' thực đơn tuần ('.count($entries).' món)')
            ->success()
            ->send();

        if (! empty($duplicates)) {
            Notification::make()
                ->title('Cảnh báo lặp món trong 3 tuần gần nhất')
                ->body('Các món đã từng xuất hiện: '.implode(', ', $duplicates))
                ->warning()
                ->persistent()
                ->send();
        }
    }

    public function saveDraft(): void
    {
        $this->save('draft');
    }

    public function sendToClient(): void
    {
        $this->save('sent');
    }

    public function lockWeek(): void
    {
        $this->save('locked');
    }

    /**
     * Quét lịch sử thực đơn 3 TUẦN (21 ngày) trước tuần đang lập; trả về tên các món bị lặp.
     *
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, string>
     */
    public function duplicateWarnings(array $entries, Carbon $weekStart, ?int $kitchenId): array
    {
        $recipeIds = collect($entries)->pluck('recipe_id')->filter()->unique();

        if ($recipeIds->isEmpty()) {
            return [];
        }

        $from = $weekStart->copy()->subDays(21)->toDateString();
        $to = $weekStart->copy()->subDay()->toDateString();

        $existing = Menu::query()
            ->when($kitchenId, fn ($query) => $query->where('kitchen_id', $kitchenId))
            ->whereBetween('date', [$from, $to])
            ->whereIn('recipe_id', $recipeIds)
            ->with('recipe')
            ->get();

        return $existing->pluck('recipe.name')->filter()->unique()->values()->all();
    }
}
