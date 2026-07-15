<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Pagamento Seguro - {{ $storeName }}</title>

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            background: #111827;
            color: #f9fafb;
            font-family: Arial, sans-serif;
        }

        #emis-shell {
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
        }

        #emis-topbar,
        #emis-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            background: #0f172a;
            border-color: #1f2937;
            padding: 0 18px;
        }

        #emis-topbar {
            height: 56px;
            border-bottom: 1px solid #1f2937;
        }

        #emis-footer {
            height: 36px;
            justify-content: center;
            border-top: 1px solid #1f2937;
            color: #9ca3af;
            font-size: 12px;
        }

        .emis-brand {
            display: flex;
            align-items: center;
            min-width: 0;
            gap: 12px;
            font-size: 14px;
            font-weight: 700;
        }

        .emis-logo {
            max-height: 34px;
            max-width: 160px;
            object-fit: contain;
        }

        .emis-summary {
            color: #d1d5db;
            font-size: 13px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            max-width: 50%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .emis-summary.is-updating {
            color: #f59e0b;
            font-weight: 700;
        }

        .emis-summary .emis-state-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #f59e0b;
            animation: emis-pulse 1.2s ease-in-out infinite;
        }

        @keyframes emis-pulse {
            0%, 100% {
                opacity: 0.4;
                transform: scale(0.95);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }
        }

        .emis-cancel {
            color: #d1d5db;
            border: 1px solid #374151;
            border-radius: 6px;
            padding: 7px 12px;
            text-decoration: none;
            font-size: 13px;
        }

        .emis-cancel:hover {
            border-color: #ef4444;
            color: #fecaca;
        }

        #emis-frame-area {
            position: relative;
            flex: 1;
            min-height: 0;
            overflow: hidden;
            background: #ffffff;
        }

        #emis-frame-wrap {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: #ffffff;
        }

        #emis-frame {
            position: absolute;
            top: 0;
            left: 0;
            border: 0;
            background: #ffffff;
            transform-origin: top left;
        }

        .emis-frame-fallback {
            position: absolute;
            left: 50%;
            bottom: 16px;
            transform: translateX(-50%);
            z-index: 6;
            display: none;
            align-items: center;
            justify-content: center;
            color: #111827;
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .emis-frame-fallback.show {
            display: inline-flex;
        }

        @media (max-width: 640px) {
            #emis-topbar {
                padding: 0 12px;
                height: 52px;
            }

            .emis-summary {
                max-width: 100%;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <div id="emis-shell">
        <div id="emis-topbar">
            <div class="emis-brand">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="emis-logo">
                @else
                    <span>{{ $storeName }}</span>
                @endif
            </div>

            <div class="emis-summary" id="emis-summary" role="status" aria-live="polite">
                <span class="emis-state-dot" aria-hidden="true"></span>
                <span id="emis-summary-text">Pedido #{{ $orderId }} - {{ $orderTotal }}</span>
            </div>

            <a href="{{ $cancelUrl }}" class="emis-cancel">Cancelar</a>
        </div>

        <div id="emis-frame-area">
            <div id="emis-frame-wrap">
                <iframe
                    id="emis-frame"
                    src="{{ $iframeSrc }}"
                    allow="payment; fullscreen"
                    allowfullscreen="true"
                    loading="eager"
                    referrerpolicy="strict-origin-when-cross-origin"
                    title="Pagamento EMIS Multicaixa Express"
                ></iframe>
            </div>

            <a
                id="emis-frame-fallback"
                href="{{ $iframeSrc }}"
                target="_blank"
                rel="noopener"
                class="emis-frame-fallback"
            >
                Abrir pagamento
            </a>
        </div>

        <div id="emis-footer">
            Processado pela EMIS - Multicaixa Express
        </div>
    </div>

    <script>
        (function () {
            var STATUS_URL = @json($statusUrl);
            var SUCCESS_URL = @json($successUrl);
            var CANCEL_URL = @json($cancelUrl);
            var processed = false;
            var polling = false;
            var completed = false;
            var statusTimer = null;
            var verificationTimer = null;
            var hasGatewayConfirmation = false;
            var flowState = 'iframe';

            var area = document.getElementById('emis-frame-area');
            var wrap = document.getElementById('emis-frame-wrap');
            var frame = document.getElementById('emis-frame');
            var summary = document.getElementById('emis-summary');
            var summaryText = document.getElementById('emis-summary-text');
            var frameFallback = document.getElementById('emis-frame-fallback');

            var EMIS_W = 480;
            var EMIS_H = 800;
            var fallbackShown = false;

            function scaleFrame() {
                var topbarHeight = document.getElementById('emis-topbar').offsetHeight;
                var footerHeight = document.getElementById('emis-footer').offsetHeight;
                var availW = Math.max(320, area.offsetWidth);
                var availH = Math.max(480, window.innerHeight - topbarHeight - footerHeight);
                var scale = Math.min(availW / EMIS_W, availH / EMIS_H, 1);

                frame.style.width = EMIS_W + 'px';
                frame.style.height = EMIS_H + 'px';
                frame.style.transform = 'scale(' + scale + ')';
                frame.style.transformOrigin = 'top left';
                frame.style.left = Math.max(0, (availW - EMIS_W * scale) / 2) + 'px';

                wrap.style.height = (EMIS_H * scale) + 'px';
            }

            function showFrameFallback(message) {
                if (fallbackShown) {
                    return;
                }

                fallbackShown = true;
                frameFallback.textContent = message;
                frameFallback.classList.add('show');
            }

            function updateSummary(message) {
                if (summaryText) {
                    summaryText.textContent = message;
                }

                if (summary) {
                    summary.classList.add('is-updating');
                }
            }

            function resetSummary() {
                if (summary) {
                    summary.classList.remove('is-updating');
                }
            }

            function showVerificationNotice(message) {
                if (completed || flowState === 'redirecting') {
                    return;
                }

                hasGatewayConfirmation = true;
                flowState = 'verifying';
                updateSummary(message);

                if (verificationTimer) {
                    window.clearTimeout(verificationTimer);
                }

                verificationTimer = window.setTimeout(function () {
                    if (flowState === 'verifying') {
                        pollOrderStatus();
                    }
                }, 1500);
            }

            function showResult(title, message, redirectUrl) {
                if (completed || flowState === 'iframe') {
                    return;
                }

                updateSummary(title);

                if (redirectUrl) {
                    completed = true;
                    flowState = 'redirecting';

                    if (statusTimer) {
                        window.clearInterval(statusTimer);
                    }

                    window.setTimeout(function () {
                        window.location.href = redirectUrl;
                    }, 2500);
                }
            }

            function pollOrderStatus() {
                if (polling || flowState !== 'verifying') {
                    return;
                }

                polling = true;

                statusTimer = window.setInterval(function () {
                    fetch(STATUS_URL, {
                        headers: {
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                        .then(function (response) {
                            if (! response.ok) {
                                throw new Error('status_unavailable');
                            }

                            return response.json();
                        })
                        .then(function (payload) {
                            if (payload.order_status === 'processing' || payload.order_status === 'completed') {
                                showResult(
                                    'Pagamento confirmado',
                                    'O pagamento foi confirmado com sucesso.',
                                    SUCCESS_URL
                                );

                                return;
                            }

                            if (payload.order_status === 'canceled') {
                                showResult(
                                    'Pagamento nao aprovado',
                                    'O pagamento nao foi aprovado. Pode tentar novamente.',
                                    CANCEL_URL
                                );
                            }
                        })
                        .catch(function () {});
                }, 3000);
            }

            window.addEventListener('resize', scaleFrame);
            window.addEventListener('orientationchange', scaleFrame);
            window.addEventListener('pageshow', scaleFrame);

            frame.addEventListener('load', function () {
                scaleFrame();
                frameFallback.classList.remove('show');
            });

            frame.addEventListener('error', function () {
                showFrameFallback('Nao foi possivel carregar o pagamento nesta janela.');
            });

            window.addEventListener('message', function (event) {
                if (event.origin.indexOf('pagamentonline.emis.co.ao') === -1 || processed) {
                    return;
                }

                processed = true;

                var data = event.data;
                var status = 'PENDING_WEBHOOK';

                if (typeof data === 'object' && data !== null && data.status) {
                    status = String(data.status).toUpperCase();
                }

                if (status === 'SUCCESS' || status === 'ACCEPTED') {
                    showVerificationNotice('Estamos a aguardar a confirmacao segura da EMIS.');

                    return;
                }

                if (status === 'REJECTED' || status === 'FAILED') {
                    showVerificationNotice('Estamos a aguardar a confirmacao final da EMIS.');

                    return;
                }

                showVerificationNotice('Estamos a aguardar a confirmacao segura da EMIS.');
            });

            scaleFrame();
        })();
    </script>
</body>
</html>
