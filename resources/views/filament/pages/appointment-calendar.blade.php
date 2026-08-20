<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-950 dark:text-white">
                    {{ $this->formattedDate() }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Horários do estabelecimento no dia selecionado.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-filament::button color="gray" wire:click="previousDay">
                    Dia anterior
                </x-filament::button>
                <x-filament::button color="gray" wire:click="today">
                    Hoje
                </x-filament::button>
                <x-filament::button color="gray" wire:click="nextDay">
                    Próximo dia
                </x-filament::button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Data</label>
                <input
                    type="date"
                    wire:model.live="date"
                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Profissional</label>
                <select
                    wire:model.live="professionalId"
                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900"
                >
                    <option value="">Todos</option>
                    @foreach ($this->professionals() as $professional)
                        <option value="{{ $professional->id }}">{{ $professional->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            @forelse ($this->appointments() as $appointment)
                <a
                    href="{{ $this->appointmentUrl($appointment) }}"
                    class="flex flex-col gap-1 border-b border-gray-100 px-4 py-3 last:border-b-0 hover:bg-emerald-50/60 dark:border-gray-800 dark:hover:bg-gray-800 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-24 shrink-0 text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                            {{ $appointment->starts_at->format('H:i') }}
                            –
                            {{ $appointment->ends_at->format('H:i') }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-950 dark:text-white">
                                {{ $appointment->client?->name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $appointment->service?->name }}
                                ·
                                {{ $appointment->professional?->name }}
                            </div>
                        </div>
                    </div>
                    <span @class([
                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                        'bg-gray-100 text-gray-700' => $appointment->status->value === 'scheduled',
                        'bg-sky-100 text-sky-800' => $appointment->status->value === 'confirmed',
                        'bg-amber-100 text-amber-800' => $appointment->status->value === 'in_progress',
                        'bg-emerald-100 text-emerald-800' => $appointment->status->value === 'completed',
                        'bg-rose-100 text-rose-800' => in_array($appointment->status->value, ['cancelled', 'no_show'], true),
                    ])>
                        {{ $this->statusLabel($appointment->status) }}
                    </span>
                </a>
            @empty
                <div class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Nenhum agendamento neste dia.
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
