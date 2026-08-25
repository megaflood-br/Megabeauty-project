<x-filament-panels::page>
    @php
        $agent = $this->selectedAgent();
        $color = $agent?->avatar_color ?: '#059669';
    @endphp

    @if ($this->agents()->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">Nenhum agente ativo neste estabelecimento.</p>
            <div class="mt-4">
                <x-filament::button tag="a" :href="$this->createAgentUrl()">
                    Criar primeiro agente
                </x-filament::button>
            </div>
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-12">
            <div class="xl:col-span-4">
                <x-filament-panels::form wire:submit="send">
                    {{ $this->form }}

                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-filament::button type="submit" wire:loading.attr="disabled">
                            Enviar ao agente
                        </x-filament::button>
                        <x-filament::button color="gray" wire:click="resetConversation" type="button">
                            Nova conversa
                        </x-filament::button>
                    </div>
                </x-filament-panels::form>

                @if ($this->lastTraces !== [])
                    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm dark:border-emerald-900 dark:bg-emerald-950/40">
                        <p class="mb-2 font-semibold text-emerald-900 dark:text-emerald-100">O que o agente consultou</p>
                        <ul class="space-y-1 text-emerald-800 dark:text-emerald-200">
                            @foreach ($this->lastTraces as $trace)
                                <li>
                                    <span class="font-medium">{{ $trace['name'] }}</span>
                                    — {{ $trace['result_summary'] }}
                                    @if (! empty($trace['arguments']['query']))
                                        ({{ $trace['arguments']['query'] }})
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="xl:col-span-8">
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                        <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full text-sm font-semibold text-white" style="background: {{ $color }}">
                            @if ($agent?->avatarUrl())
                                <img src="{{ $agent->avatarUrl() }}" alt="{{ $agent->name }}" class="h-11 w-11 object-cover">
                            @else
                                {{ $agent?->initials() ?? 'IA' }}
                            @endif
                        </div>
                        <div>
                            <p class="font-semibold text-gray-950 dark:text-white">{{ $agent?->name ?? 'Agente' }}</p>
                            <p class="text-xs text-gray-500">{{ $agent?->role->label() }} · {{ $agent?->model }}</p>
                        </div>
                    </div>

                    <div class="h-[28rem] space-y-3 overflow-y-auto bg-slate-50 p-4 dark:bg-gray-950">
                        @forelse ($this->messages as $message)
                            @if (($message['role'] ?? '') === 'user')
                                <div class="flex justify-end">
                                    <div class="max-w-[80%] rounded-2xl rounded-br-md bg-emerald-600 px-4 py-2 text-sm text-white">
                                        {{ $message['content'] }}
                                    </div>
                                </div>
                            @elseif (($message['role'] ?? '') === 'assistant')
                                <div class="flex justify-start">
                                    <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-white px-4 py-2 text-sm text-gray-800 shadow-sm dark:bg-gray-800 dark:text-gray-100">
                                        {{ $message['content'] }}
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="flex h-full items-center justify-center">
                                <p class="text-sm text-gray-500">
                                    {{ $agent?->greeting ?: 'Envie uma mensagem para testar o atendimento.' }}
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
