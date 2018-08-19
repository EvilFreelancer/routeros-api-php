<?php

namespace RouterOS\Interfaces;

interface ClientInterface
{
    const PORT = 8728;
    const PORT_SSL = 8729;
    const SSL = false;
    const TIMEOUT = 1;
    const ATTEMPTS = 10;
    const ATTEMPTS_DELAY = 1;
}
