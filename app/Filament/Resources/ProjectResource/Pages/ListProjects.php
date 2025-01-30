<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Projecten';

    public function getBreadcrumb(): string
    {
        return 'lijst';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nieuw project'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Lopend' => Tab::make()->query(fn ($query) => $query->where('is_finished', false)),
            'Afgerond' => Tab::make()->query(fn ($query) => $query->where('is_finished', true)),
            null => Tab::make('Alle projecten'),
        ];
    }
}
