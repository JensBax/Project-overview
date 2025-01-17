<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Activities;
use App\Models\Expenses;
use App\Models\Projects;
use App\Models\User;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Resource
{
    protected static ?string $model = Projects::class;

    protected static ?string $navigationLabel = 'Projecten';
    protected static ?string $pluralLabel = 'Projecten';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->columns(2)
                            ->schema([
                                Select::make('users_id')
                                    ->label('Selecteer opnemer')
                                    ->options(
                                        User::all()->pluck('business', 'id')->toArray()
                                    )
                                    ->live()
                                    ->required()
                                    ->searchable(),
                                TextInput::make('client')->label('Opdractgever')->required(),
                                Textarea::make('description')->label('Opmerkingen')->columnSpanFull(),
                                TextInput::make('address')->label('Adres')->required(),
                                TextInput::make('city')->label('Plaats')->required(),
                                TextInput::make('price')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->label('Klusprijs')
                                    ->required(),
                                TextInput::make('duration_in_days')
                                    ->numeric()
                                    ->label('Duur in dagen')
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Toggle::make('is_finished')->label('Afgerond'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hiddenOn(['create']),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('address')->label('Adres')
                    ->searchable(),
                TextColumn::make('users.business')->label('Bedrijf'),
                IconColumn::make('is_finished')
                    ->icon(fn (Model $record) => $record->is_finished ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->colors([
                        'danger' => 0,
                        'success' => 1,
                    ])
                    ->label('Status'),
                TextColumn::make('updated_at')->hidden()
            ])
            ->searchPlaceholder('Zoek op adres')
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Open'),
                Tables\Actions\EditAction::make()->label('Bewerk'),
                Tables\Actions\DeleteAction::make()->label('Verwijder'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make()
                    ->schema([
                        TextEntry::make('users.business')->label('Opnemer'),
                        TextEntry::make('client')->label('Opdractgever'),
                        TextEntry::make('address')->label('Adres'),
                        TextEntry::make('city')->label('Plaats'),
                        TextEntry::make('price')
                            ->numeric()
                            ->label('Klusprijs')
                            ->money('EUR'),
                        TextEntry::make('duration_in_days')->label('Duur in dagen'),
                    ])
                    ->columns(2),
                InfolistSection::make()
                    ->schema([
                        TextEntry::make('description')
                            ->label('Opmerkingen')
                            ->default('Geen opmerkingen.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                InfolistSection::make()
                    ->schema([
                        TextEntry::make('expenses')->label('Totale uitgaven')->state(function (Model $record): string {
                            return Expenses::query()->where('projects_id', $record->id)->sum('price');
                        })->money('EUR'),
                        TextEntry::make('profit')->label('Winst')->state(function (Model $record): string {
                            return self::calculateProfit($record);
                        })->money('EUR'),
                        TextEntry::make('activities')->label('Totale uren')->state(function (Model $record): string {
                            return Activities::query()->where('projects_id', $record->id)->sum('hour_amount');
                        }),
                    ])
                    ->columns(3),
                RepeatableEntry::make('per_person')
                    ->label('Per persoon')
                    ->state(function (Model $record): array {
                        return self::calculateProfitPerPerson($record);
                    })
                    ->schema([
                        TextEntry::make('business')->label('Naam'),
                        TextEntry::make('hour_amount')->numeric()->label('Hoeveelheid uren'),
                        TextEntry::make('profit')->numeric()->label('Winst')->money('EUR'),
                    ])
                    ->columnSpanFull()
                    ->columns(4)
                    ->visible(fn (Model $record): bool => $record->activities->isNotEmpty()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewProject::class,
            Pages\EditProject::class,
            Pages\ManageProjectExpenses::class,
            Pages\ManageProjectActivities::class
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            'expenses' => Pages\ManageProjectExpenses::route('/{record}/expenses'),
            'activities' => Pages\ManageProjectActivities::route('/{record}/activities'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ProjectResource\Widgets\ProjectOverview::class,
        ];
    }

    public static function calculateProfit($record): string
    {
        return $record->price - $record->expenses->sum('price');
    }

    public static function calculateProfitPerPerson($record): array
    {
        $per_person = [];
        $persons = [];
        $projectId = $record->id;
        $totalHours = Activities::query()->where('projects_id', $projectId)->sum('hour_amount');
        foreach ($record->activities as $activity) {
            $persons[] = $activity->users_id;
        }

        $uniquePersons = array_unique($persons);
        foreach ($uniquePersons as $person) {
            $business = User::query()->find($person)->business;
            $hourAmount = Activities::query()->where('users_id', $person)->where('projects_id', $projectId)->sum('hour_amount');
            $netProfit = $record->price - $record->expenses->sum('price');
            $profit_per_person = $netProfit / $totalHours * $hourAmount;
            $per_person[] = ['business' => $business, 'hour_amount' => $hourAmount, 'profit' => $profit_per_person];
        }
        $record['per_person'] = $per_person;
        return $per_person;
    }
}
