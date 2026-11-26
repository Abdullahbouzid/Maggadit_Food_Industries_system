<?php

/*
|--------------------------------------------------------------------------
| Firebase Cloud Messaging (HTTP v1) credentials
|--------------------------------------------------------------------------
| Generated from the Firebase service-account json:
|   project_id   -> project_id
|   client_email -> client_email
|   private_key  -> private_key (keep the "-----BEGIN PRIVATE KEY-----" header)
*/
return [
    'project_id' => env('FCM_PROJECT_ID', 'maggaditfoodsystem'),
    'private_key' => env('FCM_PRIVATE_KEY', ''),
    'client_email' => env('FCM_CLIENT_EMAIL', ''),
];
