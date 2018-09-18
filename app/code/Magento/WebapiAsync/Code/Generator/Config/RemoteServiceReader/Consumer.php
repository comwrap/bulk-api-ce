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
 * Remote service reader with auto generated configuration for queue_consumer.xml
 */
class Consumer implements \Magento\Framework\Config\ReaderInterface
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
     * Generate consumer configuration based on remote services declarations.
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

        $result[WebApiAsyncConfig::DEFAULT_CONSUMER_NAME] =
            [
                'name' => WebApiAsyncConfig::DEFAULT_CONSUMER_NAME,
                'queue' => WebApiAsyncConfig::DEFAULT_CONSUMER_NAME,
                'consumerInstance' => WebApiAsyncConfig::DEFAULT_CONSUMER_INSTANCE,
                'connection' => WebApiAsyncConfig::DEFAULT_CONSUMER_CONNECTION,
                'handlers'=>[],
                'maxMessages' => WebApiAsyncConfig::DEFAULT_CONSUMER_MAX_MESSAGE
            ];

        return $result;
    }
}
