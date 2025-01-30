<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProjectResource;
use App\Models\Projects;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class PersonalProjects extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Opgenomen projecten';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Projects::query()
                    ->where('users_id', auth()->id())
                    ->orderBy('updated_at', 'desc')
            )
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
            ->defaultPaginationPageOption(5)
            ->searchPlaceholder('Zoek op adres')
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->url(fn (Projects $record): string => ProjectResource::getUrl('view', ['record' => $record]))
            ]);
    }
}
