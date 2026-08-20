<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament-panels::form wire:submit="generate">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit">
                    Gerar com GPT-4o-mini
                </x-filament::button>
            </div>
        </x-filament-panels::form>

        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">Resultado</h3>
            <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200">{{ $result ?: 'A resposta da IA aparece aqui.' }}</pre>
        </div>
    </div>
</x-filament-panels::page>
