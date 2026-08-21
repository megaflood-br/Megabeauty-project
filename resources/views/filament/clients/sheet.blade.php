@php
    $tabs = $this->clientSheetTabs();
    $active = $this->clientTab;
    $record = $this->clientSheetRecord();
@endphp

@include('filament.clients.sheet-styles')

<div class="mb-client-sheet">
    <div class="mb-client-sheet-header">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M20 21a8 8 0 0 0-16 0"/>
            <circle cx="12" cy="8" r="4"/>
        </svg>
        <span>{{ $this->clientSheetTitle() }}</span>
    </div>

    <div class="mb-client-sheet-split">
        <nav class="mb-client-nav" aria-label="Seções do cliente">
            @foreach ($tabs as $key => $tab)
                <button
                    type="button"
                    wire:click="setClientTab('{{ $key }}')"
                    @class(['is-active' => $active === $key])
                >
                    <span>{{ $tab['label'] }}</span>
                    @if (filled($tab['badge']))
                        <span class="mb-client-nav-badge">{{ $tab['badge'] }}</span>
                    @endif
                </button>
            @endforeach
        </nav>

        <div class="mb-client-sheet-main">
            <div @class(['hidden' => $active !== 'cadastro'])>
                {{ $this->form }}
            </div>

            @if ($active === 'agendamentos')
                <div class="mb-client-panel">
                    <h3>Agendamentos</h3>
                    @php($appointments = $this->clientSheetAppointments())
                    @if ($appointments->isEmpty())
                        <p>{{ $record ? 'Nenhum agendamento para este cliente.' : 'Salve o cliente para ver os agendamentos.' }}</p>
                    @else
                        <div class="mb-client-panel-list">
                            @foreach ($appointments as $appointment)
                                <article>
                                    <strong>{{ $appointment->starts_at?->format('d/m/Y H:i') }}</strong>
                                    · {{ $appointment->service?->name }}
                                    · {{ $appointment->professional?->name }}
                                    · {{ $appointment->status?->label() }}
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif ($active === 'debitos')
                <div class="mb-client-panel">
                    <h3>Débitos</h3>
                    <p>Em aberto: {{ $this->clientSheetOpenDebits() }}</p>
                </div>
            @elseif ($active === 'creditos')
                <div class="mb-client-panel">
                    <h3>Créditos</h3>
                    <p>Em aberto: {{ $this->clientSheetOpenCredits() }}</p>
                </div>
            @elseif ($active === 'cashback')
                <div class="mb-client-panel">
                    <h3>Cashback</h3>
                    <p>R$ 0,00</p>
                </div>
            @elseif ($active === 'anotacoes')
                <div class="mb-client-panel">
                    <h3>Anotações</h3>
                    <p>{{ filled($record?->notes) ? $record->notes : 'Nenhuma anotação.' }}</p>
                </div>
            @elseif ($active !== 'cadastro')
                <div class="mb-client-panel">
                    <h3>{{ $tabs[$active]['label'] ?? 'Seção' }}</h3>
                    <p>Em breve nesta ficha do cliente.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="mb-client-sheet-footer" @class(['hidden' => $active !== 'cadastro'])>
        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </div>
</div>
