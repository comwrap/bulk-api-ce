<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Psr\Log\LoggerInterface;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\ConnectionTypeResolver $connectionResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ConnectionTypeResolver $connectionResolver,
        LoggerInterface $logger
    ) {
        $this->connectionTypeResolver = $connectionResolver;
        $this->logger = $logger;
    }

    /**
     * Generate topology configuration based on remote services declarations
     *
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        try {
            $this->connectionTypeResolver->getConnectionType('amqp');
        } catch (\Exception $e) {
            $this->logger->debug(__('Connection type "amqp" not configured.'));
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
