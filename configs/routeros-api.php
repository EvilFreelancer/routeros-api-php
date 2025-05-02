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

    'host' => env('ROUTEROS_HOST', '192.168.88.1'), // Address of Mikrotik RouterOS
    'user' => env('ROUTEROS_USER', 'admin'),        // Username
    'pass' => env('ROUTEROS_PASS'),                 // Password
    'port' => (int) env('ROUTEROS_PORT', 8728),     // RouterOS API port number for access (if not set use default or default with SSL if SSL enabled)

    /*
     |--------------------------------------------------------------------------
     | Change settings of stream
     |--------------------------------------------------------------------------
     |
     | Settings bellow need to advanced tune of your stream connection,
     | for example you may tune stream context options, or change timeout
     | of connection.
     |
     */

    'attempts'        => (int) env('ROUTEROS_ATTEMPTS', 10),                                      // Count of attempts to establish TCP session
    'delay'           => (int) env('ROUTEROS_DELAY', 1),                                          // Delay between attempts in seconds
    'timeout'         => (int) env('ROUTEROS_TIMEOUT', 10),                                       // Max timeout for instantiating connection with RouterOS
    'socket_timeout'  => (int) env('ROUTEROS_SOCKET_TIMEOUT', env('ROUTEROS_TIMEOUT', 30)),       // Max timeout for read from RouterOS
    'socket_blocking' => (bool) env('ROUTEROS_SOCKET_BLOCKING', true),                            // Set blocking mode on a socket stream

    // @see https://www.php.net/manual/en/context.socket.php
    'socket_options'  => [
        // Examples:
        // 'bindto' => '192.168.0.100:0',    // connect to the internet using the '192.168.0.100' IP
        // 'bindto' => '192.168.0.100:7000', // connect to the internet using the '192.168.0.100' IP and port '7000'
        // 'bindto' => '[2001:db8::1]:7000', // connect to the internet using the '2001:db8::1' IPv6 address and port '7000'
        // 'bindto' => '0:7000',             // connect to the internet using port '7000'
        // 'bindto' => '0:0',                // Forcing IPv4
        // 'bindto' => '[::]:0',             // Forcing IPv6
        // 'tcp_nodelay' => true,            // Setting this option to true will set SOL_TCP,NO_DELAY=1 appropriately, thus disabling the TCP Nagle algorithm.
    ],

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

    'ssl'         => (bool) env('ROUTEROS_SSL', false), // Enable ssl support (if port is not set this parameter must change default port to ssl port)

    // @see https://www.php.net/manual/en/context.ssl.php
    'ssl_options' => [
        'ciphers'           => env('ROUTEROS_SSL_CIPHERS', 'ADH:ALL'),              // ADH:ALL, ADH:ALL@SECLEVEL=0, ADH:ALL@SECLEVEL=1 ... ADH:ALL@SECLEVEL=5
        'verify_peer'       => (bool) env('ROUTEROS_SSL_VERIFY_PEER', false),       // Require verification of SSL certificate used.
        'verify_peer_name'  => (bool) env('ROUTEROS_SSL_VERIFY_PEER_NAME', false),  // Require verification of peer name.
        'allow_self_signed' => (bool) env('ROUTEROS_SSL_ALLOW_SELF_SIGNED', false), // Allow self-signed certificates. Requires verify_peer=true.
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

    'ssh_port'        => (int) env('ROUTEROS_SSH_PORT', 22),                             // Number of SSH port
    'ssh_timeout'     => (int) env('ROUTEROS_SSH_TIMEOUT', env('ROUTEROS_TIMEOUT', 30)), // Max timeout for read from RouterOS via SSH proto (for "/export" command)
    'ssh_private_key' => env('ROUTEROS_SSH_PRIVKEY', '~/.ssh/id_rsa.pub'),               // Full path to required private key

    /*
     |--------------------------------------------------------------------------
     | Optional connection settings of client
     |--------------------------------------------------------------------------
     |
     | Settings bellow need to advanced tune of your connection, for example
     | you may enable legacy mode by default.
     |
     */

    'legacy' => (bool) env('ROUTEROS_LEGACY', false), // Support of legacy login scheme (true - pre 6.43, false - post 6.43)

];
