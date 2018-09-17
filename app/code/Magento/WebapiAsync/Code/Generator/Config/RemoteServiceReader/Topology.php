<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;

/**
 * Remote service reader with auto generated configuration for queue_topology.xml
 */
class Topology implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\ConnectionTypeResolver
     */
    private $connectionTypeResolver;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\ConnectionTypeResolver $connectionTypeResolver
     */
    public function __construct(
        ConnectionTypeResolver $connectionTypeResolver
    ) {
        $this->connectionTypeResolver = $connectionTypeResolver;
    }

    /**
     * Generate topology configuration based on remote services declarations
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        try {
            $this->connectionTypeResolver->getConnectionType('amqp');
        } catch (\Exception $e) {
            return [];
        }

        $bindings = [];
        $bindings[WebApiAsyncConfig::DEFAULT_CONSUMER_NAME] = [
            'id' => WebApiAsyncConfig::DEFAULT_CONSUMER_NAME,
            'topic' => 'async.#',
            'destinationType' => 'queue',
            'destination' => WebApiAsyncConfig::DEFAULT_CONSUMER_NAME,
            'disabled' => false,
            'arguments' => [],
        ];

        $result = [
            'magento-async--amqp' =>
                [
                    'name' => 'magento',
                    'type' => 'topic',
                    'connection' => 'amqp',
                    'durable' => true,
                    'autoDelete' => false,
                    'arguments' => [],
                    'internal' => false,
                    'bindings' => $bindings,
                ],
        ];

        return $result;
    }
}
