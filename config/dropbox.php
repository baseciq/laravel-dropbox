<?php

return [

    /*
    * Set the client id, known as "App key" in dropbox app console
    */
    'clientId' => env('DROPBOX_CLIENT_ID'),

    /*
    * Set the client secret, known as "App secret" in dropbox app console
    */
    'clientSecret' => env('DROPBOX_SECRET_ID'),

    /*
    * Set the url to trigger the oauth process this url should call return Dropbox::connect();
    *
    * NOTE: This url must be added to the "Redirect URIs" in dropbox app console.
    */
    'redirectUri' => env('DROPBOX_OAUTH_URL'),

    /*
    * Set the url to redirect once authenticated, for example your app dashboard or page where user starts using dropbox features
    */
    'landingUri' => env('DROPBOX_LANDING_URL', '/'),

    /**
     * Set access token, when set will bypass the oauth2 process
     */
    'accessToken' => env('DROPBOX_ACCESS_TOKEN', ''),

    /**
     * Set access type, options are offline and online
     * Offline - will return a short-lived access_token and a long-lived
     * refresh_token that can be used to request a new short-lived access
     * token as long as a user's approval remains valid.
     *
     * Online - will return a short-lived access_token
     */
    'accessType' => env('DROPBOX_ACCESS_TYPE', 'offline'),

    /*
    set the scopes to be used
    */
    'scopes' => 'account_info.read files.metadata.write files.metadata.read files.content.write files.content.read',
];
