<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceTableResource\Pages;

use App\Filament\Resources\PriceTableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPriceTables extends ListRecords
{
    protected static string $resource = PriceTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
