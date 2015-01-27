<?php

/**
 * AppserverIo\RemoteMethodInvocation\RemoteMethod
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/rmi
 * @link      http://www.appserver.io
 */

namespace AppserverIo\RemoteMethodInvocation;

/**
 * The interface for the remote connection.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/rmi
 * @link      http://www.appserver.io
 */
class ContextSession implements SessionInterface
{

    /**
     * The connection instance.
     *
     * @var \AppserverIo\RemoteMethodInvocation\ConnectionInterface
     */
    protected $connection = null;

    /**
     * The session ID used for the connection.
     *
     * @var string
     */
    protected $sessionId = null;

    /**
     * Initializes the session with the connection.
     *
     * @param \AppserverIo\RemoteMethodInvocation\ConnectionInterface $connection The connection for the session
     */
    public function __construct(ConnectionInterface $connection)
    {
        // initialize the connection
        $this->connection = $connection;
        // check if already a session id exists in the session
        if (($this->sessionId = session_id()) == null) {
            // if not, create a unique ID
            $this->sessionId = uniqid();
        }
    }

    /**
     * Returns the ID of the session to use.
     *
     * @return string The session ID
     * @see \AppserverIo\RemoteMethodInvocation\SessionInterface::getSessionId()
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * The session ID to use.
     *
     * @param string $sessionId The session ID to use
     *
     * @return void
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Invokes the remote method over the connection.
     *
     * @param \AppserverIo\RemoteMethodInvocation\RemoteMethodInterface $remoteMethod The remote method call to invoke
     *
     * @return mixed the method return value
     * @see AppserverIo\RemoteMethodInvocation\SessionInterface::send()
     * @todo Refactor to replace check for 'setSession' method, e. g. check for an interface
     */
    public function send(RemoteMethodInterface $remoteMethod)
    {

        // invoke the remote method on the connection
        $response = $this->connection->send($remoteMethod);

        // check if a proxy has been returned
        if (method_exists($response, 'setSession')) {
            $response->setSession($this);
        }

        // return the response
        return $response;
    }

    /**
     * Creates a remote inital context instance.
     *
     * @return \AppserverIo\RemoteMethodInvocation\RemoteObjectInterface The proxy for the initial context
     * @see \AppserverIo\RemoteMethodInvocation\SessionInterface::createInitialContext()
     */
    public function createInitialContext()
    {
        $initialContext = new InitialContextProxy();
        $initialContext->setSession($this);
        return $initialContext;
    }
}
