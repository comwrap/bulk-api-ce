<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;

/**
 * Remote service reader with auto generated configuration for queue_consumer.xml
 */
class Consumer implements \Magento\Framework\Config\ReaderInterface
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
     * Generate consumer configuration based on remote services declarations.
     * If connection amqp not configured return empty array.
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

        $result[WebApiAsyncConfig::DEFAULT_CONSUMER_NAME] =
            [
                'name' => WebApiAsyncConfig::DEFAULT_CONSUMER_NAME,
                'queue' => WebApiAsyncConfig::DEFAULT_CONSUMER_NAME,
                'consumerInstance' => WebApiAsyncConfig::DEFAULT_CONSUMER_INSTANCE,
                'connection' => WebApiAsyncConfig::DEFAULT_CONSUMER_CONNECTION,
                'maxMessages' => WebApiAsyncConfig::DEFAULT_CONSUMER_MAX_MESSAGE
            ];

        return $result;
    }
}
