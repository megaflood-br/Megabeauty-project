<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceTableResource\Pages;

use App\Filament\Resources\PriceTableResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceTable extends CreateRecord
{
    protected static string $resource = PriceTableResource::class;
}
