<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\ClientResource\Concerns\HasClientSheet;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;

class EditClient extends EditRecord
{
    use HasClientSheet;

    protected static string $resource = ClientResource::class;

    protected static string $view = 'filament.resources.clients.pages.edit-client';

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::SevenExtraLarge;
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::End;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction()->label('Cancelar'),
            $this->getSaveFormAction()->label('Salvar'),
        ];
    }
}
