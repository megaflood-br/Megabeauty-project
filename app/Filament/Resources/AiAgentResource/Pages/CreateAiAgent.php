<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiAgentResource\Pages;

use App\Filament\Resources\AiAgentResource;
use App\Models\AiAgent;
use Filament\Resources\Pages\CreateRecord;

class CreateAiAgent extends CreateRecord
{
    protected static string $resource = AiAgentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! AiAgent::query()->exists()) {
            $data['is_default'] = true;
        }

        return $data;
    }
}
