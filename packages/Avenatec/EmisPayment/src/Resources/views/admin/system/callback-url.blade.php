@php
    $callbackUrl = app(\Avenatec\EmisPayment\Payment\EmisPayment::class)->getWebhookUrl();
@endphp

<div class="mb-4 last:!mb-0">
    <label class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
        {{ $field->getTitle() }}
    </label>

    <div class="flex gap-2">
        <input
            type="text"
            value="{{ $callbackUrl }}"
            readonly
            class="w-full cursor-copy rounded-md border bg-gray-50 px-3 py-2.5 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300"
            onclick="this.select()"
        />

        <a
            href="{{ $callbackUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="secondary-button whitespace-nowrap px-4 py-2.5"
        >
            Abrir
        </a>
    </div>

    <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
        URL publica gerada automaticamente com base em APP_URL. Deve estar acessivel pela EMIS sem autenticacao e sem bloqueio do Cloudflare.
    </p>
</div>
