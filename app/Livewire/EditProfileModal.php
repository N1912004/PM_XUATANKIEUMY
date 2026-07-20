<?php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Livewire\Attributes\On;
use Livewire\Component;

class EditProfileModal extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    #[On('open-profile-modal')]
    public function openModal(): void
    {
        $this->mountAction('editProfile');
    }

    public function editProfileAction(): Action
    {
        return Action::make('editProfile')
            ->modalHeading(__('user.profile.heading'))
            ->modalSubmitActionLabel(__('user.profile.save'))
            ->modalCancelActionLabel(__('common.actions.cancel'))
            ->modalWidth(MaxWidth::TwoExtraLarge)
            ->fillForm(fn () => auth()->user()?->toArray() ?? [])
            ->form([
                Grid::make(3)
                    ->schema([
                        // Left column: Avatar upload (1/3 width)
                        FileUpload::make('avatar_url')
                            ->label(__('user.profile.avatar'))
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
                            ->imagePreviewHeight('150')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->helperText(__('user.profile.avatar_help'))
                            ->alignCenter()
                            ->columnSpan(1),

                        // Right column: Account inputs (2/3 width)
                        Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('user.profile.name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label(__('user.profile.email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(table: 'users', column: 'email', ignorable: fn () => auth()->user()),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('password')
                                            ->label(__('user.profile.new_password'))
                                            ->password()
                                            ->autocomplete('new-password')
                                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->confirmed(),

                                        TextInput::make('password_confirmation')
                                            ->label(__('user.profile.password_confirmation'))
                                            ->password()
                                            ->requiredWith('password')
                                            ->dehydrated(false),
                                    ]),
                            ]),
                    ]),
            ])
            ->action(function (array $data) {
                $user = auth()->user();
                if ($user) {
                    $user->update($data);

                    Notification::make()
                        ->success()
                        ->title(__('user.messages.profile_updated'))
                        ->send();

                    $this->redirect(request()->header('Referer') ?: filament()->getUrl());
                }
            });
    }

    public function render()
    {
        return view('livewire.edit-profile-modal');
    }
}
