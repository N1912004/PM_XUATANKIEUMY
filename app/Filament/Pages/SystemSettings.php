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

    public static function getNavigationLabel(): string
    {
        return __('settings.navigation_label');
    }

    public function getTitle(): string
    {
        return __('settings.title');
    }

    protected static ?string $slug = 'system-settings';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.system-settings';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
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
            'primary_color' => Setting::get('primary_color', '#267DC1'),
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
                        Forms\Components\Tabs\Tab::make(__('settings.tabs.branding'))
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Forms\Components\Section::make(__('settings.branding.cms_info'))
                                    ->description(__('settings.branding.cms_info_desc'))
                                    ->icon('heroicon-o-globe-alt')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('site_name')
                                            ->label(__('settings.branding.site_name'))
                                            ->placeholder(__('settings.branding.site_name_placeholder'))
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText(__('settings.branding.site_name_helper')),
                                        Forms\Components\TextInput::make('site_description')
                                            ->label(__('settings.branding.site_description'))
                                            ->placeholder(__('settings.branding.site_description_placeholder'))
                                            ->maxLength(500)
                                            ->helperText(__('settings.branding.site_description_helper')),
                                    ]),

                                Forms\Components\Section::make(__('settings.branding.logo_favicon'))
                                    ->description(__('settings.branding.logo_favicon_desc'))
                                    ->icon('heroicon-o-photo')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\FileUpload::make('site_logo')
                                            ->label(__('settings.branding.logo'))
                                            ->image()
                                            ->disk('public')
                                            ->directory('settings')
                                            ->visibility('public')
                                            ->openable()
                                            ->downloadable()
                                            ->imagePreviewHeight('80')
                                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/jpeg', 'image/webp'])
                                            ->maxSize(2048)
                                            ->helperText(__('settings.branding.logo_helper')),
                                        Forms\Components\FileUpload::make('site_favicon')
                                            ->label(__('settings.branding.favicon'))
                                            ->disk('public')
                                            ->directory('settings')
                                            ->visibility('public')
                                            ->openable()
                                            ->downloadable()
                                            ->imagePreviewHeight('48')
                                            ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->maxSize(1024)
                                            ->helperText(__('settings.branding.favicon_helper')),
                                    ]),

                                Forms\Components\Section::make(__('settings.branding.primary_color'))
                                    ->description(__('settings.branding.primary_color_desc'))
                                    ->icon('heroicon-o-swatch')
                                    ->schema([
                                        Forms\Components\ColorPicker::make('primary_color')
                                            ->label(__('settings.branding.primary_color'))
                                            ->helperText(__('settings.branding.primary_color_helper')),
                                    ]),
                            ]),

                        // Tab 2: Công ty
                        Forms\Components\Tabs\Tab::make(__('settings.tabs.company'))
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Section::make(__('settings.company.title'))
                                    ->description(__('settings.company.desc'))
                                    ->icon('heroicon-o-identification')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('company_name')
                                            ->label(__('settings.company.name'))
                                            ->placeholder(__('settings.company.name_placeholder'))
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('company_phone')
                                            ->label(__('settings.company.phone'))
                                            ->tel()
                                            ->placeholder(__('settings.company.phone_placeholder'))
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('company_email')
                                            ->label(__('settings.company.email'))
                                            ->email()
                                            ->placeholder(__('settings.company.email_placeholder'))
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('company_address')
                                            ->label(__('settings.company.address'))
                                            ->placeholder(__('settings.company.address_placeholder'))
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
            ->title(__('settings.notifications.saved_title'))
            ->body(__('settings.notifications.saved_body'))
            ->send();

        $this->redirect(filament()->getUrl());
    }
}
