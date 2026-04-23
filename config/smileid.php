<?php

return [

    'partner_id' => env('SMILEID_PARTNER_ID', ''),

    'api_key' => env('SMILEID_API_KEY', ''),

    /**
     * 0 = sandbox, 1 = production (cf. SDK Smile ID).
     */
    'sid_server' => env('SMILEID_SID_SERVER', '0'),

    'callback_enabled' => env('SMILEID_CALLBACK_ENABLED', true),

    /**
     * Vérification HMAC du champ signature (recommandé en prod).
     */
    'verify_signature' => env('SMILEID_VERIFY_SIGNATURE', true),

    /**
     * Endpoint optionnel pour pousser une mise à jour de statut vers un service Smile ID interne/intégré.
     * Si null, seule la base locale est mise à jour.
     */
    'status_update_endpoint' => env('SMILEID_STATUS_UPDATE_ENDPOINT'),
];
