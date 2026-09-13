<x-filament-panels::page>
    @include('filament.agenda.edit-modal-styles')
    <style>
        .mb-agenda {
            --slot-h: {{ $this->timeline()->slotHeightPx() }}px;
            --time-w: 84px;
            --col-w: 168px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            overflow: auto;
            max-height: calc(100vh - 14rem);
        }
        .dark .mb-agenda {
            border-color: #374151;
            background: #111827;
        }
        .mb-agenda-head,
        .mb-agenda-body {
            display: grid;
            grid-template-columns: var(--time-w) repeat({{ $this->professionals()->count() ?: 1 }}, minmax(var(--col-w), 1fr));
            min-width: calc(var(--time-w) + {{ max(1, $this->professionals()->count()) }} * var(--col-w));
        }
        .mb-agenda-head {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }
        .dark .mb-agenda-head {
            background: #111827;
            border-bottom-color: #374151;
        }
        .mb-agenda-corner,
        .mb-agenda-time {
            position: sticky;
            left: 0;
            z-index: 21;
            background: #fff;
        }
        .dark .mb-agenda-corner,
        .dark .mb-agenda-time {
            background: #111827;
        }
        .mb-agenda-pro {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-left: 1px solid #e5e7eb;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }
        .dark .mb-agenda-pro {
            border-left-color: #374151;
        }
        .mb-agenda-avatar {
            width: 28px;
            height: 28px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .mb-agenda-body {
            position: relative;
        }
        .mb-agenda-time {
            z-index: 15;
            border-right: 1px solid #e5e7eb;
        }
        .dark .mb-agenda-time {
            border-right-color: #374151;
        }
        .mb-agenda-slot-label {
            height: var(--slot-h);
            padding: 0 10px;
            font-size: 12px;
            line-height: var(--slot-h);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }
        .mb-agenda-slot-label.is-hour {
            border-bottom-color: #d1d5db;
            font-weight: 700;
            color: #111827;
        }
        .dark .mb-agenda-slot-label {
            border-bottom-color: #1f2937;
            color: #6b7280;
        }
        .dark .mb-agenda-slot-label.is-hour {
            border-bottom-color: #4b5563;
            color: #d1d5db;
        }
        .mb-agenda-col {
            position: relative;
            border-left: 1px solid #e5e7eb;
            background-image: repeating-linear-gradient(
                to bottom,
                #ffffff,
                #ffffff calc(var(--slot-h) - 1px),
                #e5e7eb calc(var(--slot-h) - 1px),
                #e5e7eb var(--slot-h)
            );
        }
        .dark .mb-agenda-col {
            border-left-color: #374151;
            background-image: repeating-linear-gradient(
                to bottom,
                #111827,
                #111827 calc(var(--slot-h) - 1px),
                #374151 calc(var(--slot-h) - 1px),
                #374151 var(--slot-h)
            );
        }
        .mb-agenda-slots {
            display: flex;
            flex-direction: column;
        }
        .mb-agenda-slot {
            height: var(--slot-h);
            display: block;
        }
        .mb-agenda-slot:hover {
            background: rgba(16, 185, 129, 0.08);
        }
        .mb-agenda-off {
            position: absolute;
            left: 0;
            right: 0;
            background: repeating-linear-gradient(
                to bottom,
                rgba(229, 231, 235, 0.72),
                rgba(229, 231, 235, 0.72) calc(var(--slot-h) - 1px),
                rgba(209, 213, 219, 0.95) calc(var(--slot-h) - 1px),
                rgba(209, 213, 219, 0.95) var(--slot-h)
            );
            pointer-events: none;
            z-index: 1;
        }
        .dark .mb-agenda-off {
            background: repeating-linear-gradient(
                to bottom,
                rgba(31, 41, 55, 0.72),
                rgba(31, 41, 55, 0.72) calc(var(--slot-h) - 1px),
                rgba(55, 65, 81, 0.95) calc(var(--slot-h) - 1px),
                rgba(55, 65, 81, 0.95) var(--slot-h)
            );
        }
        .mb-agenda-block {
            position: absolute;
            left: 4px;
            right: 4px;
            z-index: 5;
            border: 0;
            border-radius: 6px;
            padding: 6px 8px;
            overflow: hidden;
            color: #fff;
            text-align: left;
            text-decoration: none;
            font: inherit;
            cursor: pointer;
            appearance: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12);
        }
        .mb-agenda-block.is-busy {
            background: #d1d5db;
            color: #374151;
        }
        .mb-agenda-block.is-active {
            background: #059669;
        }
        .mb-agenda-block-time {
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            opacity: 0.95;
        }
        .mb-agenda-block-client {
            font-size: 12px;
            font-weight: 600;
            line-height: 1.25;
            margin-top: 2px;
        }
        .mb-agenda-block-service {
            font-size: 11px;
            line-height: 1.25;
            opacity: 0.9;
            margin-top: 2px;
        }
    </style>

    <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-950 dark:text-white">
                {{ $this->formattedDate() }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                A coluna da esquerda acompanha o intervalo escolhido: {{ $this->slotIntervalLabel() }}.
            </p>
        </div>
        <div class="flex flex-wrap items-end gap-2">
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-200">Intervalo da grade</span>
                <select
                    wire:model.live="slotMinutes"
                    @disabled(! $this->canManageInterval())
                    class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900"
                >
                    @foreach ($this->slotIntervalOptions() as $minutes => $label)
                        <option value="{{ $minutes }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <x-filament::button color="gray" wire:click="previousDay">Dia anterior</x-filament::button>
            <x-filament::button color="gray" wire:click="today">Hoje</x-filament::button>
            <x-filament::button color="gray" wire:click="nextDay">Próximo dia</x-filament::button>
            <input
                type="date"
                wire:model.live="date"
                class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900"
            />
        </div>
    </div>

    <div class="mb-agenda">
        <div class="mb-agenda-head">
            <div class="mb-agenda-corner"></div>
            @forelse ($this->professionals() as $professional)
                <div class="mb-agenda-pro">
                    <span class="mb-agenda-avatar" style="background: {{ $professional->color ?: '#059669' }}">
                        {{ $this->initials($professional) }}
                    </span>
                    <span>{{ $professional->name }}</span>
                </div>
            @empty
                <div class="mb-agenda-pro text-sm font-normal text-gray-500">
                    Cadastre profissionais para montar a grade.
                </div>
            @endforelse
        </div>

        <div class="mb-agenda-body" style="height: {{ $this->timeline()->gridHeight() }}px;">
            <div class="mb-agenda-time">
                @foreach ($this->timeline()->slots() as $slot)
                    <div @class(['mb-agenda-slot-label', 'is-hour' => $slot['hour']])>
                        {{ $slot['label'] }}
                    </div>
                @endforeach
            </div>

            @foreach ($this->professionals() as $professional)
                @php($window = $this->workingWindow($professional))
                <div class="mb-agenda-col" style="height: {{ $this->timeline()->gridHeight() }}px;">
                    @if ($before = $this->offHoursStyle($window, 'before'))
                        <div class="mb-agenda-off" style="{{ $before }}"></div>
                    @endif
                    @if ($after = $this->offHoursStyle($window, 'after'))
                        <div class="mb-agenda-off" style="{{ $after }}"></div>
                    @endif

                    <div class="mb-agenda-slots relative z-[2]">
                        @foreach ($this->timeline()->slots() as $slot)
                            <a
                                class="mb-agenda-slot"
                                href="{{ $this->createUrl($professional, $slot['minutes']) }}"
                                title="Novo horário às {{ $slot['label'] }} com {{ $professional->name }}"
                            ></a>
                        @endforeach
                    </div>

                    @foreach ($this->appointmentsFor($professional) as $appointment)
                        @php($pos = $this->blockPosition($appointment))
                        <button
                            type="button"
                            wire:click="mountAction('editAppointment', { record: {{ $appointment->id }} })"
                            class="mb-agenda-block {{ $this->isOccupied($appointment) ? 'is-busy' : 'is-active' }}"
                            style="top: {{ $pos['top'] }}px; height: {{ $pos['height'] }}px; {{ $this->isOccupied($appointment) ? '' : 'background: '.($appointment->color ?: $appointment->professional?->color ?: '#059669').';' }}"
                            title="Editar agendamento de {{ $appointment->client?->name }}"
                        >
                            <div class="mb-agenda-block-time">
                                {{ $appointment->starts_at->format('H:i') }}
                                –
                                {{ $appointment->ends_at->format('H:i') }}
                            </div>
                            @if ($this->isOccupied($appointment))
                                <div class="mb-agenda-block-client">Ocupado</div>
                            @else
                                <div class="mb-agenda-block-client">{{ $appointment->client?->name }}</div>
                                <div class="mb-agenda-block-service">{{ $appointment->service?->name }}</div>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
