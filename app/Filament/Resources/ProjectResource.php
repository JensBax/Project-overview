<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Activities;
use App\Models\Expenses;
use App\Models\Projects;
use App\Models\User;
use Faker\Provider\Text;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Resource
{
    protected static ?string $model = Projects::class;

    protected static ?string $navigationLabel = 'Projecten';
    protected static ?string $pluralLabel = 'Projecten';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
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
                TextInput::make('city')->label('Stad')->required(),
                TextInput::make('price')
                    ->numeric()
                    ->inputMode('decimal')
                    ->label('Klusprijs')
                    ->required(),
                TextInput::make('duration_in_days')
                    ->numeric()
                    ->label('Duur in dagen')
                    ->required(),
                Repeater::make('expenses')
                    ->label('Uitgaven')
                    ->relationship()
                    ->schema([
                        Select::make('users_id')
                            ->label('Selecteer bedrijf')
                            ->options(
                                User::all()->pluck('business', 'id')->toArray()
                            )
                            ->live()
                            ->required()
                            ->searchable(),
                        TextInput::make('description')->label('Omschrijving'),
                        TextInput::make('price')->numeric()->inputMode('decimal')->label('Prijs')->required(),
                    ])
                    ->addActionLabel('Uitgave toevoegen')
                    ->grid()
                    ->defaultItems(0),
                Repeater::make('activities')
                    ->label('Werkzaamheden')
                    ->relationship()
                    ->schema([
                        Select::make('users_id')
                            ->label('Selecteer bedrijf')
                            ->options(
                                User::all()->pluck('business', 'id')->toArray()
                            )
                            ->live()
                            ->required()
                            ->searchable(),
                        TextInput::make('description')->label('Omschrijving'),
                        TextInput::make('hour_amount')->numeric()->label('Hoeveelheid uren')->required(),
                    ])
                    ->addActionLabel('Uren toevoegen')
                    ->grid()
                    ->defaultItems(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('address')->label('Adres')
                    ->searchable(),
                TextColumn::make('users.name')->label('Opnemer'),
                TextColumn::make('updated_at')->hidden(),
            ])
            ->searchPlaceholder('Zoek op adres')
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('users.business')->label('Opnemer'),
                TextEntry::make('client')->label('Opdractgever'),
                TextEntry::make('description')->label('Opmerkingen')->columnSpanFull(),
                TextEntry::make('address')->label('Adres'),
                TextEntry::make('city')->label('Stad'),
                TextEntry::make('price')
                    ->numeric()
                    ->label('Klusprijs')
                    ->money('EUR'),
                TextEntry::make('duration_in_days')->label('Duur in dagen'),
                RepeatableEntry::make('expenses')
                    ->label('Uitgaven')
                    ->schema([
                        TextEntry::make('users.business')->label('Bedrijf'),
                        TextEntry::make('description')->label('Omschrijving'),
                        TextEntry::make('price')->numeric()->label('Prijs'),
                    ])
                    ->grid()
                    ->visible(fn (Model $record): bool => $record->expenses->isNotEmpty()),
                RepeatableEntry::make('activities')
                    ->label('Werkzaamheden')
                    ->schema([
                        TextEntry::make('users.business')->label('Bedrijf'),
                        TextEntry::make('description')->label('Omschrijving'),
                        TextEntry::make('hour_amount')->numeric()->label('Hoeveelheid uren'),
                    ])
                    ->grid()
                    ->visible(fn (Model $record): bool => $record->activities->isNotEmpty()),
                TextEntry::make('profit')->label('Winst')->state(function (Model $record): string {
                    return self::calculateProfit($record);
                }),
                RepeatableEntry::make('per_person')
                    ->label('Per persoon')
                    ->state(function (Model $record): array {
                        return self::calculateProfitPerPerson($record);
                    })
                    ->schema([
                        TextEntry::make('name')->label('Naam'),
                        TextEntry::make('hour_amount')->numeric()->label('Hoeveelheid uren'),
                        TextEntry::make('profit')->numeric()->label('Winst'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
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
        return $record->price . ' - ' . $record->expenses->sum('price') . ' = ' . '€' . $record->price - $record->expenses->sum('price');
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
            $userName = User::query()->find($person)->name;
            $hourAmount = Activities::query()->where('users_id', $person)->where('projects_id', $projectId)->sum('hour_amount');
            $netProfit = $record->price - $record->expenses->sum('price');
            $profit_per_person = $netProfit / $totalHours * $hourAmount;
            $per_person[] = ['name' => $userName, 'hour_amount' => $hourAmount, 'profit' => $profit_per_person];
        }
        $record['per_person'] = $per_person;
        return $per_person;
    }
}
