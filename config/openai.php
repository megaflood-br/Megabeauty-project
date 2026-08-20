<?php

declare(strict_types=1);

return [
    'api_key' => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'timeout' => (int) env('OPENAI_TIMEOUT', 30),
    'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 800),
    'temperature' => (float) env('OPENAI_TEMPERATURE', 0.2),
];
