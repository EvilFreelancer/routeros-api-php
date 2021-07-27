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
     * Code of error
     *
     * @var int
     */
    private $socket_err_num;

    /**
     * Description of socket error
     *
     * @var string
     */
    private $socket_err_str;

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
        $options = ['ssl' => $this->config('ssl_options')];

        // Default: Context for ssl
        $context = stream_context_create($options);

        // Default: Proto tcp:// but for ssl we need ssl://
        $proto = $this->config('ssl') ? 'ssl://' : '';

        // Initiate socket client
        $socket = @stream_socket_client(
            $proto . $this->config('host') . ':' . $this->config('port'),
            $this->socket_err_num,
            $this->socket_err_str,
            $this->config('timeout'),
            STREAM_CLIENT_CONNECT,
            $context
        );

        // Throw error is socket is not initiated
        if (false === $socket) {
            throw new ConnectException('Unable to establish socket session, ' . $this->socket_err_str);
        }

        //Timeout read
        stream_set_timeout($socket, $this->config('socket_timeout'));

        // Save socket to static variable
        $this->setSocket($socket);
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
