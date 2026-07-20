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

/**
 * Bản lập thực đơn tuần dạng repeater (màn CŨ).
 *
 * BA R33 yêu cầu lập thực đơn tuần bằng GRID T2→CN có nút (+) từng ô ngày/ca, và nói rõ
 * "không đặt nút Thêm món trên toolbar chung gây nhầm luồng" — đúng luồng đó nằm ở
 * MenuResource/Pages/ListMenus (view 'week'). Trang này bị ẨN KHỎI MENU để tránh hai
 * màn lập thực đơn tuần song song; route vẫn giữ để không vỡ link cũ đã lưu.
 */
class LapThucDonTuan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('menu.weekly.title');
    }

    public function getTitle(): string
    {
        return __('menu.weekly.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('menu.navigation.group');
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
                            ->label(__('menu.fields.kitchen'))
                            ->options(fn () => Kitchen::pluck('name', 'id'))
                            ->searchable(),
                        DatePicker::make('week_start')
                            ->label(__('menu.weekly.fields.week_start'))
                            ->required()
                            ->helperText(__('menu.weekly.helpers.week_start')),
                    ]),
                TextInput::make('edit_reason')
                    ->label(__('menu.fields.audit_reason'))
                    ->placeholder(__('menu.weekly.placeholders.audit_reason'))
                    ->maxLength(255),
                Repeater::make('entries')
                    ->label(__('menu.weekly.fields.items'))
                    ->schema([
                        Select::make('day')
                            ->label(__('menu.weekly.fields.day'))
                            ->options(self::WEEKDAYS)
                            ->required(),
                        Select::make('shift_id')
                            ->label(__('menu.fields.shift'))
                            ->options(fn () => Shift::pluck('name', 'id'))
                            ->required(),
                        Select::make('recipe_id')
                            ->label(__('menu.fields.recipe'))
                            ->options(fn () => Recipe::where('status', 'active')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('estimated_portions')
                            ->label(__('menu.fields.estimated_portions'))
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
            Notification::make()->title(__('menu.weekly.notifications.no_items'))->warning()->send();

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
                Notification::make()->title(__('menu.errors.own_kitchen_only'))->danger()->send();

                return;
            }
        }

        $duplicates = $this->duplicateWarnings($entries, $weekStart, $kitchenId);

        $editReason = trim((string) ($state['edit_reason'] ?? ''));

        // Nạp toàn bộ menu hiện có của các ngày liên quan bằng 1 query, tra theo khóa
        // "date|shift|recipe" — thay cho 1 EXISTS + 1 SELECT mỗi entry (2N round-trip)
        $entryDates = collect($entries)
            ->map(fn ($entry) => $weekStart->copy()->addDays((int) $entry['day'])->toDateString())
            ->unique()
            ->values();

        $existingByKey = Menu::where('kitchen_id', $kitchenId)
            ->whereIn('date', $entryDates)
            ->get()
            ->keyBy(fn (Menu $menu) => $menu->date->toDateString().'|'.$menu->shift_id.'|'.$menu->recipe_id);

        // Sửa thực đơn ĐÃ CHỐT bắt buộc phải có lý do (lưu vào menu_audit_logs.reason)
        $touchesLocked = collect($entries)->contains(function ($entry) use ($weekStart, $existingByKey) {
            $date = $weekStart->copy()->addDays((int) $entry['day'])->toDateString();

            return $existingByKey->get($date.'|'.$entry['shift_id'].'|'.$entry['recipe_id'])?->status === 'locked';
        });

        if ($touchesLocked && $editReason === '') {
            Notification::make()
                ->title(__('menu.errors.audit_reason_required'))
                ->body('Bạn đang sửa thực đơn ĐÃ CHỐT — vui lòng nhập "Lý do sửa" trước khi lưu.')
                ->danger()
                ->send();

            return;
        }

        $skippedDowngrade = 0;
        $skippedPast = 0;

        DB::transaction(function () use ($entries, $weekStart, $kitchenId, $status, $editReason, $existingByKey, &$skippedDowngrade, &$skippedPast): void {
            foreach ($entries as $entry) {
                $date = $weekStart->copy()->addDays((int) $entry['day'])->toDateString();

                $existing = $existingByKey->get($date.'|'.$entry['shift_id'].'|'.$entry['recipe_id']);

                if ($existing) {
                    // Guard vòng đời dùng chung (khóa quá khứ / không hạ cấp / lý do khi sửa đã chốt)
                    $blocked = $existing->editBlockReason($status, $editReason);
                    if ($blocked !== null) {
                        $blocked === 'past' ? $skippedPast++ : $skippedDowngrade++;

                        continue;
                    }

                    $existing->auditReason = $editReason !== '' ? $editReason : null;
                    $existing->update([
                        'estimated_portions' => $entry['estimated_portions'],
                        'status' => $status,
                    ]);
                } else {
                    Menu::create([
                        'kitchen_id' => $kitchenId,
                        'date' => $date,
                        'shift_id' => $entry['shift_id'],
                        'recipe_id' => $entry['recipe_id'],
                        'estimated_portions' => $entry['estimated_portions'],
                        'status' => $status,
                    ]);
                }
            }
        });

        if ($skippedDowngrade > 0) {
            Notification::make()
                ->title(__('menu.weekly.notifications.downgrade_skipped', ['count' => $skippedDowngrade, 'status' => Menu::STATUS_LABELS[$status] ?? $status]))
                ->warning()
                ->send();
        }

        if ($skippedPast > 0) {
            Notification::make()
                ->title(__('menu.weekly.notifications.past_skipped', ['count' => $skippedPast]))
                ->warning()
                ->send();
        }

        $label = $status === 'draft' ? 'Đã lưu nháp' : (Menu::STATUS_LABELS[$status] ?? $status);

        Notification::make()
            ->title(__('menu.weekly.notifications.saved', ['status' => $label, 'count' => count($entries)]))
            ->success()
            ->send();

        if (! empty($duplicates)) {
            Notification::make()
                ->title(__('menu.weekly.notifications.duplicate_warning'))
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

    public function confirmByClient(): void
    {
        $this->save('confirmed');
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
