<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Project overzicht';

    public function getBreadcrumb(): string
    {
        return 'overzicht';
    }
}
