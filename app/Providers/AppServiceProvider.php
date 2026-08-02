<?php

namespace App\Providers;

use BladeUI\Icons\Factory;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteAction as TableForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (class_exists(Factory::class)) {
            app(Factory::class)->add('fa', [
                'path' => resource_path('svg/fa'),
                'prefix' => 'fa',
            ]);
        }

        $this->configureNotificationDuration();

        $configureDelete = function ($action) {
            $action
                ->modalAlignment(Alignment::Left)
                ->modalHeading('Xóa')
                ->modalDescription(function ($record = null) {
                    if (! $record) {
                        return 'Bạn có chắc chắn muốn xóa các mục đã chọn?';
                    }
                    $class = get_class($record);

                    return match (true) {
                        str_contains($class, 'Ingredient') => 'Bạn có chắc chắn muốn xóa nguyên liệu này?',
                        str_contains($class, 'Supplier') => 'Bạn có chắc chắn muốn xóa nhà cung cấp này không?',
                        str_contains($class, 'Recipe') => 'Bạn có chắc chắn muốn xóa món ăn này?',
                        str_contains($class, 'Employee') => 'Bạn có chắc chắn muốn xóa nhân viên này?',
                        default => 'Bạn có chắc chắn muốn xóa mục này?',
                    };
                })
                ->modalSubmitActionLabel('Xóa')
                ->modalCancelActionLabel('Hủy')
                ->modalIcon('heroicon-s-exclamation-triangle')
                ->modalIconColor('danger')
                ->icon(new HtmlString('<i class="fa-solid fa-trash" style="color:#dc2626;font-size:14px"></i>'));
        };

        DeleteAction::configureUsing($configureDelete);
        TableDeleteAction::configureUsing($configureDelete);
        DeleteBulkAction::configureUsing($configureDelete);
        ForceDeleteAction::configureUsing($configureDelete);
        TableForceDeleteAction::configureUsing($configureDelete);
        ForceDeleteBulkAction::configureUsing($configureDelete);
    }

    /**
     * Thời gian hiển thị popup thông báo = 2 giây (để người dùng kịp nhận ra)
     * + 0,3 giây mỗi từ (để kịp đọc hết nội dung). Mặc định của Filament là
     * 6 giây cố định — thông báo "Đã lưu" đứng y như một thông báo lỗi dài.
     *
     * Đăng ký bằng Closure chứ KHÔNG tính sẵn ra số: configure() chạy ngay lúc
     * Notification::make(), trước khi title/body được gán, nên tính ngay tại đó
     * sẽ luôn đếm được 0 từ. getDuration() gọi evaluate() lúc gửi, khi ấy nội
     * dung mới đầy đủ.
     *
     * Chỗ nào cần thời gian khác thì gọi ->duration()/->seconds()/->persistent()
     * trên chính notification đó — gọi sau nên vẫn đè được cấu hình này.
     */
    protected function configureNotificationDuration(): void
    {
        Notification::configureUsing(function (Notification $notification): void {
            $notification->duration(function () use ($notification): int {
                return $this->readingDuration(
                    (string) $notification->getTitle().' '.(string) $notification->getBody()
                );
            });
        });
    }

    /**
     * Quy đổi nội dung thông báo thành số mili giây hiển thị.
     */
    protected function readingDuration(string $content): int
    {
        // Body của Filament là HTML (dùng <br> xuống dòng). Đổi thẻ thành khoảng
        // trắng chứ đừng strip_tags thẳng: "một<br>Hai" sẽ dính thành "mộtHai"
        // và bị đếm thiếu một từ.
        $plain = strip_tags(preg_replace('/<[^>]*>/', ' ', $content) ?? $content);
        $text = trim(preg_replace('/\s+/u', ' ', $plain) ?? '');

        $words = $text === '' ? 0 : count(explode(' ', $text));

        return 2000 + ($words * 300);
    }
}
