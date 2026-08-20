@php
    $client = filled($selectedClientId ?? null)
        ? \App\Models\Client::query()->find($selectedClientId)
        : ($appointment->client ?? null);
    $summary = \App\Support\Agenda\ClientAgendaSummary::make($client);
    $whatsappUrl = $client?->whatsappUrl();
@endphp

<aside class="mb-client-card">
    <div class="mb-client-card-profile">
        <div class="mb-client-card-avatar">
            {{ $client?->initials() ?? '?' }}
        </div>
        <div class="mb-client-card-identity">
            <div class="mb-client-card-name">{{ $client?->name ?? 'Cliente não selecionado' }}</div>
            @if ($client)
                <div class="mb-client-card-phone">{{ $client->formattedPhone() }}</div>
            @endif
        </div>
    </div>

    @if ($whatsappUrl)
        <a class="mb-client-card-whatsapp" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M20.5 3.5A11.9 11.9 0 0 0 12.1 0C5.5 0 .1 5.4.1 12c0 2.1.6 4.2 1.6 6L0 24l6.1-1.6A12 12 0 0 0 12.1 24C18.7 24 24 18.6 24 12.1c0-3.2-1.2-6.2-3.5-8.6zM12.1 22a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.6.9.9-3.5-.2-.4A9.8 9.8 0 0 1 2.1 12C2.1 6.5 6.6 2 12.1 2c2.6 0 5.1 1 7 2.9a9.8 9.8 0 0 1 2.9 7c0 5.5-4.5 10.1-9.9 10.1zm5.4-7.4c-.3-.1-1.8-.9-2.1-1s-.5-.1-.7.2c-.2.3-.8 1-.9 1.1s-.3.2-.6.1a8 8 0 0 1-2.4-1.5 8.9 8.9 0 0 1-1.6-2c-.2-.3 0-.4.1-.6l.5-.6c.2-.2.2-.3.3-.5s0-.4 0-.5-.7-1.7-1-2.3c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4s-1 1-1 2.4 1.1 2.8 1.2 3c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4s-.3-.2-.6-.3z"/></svg>
            Conversar
        </a>
    @endif

    <dl class="mb-client-card-list">
        <div>
            <dt>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Aniversário
            </dt>
            <dd>{{ $summary['birthday'] }}</dd>
        </div>
        <div>
            <dt>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                Cashback
            </dt>
            <dd>{{ $summary['cashback'] }}</dd>
        </div>
        <div>
            <dt>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                Crédito
            </dt>
            <dd>{{ $summary['credit'] }}</dd>
        </div>
        <div>
            <dt>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h12v4H6zM4 6h16l-1.5 14h-13L4 6z"/></svg>
                Comandas em aberto
            </dt>
            <dd>{{ $summary['openOrders'] }}</dd>
        </div>
        <div>
            <dt>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
                Pagamentos em aberto
            </dt>
            <dd>{{ $summary['openPayments'] }}</dd>
        </div>
    </dl>

    <section class="mb-client-card-section">
        <header>
            <span>Pacotes</span>
        </header>
        <p>Nenhum pacote</p>
    </section>

    <section class="mb-client-card-section">
        <header>
            <span>Assinaturas</span>
        </header>
        <p>Nenhuma assinatura</p>
    </section>

    <section class="mb-client-card-section">
        <header>
            <span>Anotações</span>
            @if ($client)
                @php
                    try {
                        $clientEditUrl = \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $client]);
                    } catch (\Throwable) {
                        $clientEditUrl = null;
                    }
                @endphp
                @if ($clientEditUrl)
                    <a href="{{ $clientEditUrl }}">+ Adicionar</a>
                @endif
            @endif
        </header>
        <p>{{ filled($client?->notes) ? $client->notes : 'Nenhuma anotação' }}</p>
    </section>
</aside>
