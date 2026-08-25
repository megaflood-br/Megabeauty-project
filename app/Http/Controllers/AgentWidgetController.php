<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiAgentChannel;
use App\Models\AiAgent;
use App\Models\AiAgentConversation;
use App\Services\Agents\AgentRuntime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class AgentWidgetController extends Controller
{
    public function show(Request $request, ?string $tenantSubdomain = null, ?string $agentSlug = null): View
    {
        $agent = $this->resolveAgent($agentSlug ?? (string) $request->route('agentSlug'));

        return view('agents.widget', [
            'agent' => $agent,
            'postUrl' => $request->url().'/mensagens',
        ]);
    }

    public function message(Request $request, AgentRuntime $runtime, ?string $tenantSubdomain = null, ?string $agentSlug = null): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'session_key' => ['nullable', 'string', 'max:80'],
        ]);

        $agent = $this->resolveAgent($agentSlug ?? (string) $request->route('agentSlug'));
        $sessionKey = $validated['session_key'] ?? (string) $request->session()->getId();

        $conversation = AiAgentConversation::query()->firstOrCreate(
            [
                'ai_agent_id' => $agent->id,
                'channel' => AiAgentChannel::Widget->value,
                'session_key' => $sessionKey,
            ],
            ['messages' => []],
        );

        $conversation->appendMessage([
            'role' => 'user',
            'content' => $validated['message'],
            'at' => now()->toIso8601String(),
        ]);

        try {
            $reply = $runtime->reply($agent, $conversation->fresh()?->chatTurns() ?? []);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'reply' => 'Tive um problema para consultar as informações agora. Pode tentar de novo em instantes?',
                'error' => true,
            ], 503);
        }

        $conversation->appendMessage([
            'role' => 'assistant',
            'content' => $reply->content,
            'at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'reply' => $reply->content,
            'agent' => [
                'name' => $agent->name,
                'role' => $agent->role->label(),
                'avatar_url' => $agent->avatarUrl(),
                'color' => $agent->avatar_color,
            ],
            'session_key' => $sessionKey,
        ]);
    }

    private function resolveAgent(string $slug): AiAgent
    {
        if (tenant_id() === null) {
            abort(404, 'Estabelecimento não identificado para o chat do agente.');
        }

        return AiAgent::query()
            ->where('is_active', true)
            ->where('public_slug', $slug)
            ->firstOrFail();
    }
}
