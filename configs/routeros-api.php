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
    'ssl'      => false,          // Enable ssl support (if port is not set this parameter must change default port to ssl port)
    'ssh_port' => 22,             // Number of SSH port

    /*
     |--------------------------------------------------------------------------
     | Optional connection settings of client
     |--------------------------------------------------------------------------
     |
     | Settings bellow need to advanced tune of your connection, for example
     | you may enable legacy mode by default, or change timeout of connection.
     |
     */

    'legacy'   => false, // Support of legacy login scheme (true - pre 6.43, false - post 6.43)
    'timeout'  => 10,    // Max timeout for answer from RouterOS
    'attempts' => 10,    // Count of attempts to establish TCP session
    'delay'    => 1,     // Delay between attempts in seconds

];
