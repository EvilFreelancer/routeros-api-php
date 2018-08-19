<?php

namespace RouterOS;

class Config
{
    /**
     * Address of Mikrotik Router
     * @var string
     */
    public $host;

    /**
     * Account's username
     * @var string
     */
    public $user;

    /**
     * Password
     * @var string
     */
    public $pass;

    /**
     * Number of port for access
     * @var int
     */
    public $port = Client::PORT;

    /**
     * Enable ssl support
     * @var bool
     */
    public $ssl = Client::SSL;

    /**
     * Default timeout
     * @var int
     */
    public $timeout = Client::TIMEOUT;

    /**
     * Count of attempts
     * @var int
     */
    public $attempts = Client::ATTEMPTS;

    /**
     * Delay between attempts
     * @var int
     */
    public $delay = Client::ATTEMPTS_DELAY;
}
