<?php

namespace App\Filament\Pages;

use App\Services\GoogleCalendarService;
use App\Services\SalonSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Подесувања';

    protected static ?string $navigationLabel = 'Подесувања';

    protected static ?string $title = 'Подесувања';

    protected static ?string $slug = 'podesuvanja';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(SalonSettings $settings): void
    {
        $this->form->fill($settings->current());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Контакт')
                    ->description('Се прикажува на сајтот и во е-поштата.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')->label('Телефон')->tel()->required()->maxLength(30),
                        TextInput::make('email')->label('Е-пошта')->email()->required()->maxLength(150),
                        TextInput::make('address')->label('Адреса')->required()->maxLength(150),
                        TextInput::make('city')->label('Град')->required()->maxLength(80),
                        TextInput::make('map_query')
                            ->label('Локација за Google Maps')
                            ->helperText('Адреса на латиница, на пр. Dimitar Vlahov, Kumanovo')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),
                        TextInput::make('socials.instagram')->label('Instagram (линк)')->url()->maxLength(200),
                        TextInput::make('socials.facebook')->label('Facebook (линк)')->url()->maxLength(200),
                    ]),
                Section::make('Календар')
                    ->description('Резервациите со платен капар во календарот на телефонот.')
                    ->schema([
                        TextEntry::make('calendar_feed_url')
                            ->label('Линк за претплата (iPhone / Apple Calendar и други)')
                            ->state(fn (): ?string => filled(config('salon.calendar_feed_secret'))
                                ? route('calendar.feed', config('salon.calendar_feed_secret'))
                                : null)
                            ->placeholder('Исклучено — поставете CALENDAR_FEED_SECRET во .env')
                            ->helperText('Приватен линк: не го споделувајте. На iPhone: Поставки → Календар → Сметки → Додај сметка → Друго → Претплатен календар.')
                            ->copyable(),
                        TextEntry::make('google_calendar_status')
                            ->label('Google Calendar')
                            ->state(fn (GoogleCalendarService $calendar): string => $calendar->isEnabled()
                                ? 'Вклучено — потврдените термини автоматски се додаваат.'
                                : 'Исклучено — се вклучува преку GOOGLE_CALENDAR_* во .env.'),
                    ]),
                Section::make('Резервации')
                    ->columns(2)
                    ->schema([
                        TextInput::make('booking.deposit_amount')->label('Капар')->suffix('ден.')->integer()->minValue(0)->required(),
                        TextInput::make('booking.deposit_hours')
                            ->label('Рок за плаќање капар')
                            ->helperText('Потоа резервацијата автоматски се откажува.')
                            ->suffix('часа')->integer()->minValue(1)->maxValue(336)->required(),
                        TextInput::make('booking.reminder_hours')
                            ->label('Потсетник пред терминот')
                            ->suffix('часа')->integer()->minValue(1)->maxValue(168)->required(),
                        TextInput::make('booking.buffer_minutes')
                            ->label('Пауза помеѓу термини')
                            ->suffix('мин.')->integer()->minValue(0)->maxValue(120)->required(),
                        TextInput::make('booking.slot_interval_minutes')
                            ->label('Термини почнуваат на секои')
                            ->suffix('мин.')->integer()->minValue(5)->maxValue(120)->required(),
                        TextInput::make('booking.min_notice_minutes')
                            ->label('Најрано резервирање однапред')
                            ->suffix('мин.')->integer()->minValue(0)->required(),
                        TextInput::make('booking.max_days_ahead')
                            ->label('Најдоцна резервирање однапред')
                            ->suffix('дена')->integer()->minValue(1)->maxValue(365)->required(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Зачувај')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(SalonSettings $settings): void
    {
        $settings->save($this->form->getState());

        Notification::make()->title('Подесувањата се зачувани')->success()->send();
    }
}
