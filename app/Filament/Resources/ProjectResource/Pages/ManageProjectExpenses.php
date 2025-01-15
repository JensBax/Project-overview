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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ManageProjectExpenses extends ManageRelatedRecords
{
    protected static string $resource = ProjectResource::class;

    protected static string $relationship = 'expenses';

    public function getTitle(): string | Htmlable
    {
        $record = $this->getRecord();

        $recordHTML = $record instanceof Htmlable ? $record->toHtml() : $record;

        return "Beheer uitgaven voor {$recordHTML->address}";
    }

    public function getBreadcrumb(): string
    {
        return 'Uitgaven';
    }

    public static function getNavigationLabel(): string
    {
        return 'Uitgaven overzicht';
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
                TextInput::make('price')->numeric()->inputMode('decimal')->label('Prijs')->required(),
                TextInput::make('invoice')->label('Factuurnummer'),
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
                        TextEntry::make('description')->label('Omschrijving')->default('Geen omshrijving.'),
                        TextEntry::make('price')->label('Prijs')->money('EUR'),
                        TextEntry::make('invoice')->label('Factuurnummer')->default('Geen factuurnummer.'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')->label('Omschrijving')->default('Geen omshrijving.'),
                TextColumn::make('price')->label('Prijs')->money('EUR'),
                TextColumn::make('users.business')->searchable()->label('Bedrijf'),
            ])
            ->searchPlaceholder('Zoek op bedrijf')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Uitgave toevoegen'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Open'),
                Tables\Actions\EditAction::make()->label('Bewerk'),
                Tables\Actions\DeleteAction::make()->label('Verwijder'),
            ]);
    }
}
