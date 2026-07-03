<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    protected function afterSave(): void
    {
        Notification::make()
            ->success()
            ->title('Đã cập nhật hồ sơ thành công!')
            ->send();

        $this->redirect(filament()->getUrl());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ảnh đại diện')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar_url')
                            ->label('Ảnh hồ sơ')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->imagePreviewHeight('200')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->helperText('Tải lên ảnh JPG, PNG hoặc WebP. Tối đa 2MB.')
                            ->alignCenter(),
                    ]),

                Forms\Components\Section::make('Thông tin tài khoản')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }
}
