<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $title = 'Cài đặt hệ thống';

    protected static ?string $navigationLabel = 'Cài đặt hệ thống';

    protected static ?string $slug = 'system-settings';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.system-settings';

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::ThreeExtraLarge;
    }

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::get('site_name', 'Bluefire Catering'),
            'site_description' => Setting::get('site_description', 'Hệ thống quản lý xuất ăn công nghiệp'),
            'site_logo' => Setting::get('site_logo'),
            'site_favicon' => Setting::get('site_favicon'),
            'company_name' => Setting::get('company_name', 'Bluefire Group'),
            'company_address' => Setting::get('company_address'),
            'company_phone' => Setting::get('company_phone'),
            'company_email' => Setting::get('company_email'),
            'primary_color' => Setting::get('primary_color', '#f59e0b'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('settings_tabs')
                    ->columnSpanFull()
                    ->tabs([
                        // Tab 1: Thương hiệu
                        Forms\Components\Tabs\Tab::make('Thương hiệu')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Forms\Components\Section::make('Thông tin CMS')
                                    ->description('Cấu hình tên và mô tả hiển thị trên hệ thống.')
                                    ->icon('heroicon-o-globe-alt')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('site_name')
                                            ->label('Tên CMS / Hệ thống')
                                            ->placeholder('VD: Bluefire Catering')
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('Tên hiển thị trên sidebar, tiêu đề trang và tab trình duyệt.'),
                                        Forms\Components\TextInput::make('site_description')
                                            ->label('Mô tả hệ thống')
                                            ->placeholder('VD: Hệ thống quản lý xuất ăn')
                                            ->maxLength(500)
                                            ->helperText('Mô tả ngắn gọn về hệ thống.'),
                                    ]),

                                Forms\Components\Section::make('Logo & Favicon')
                                    ->description('Tải lên logo và biểu tượng (favicon) cho hệ thống.')
                                    ->icon('heroicon-o-photo')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\FileUpload::make('site_logo')
                                            ->label('Logo hệ thống')
                                            ->image()
                                            ->disk('public')
                                            ->directory('settings')
                                            ->visibility('public')
                                            ->imagePreviewHeight('80')
                                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/jpeg', 'image/webp'])
                                            ->maxSize(1024)
                                            ->helperText('Logo hiển thị trên sidebar. Khuyến nghị: PNG/SVG, nền trong suốt, kích thước 200x60px.'),
                                        Forms\Components\FileUpload::make('site_favicon')
                                            ->label('Favicon')
                                            ->disk('public')
                                            ->directory('settings')
                                            ->visibility('public')
                                            ->imagePreviewHeight('48')
                                            ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->maxSize(512)
                                            ->helperText('Biểu tượng nhỏ hiển thị trên tab trình duyệt. Khuyến nghị: SVG hoặc PNG 32x32px.'),
                                    ]),

                                Forms\Components\Section::make('Màu chủ đạo')
                                    ->description('Tùy chỉnh màu sắc giao diện.')
                                    ->icon('heroicon-o-swatch')
                                    ->schema([
                                        Forms\Components\ColorPicker::make('primary_color')
                                            ->label('Màu chủ đạo')
                                            ->helperText('Màu chính dùng cho nút, link và các thành phần giao diện nổi bật.'),
                                    ]),
                            ]),

                        // Tab 2: Công ty
                        Forms\Components\Tabs\Tab::make('Thông tin công ty')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Section::make('Thông tin doanh nghiệp')
                                    ->description('Thông tin công ty hiển thị trên báo cáo và biểu mẫu xuất ra.')
                                    ->icon('heroicon-o-identification')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('company_name')
                                            ->label('Tên công ty')
                                            ->placeholder('VD: Công ty TNHH Bluefire Group')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('company_phone')
                                            ->label('Số điện thoại')
                                            ->tel()
                                            ->placeholder('VD: 0901 234 567')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('company_email')
                                            ->label('Email liên hệ')
                                            ->email()
                                            ->placeholder('VD: info@bluefire.vn')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('company_address')
                                            ->label('Địa chỉ')
                                            ->placeholder('VD: 123 Đường ABC, Quận 1, TP.HCM')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->contained(false)
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settingsMap = [
            'site_name' => 'branding',
            'site_description' => 'branding',
            'site_logo' => 'branding',
            'site_favicon' => 'branding',
            'primary_color' => 'branding',
            'company_name' => 'company',
            'company_address' => 'company',
            'company_phone' => 'company',
            'company_email' => 'company',
        ];

        foreach ($settingsMap as $key => $group) {
            if (array_key_exists($key, $data)) {
                $value = $data[$key];

                // FileUpload fields return an array of file paths.
                // We extract the first file path string for storage.
                if (is_array($value)) {
                    $value = reset($value) ?: null;
                }

                Setting::set($key, $value, $group);
            }
        }

        Setting::clearCache();

        Notification::make()
            ->success()
            ->title('Đã lưu cài đặt hệ thống!')
            ->body('Các thay đổi sẽ được áp dụng ngay lập tức.')
            ->send();

        $this->redirect(filament()->getUrl());
    }
}
