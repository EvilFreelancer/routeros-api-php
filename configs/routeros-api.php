<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Connection details
     |--------------------------------------------------------------------------
     |
     | Here you may specify different information about your router, like
     | hostname (or ip-address), username, password, port and ssl mode.
     |
     | SSH port should be set if you want to use "/export" command.
     |
     */

    'host'     => '192.168.88.1', // Address of Mikrotik RouterOS
    'user'     => 'admin',        // Username
    'pass'     => null,           // Password
    'port'     => 8728,           // RouterOS API port number for access (if not set use default or default with SSL if SSL enabled)
    'ssh_port' => 22,             // Number of SSH port

    /*
     |--------------------------------------------------------------------------
     | SSL settings
     |--------------------------------------------------------------------------
     |
     | Settings of SSL connection, if disabled then other parameters will
     | be skipped.
     |
     | @link https://wiki.mikrotik.com/wiki/Manual:API-SSL
     | @link https://www.openssl.org/docs/man1.1.1/man3/SSL_CTX_set_security_level.html
     |
     */

    'ssl'         => false,     // Enable ssl support (if port is not set this parameter must change default port to ssl port)

    // @see https://www.php.net/manual/en/context.ssl.php
    'ssl_options' => [
        'ciphers'           => 'ADH:ALL', // ADH:ALL, ADH:ALL@SECLEVEL=0, ADH:ALL@SECLEVEL=1 ... ADH:ALL@SECLEVEL=5
        'verify_peer'       => false,     // Require verification of SSL certificate used.
        'verify_peer_name'  => false,     // Require verification of peer name.
        'allow_self_signed' => false,     // Allow self-signed certificates. Requires verify_peer=true.
    ],

    /*
     |--------------------------------------------------------------------------
     | Optional connection settings of client
     |--------------------------------------------------------------------------
     |
     | Settings bellow need to advanced tune of your connection, for example
     | you may enable legacy mode by default, or change timeout of connection.
     |
     */

    'legacy'         => false, // Support of legacy login scheme (true - pre 6.43, false - post 6.43)
    'timeout'        => 10,    // Max timeout for instantiating connection with RouterOS
    'socket_timeout' => 30,    // Max timeout for read from RouterOS
    'ssh_timeout'    => 30,    // Max timeout for read from RouterOS via SSH proto (for "/export" command)
    'attempts'       => 10,    // Count of attempts to establish TCP session
    'delay'          => 1,     // Delay between attempts in seconds

];
