<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ManageProjectActivities extends ManageRelatedRecords
{
    protected static string $resource = ProjectResource::class;

    protected static string $relationship = 'activities';

    public function getTitle(): string | Htmlable
    {
        $record = $this->getRecord();

        $recordHTML = $record instanceof Htmlable ? $record->toHtml() : $record;

        return "Beheer uren voor {$recordHTML->address}";
    }

    public function getBreadcrumb(): string
    {
        return 'Uren';
    }

    public static function getNavigationLabel(): string
    {
        return 'Uren overzicht';
    }

    public function form(Form $form): Form
    {
        return $form
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
                TextInput::make('hour_amount')->numeric()->label('Uren')->required(),
            ])
            ->columns(1);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                InfolistSection::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('users.business')->label('Bedrijf'),
                        TextEntry::make('hour_amount')->label('Uren'),
                        TextEntry::make('description')->label('Omschrijving')->default('Geen omshrijving.'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Datum')->date(),
                TextColumn::make('hour_amount')->label('Uren')
                    ->summarize(Sum::make()->label('Uren')),
                TextColumn::make('users.business')->searchable()->label('Bedrijf'),
            ])
            ->searchPlaceholder('Zoek op bedrijf')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Uren toevoegen'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Open'),
                Tables\Actions\EditAction::make()->label('Bewerk'),
                Tables\Actions\DeleteAction::make()->label('Verwijder'),
            ]);
    }
}
