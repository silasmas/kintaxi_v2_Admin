@php
    $record = $getRecord();
    $core = json_decode((string) ($record?->smile_result_json ?? ''), true);
    $payload = json_decode((string) ($record?->callback_payload_json ?? ''), true);
@endphp

<div class="space-y-4 text-sm text-gray-950 dark:text-gray-100">
    @if (! is_array($core) || $core === [])
        <p class="text-gray-500 dark:text-gray-400">
            Aucune métadonnée Smile structurée (résultat vide ou encore côté mobile uniquement).
        </p>
    @else
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach (['SmileJobID', 'ResultText', 'ResultCode', 'ConfidenceValue', 'target'] as $k)
                @if (isset($core[$k]) && $core[$k] !== '' && $core[$k] !== null)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $k }}</dt>
                        <dd class="mt-0.5">
                            @if (is_scalar($core[$k]))
                                {{ $core[$k] }}
                            @else
                                <span class="text-xs text-gray-500">(objet — voir « Données techniques »)</span>
                            @endif
                        </dd>
                    </div>
                @endif
            @endforeach
        </dl>

        @if (array_key_exists('didSubmitDocumentVerificationJob', $core))
            <p class="rounded-md bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:bg-gray-800/80 dark:text-gray-300">
                Soumission verification document (app)&nbsp;: <strong>{{ $core['didSubmitDocumentVerificationJob'] ? 'oui' : 'non' }}</strong>
            </p>
        @endif
    @endif

    @if (is_array($payload))
        @php
            $jc = $payload['job_complete'] ?? null;
            $js = $payload['job_success'] ?? null;
        @endphp
        @if ($jc !== null || $js !== null)
            <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">Dernière notion de statut (callback ou fusion job_status)</p>
                <ul class="mt-1 list-inside list-disc text-xs text-gray-600 dark:text-gray-300">
                    @if ($jc !== null)
                        <li>job_complete : {{ $jc ? 'oui' : 'non' }}</li>
                    @endif
                    @if ($js !== null)
                        <li>job_success : {{ $js ? 'oui' : 'non' }}</li>
                    @endif
                </ul>
            </div>
        @endif
    @endif

    <p class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-100/90">
        Les chemins du type <code class="rounded bg-white/60 px-1 dark:bg-black/30">/data/user/0/…</code> sont des fichiers sur le téléphone de l’utilisateur&nbsp;: ils ne sont <strong>pas</strong> accessibles depuis cette interface.
        Les images affichées ici sont des <strong>URLs signées</strong> renvoyées par Smile ID (callback ou « Rafraîchir depuis Smile ID »).
    </p>
</div>
