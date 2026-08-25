<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $agent->name }} · {{ tenant()?->name }}</title>
    <style>
        :root {
            --brand: {{ $agent->avatar_color ?: '#059669' }};
            --bg: #f1f5f9;
            --card: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            background: var(--bg);
            color: var(--ink);
            min-height: 100vh;
        }
        .shell {
            max-width: 440px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--card);
            box-shadow: 0 10px 40px rgb(15 23 42 / 8%);
        }
        header {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 16px;
            background: var(--brand);
            color: white;
        }
        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            overflow: hidden;
            background: rgb(255 255 255 / 18%);
            display: grid;
            place-items: center;
            font-weight: 700;
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        header small { opacity: .85; }
        .log {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #f8fafc;
        }
        .bubble {
            max-width: 82%;
            padding: 10px 14px;
            border-radius: 18px;
            line-height: 1.4;
            font-size: 14px;
            white-space: pre-wrap;
        }
        .in { align-self: flex-start; background: white; box-shadow: 0 1px 2px rgb(15 23 42 / 6%); }
        .out { align-self: flex-end; background: var(--brand); color: white; border-bottom-right-radius: 6px; }
        form {
            display: flex;
            gap: 8px;
            padding: 12px;
            border-top: 1px solid #e2e8f0;
            background: white;
        }
        input[type=text] {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 12px 16px;
            font: inherit;
        }
        button {
            border: 0;
            background: var(--brand);
            color: white;
            border-radius: 999px;
            padding: 0 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:disabled { opacity: .6; cursor: wait; }
    </style>
</head>
<body>
    <div class="shell" id="chat">
        <header>
            <div class="avatar">
                @if ($agent->avatarUrl())
                    <img src="{{ $agent->avatarUrl() }}" alt="{{ $agent->name }}">
                @else
                    {{ $agent->initials() }}
                @endif
            </div>
            <div>
                <strong>{{ $agent->name }}</strong><br>
                <small>{{ $agent->role->label() }} · {{ tenant()?->name }}</small>
            </div>
        </header>
        <div class="log" id="log">
            <div class="bubble in">{{ $agent->greeting ?: 'Olá! Como posso te ajudar?' }}</div>
        </div>
        <form id="form">
            <input type="text" id="message" maxlength="2000" placeholder="Escreva sua mensagem" autocomplete="off" required>
            <button type="submit">Enviar</button>
        </form>
    </div>
    <script>
        const form = document.getElementById('form');
        const input = document.getElementById('message');
        const log = document.getElementById('log');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const endpoint = @json($postUrl);
        let sessionKey = localStorage.getItem('mb-agent-session') || crypto.randomUUID();
        localStorage.setItem('mb-agent-session', sessionKey);

        function addBubble(text, type) {
            const el = document.createElement('div');
            el.className = 'bubble ' + type;
            el.textContent = text;
            log.appendChild(el);
            log.scrollTop = log.scrollHeight;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            addBubble(text, 'out');
            input.value = '';
            form.querySelector('button').disabled = true;
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ message: text, session_key: sessionKey }),
                });
                const payload = await response.json();
                addBubble(payload.reply || 'Não consegui responder agora.', 'in');
                if (payload.session_key) {
                    sessionKey = payload.session_key;
                    localStorage.setItem('mb-agent-session', sessionKey);
                }
            } catch (error) {
                addBubble('Falha de conexão. Tente novamente.', 'in');
            } finally {
                form.querySelector('button').disabled = false;
                input.focus();
            }
        });
    </script>
</body>
</html>
