<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Secret
    |--------------------------------------------------------------------------
    */
    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Keys
    |--------------------------------------------------------------------------
    */
    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT time to live (dakika)
    |--------------------------------------------------------------------------
    | Token geçerlilik süresi. 90 dakika (1.5 saat) olarak ayarlandı.
    | Frontend’te 55 dakikada bir refresh işlemi bu süreyle uyumludur.
    */
    'ttl' => env('JWT_TTL', 90),

    /*
    |--------------------------------------------------------------------------
    | Refresh time to live (dakika)
    |--------------------------------------------------------------------------
    | Token yenilenebilirlik süresi. 2880 dakika = 2 gün.
    | Kullanıcı bu süre içinde aktifse yeni token alabilir.
    */
    'refresh_ttl' => env('JWT_REFRESH_TTL', 2880),

    /*
    |--------------------------------------------------------------------------
    | JWT hashing algorithm
    |--------------------------------------------------------------------------
    */
    'algo' => env('JWT_ALGO', Tymon\JWTAuth\Providers\JWT\Provider::ALGO_HS256),

    /*
    |--------------------------------------------------------------------------
    | Required Claims
    |--------------------------------------------------------------------------
    */
    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

    /*
    |--------------------------------------------------------------------------
    | Persistent Claims
    |--------------------------------------------------------------------------
    */
    'persistent_claims' => [
        // Token yenilenirken saklanacak özel claim’ler eklenebilir.
        // Örnek: 'role'
    ],

    /*
    |--------------------------------------------------------------------------
    | Lock Subject
    |--------------------------------------------------------------------------
    */
    'lock_subject' => true,

    /*
    |--------------------------------------------------------------------------
    | Leeway (saniye)
    |--------------------------------------------------------------------------
    | Sunucular arasında zaman farkı varsa küçük tolerans süresi tanımlar.
    */
    'leeway' => env('JWT_LEEWAY', 0),

    /*
    |--------------------------------------------------------------------------
    | Blacklist Enabled
    |--------------------------------------------------------------------------
    | Çıkış yapılan token’ların geçersiz olmasını sağlar.
    */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Blacklist Grace Period
    |--------------------------------------------------------------------------
    | Aynı anda yapılan isteklerde token çakışmasını önler.
    */
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /*
    |--------------------------------------------------------------------------
    | Cookies encryption
    |--------------------------------------------------------------------------
    | Cookie içeriği Laravel tarafından çözülmeli.
    | decrypt_cookies = true olmalı.
    | Ayrıca EncryptCookies.php içinde:
    | protected $except = ['token'];
    */
    'decrypt_cookies' => true,

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
    ],

];
