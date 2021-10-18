<?php

namespace RouterOS;

use RouterOS\Exceptions\ConnectException;

trait SocketTrait
{
    /**
     * Socket resource
     *
     * @var resource|null
     */
    private $socket;

    /**
     * Initiate socket session
     *
     * @return void
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConnectException
     * @throws \RouterOS\Exceptions\ConfigException
     */
    private function openSocket(): void
    {
        $options = [];

        // Pass SSL options
        $sslOptions = $this->config('ssl_options');
        if (!empty($sslOptions)) {
            $options['ssl'] = $sslOptions;
        }

        // Pass socket context options, eg.: bindto, tcp_nodelay
        $socketOptions = $this->config('socket_options');
        if (!empty($socketOptions)) {
            $options['socket'] = $socketOptions;
        }

        // Default: Context for ssl
        $context = stream_context_create($options);

        // Default: Proto tcp:// but for ssl we need ssl://
        $proto = $this->config('ssl') ? 'ssl://' : '';

        // Initiate socket client
        $socketClient = @stream_socket_client(
            $proto . $this->config('host') . ':' . $this->config('port'),
            $socketErrorNumber,
            $socketErrorString,
            $this->config('timeout'),
            STREAM_CLIENT_CONNECT,
            $context
        );

        // Throw error is socket is not initiated
        if (false === $socketClient) {
            throw new ConnectException('Unable to establish socket session, ' . $socketErrorString, $socketErrorNumber);
        }

        // Set blocking mode on a stream
        if ($this->config('socket_blocking') === true){
            stream_set_blocking($socketClient, true);
        }

        // Timeout read
        stream_set_timeout($socketClient, $this->config('socket_timeout'));

        // Save socket to static variable
        $this->setSocket($socketClient);
    }

    /**
     * Close socket session
     *
     * @return bool
     */
    private function closeSocket(): bool
    {
        return fclose($this->socket);
    }

    /**
     * Save socket resource to static variable
     *
     * @param resource $socket
     *
     * @return  void
     */
    private function setSocket($socket): void
    {
        $this->socket = $socket;
    }

    /**
     * Return socket resource if is exist
     *
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }
}
