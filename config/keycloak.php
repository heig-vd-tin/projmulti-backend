<?php

return [
    'realm_public_key' => env('KEYCLOAK_REALM_PUBLIC_KEY'),
    'user_provider_credential' => env('KEYCLOAK_USER_PROVIDER_CREDENTIAL', 'email'),
    'token_principal_attribute' => env('KEYCLOAK_TOKEN_PRINCIPAL_ATTRIBUTE', 'email'),
    // Développement local uniquement (APP_ENV=local et APP_DEBUG=true) : utilisateur connecté d'office.
    'dev_user_id' => env('ID_USER'),
];
