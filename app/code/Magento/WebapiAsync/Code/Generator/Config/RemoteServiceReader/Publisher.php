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
 * Remote service reader with auto generated configuration for queue_publisher.xml
 */
class Publisher implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\ConnectionTypeResolver
     */
    private $connectionTypeResolver;
    /**
     * @var WebApiAsyncConfig
     */
    private $webapiAsyncConfig;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param WebApiAsyncConfig $webapiAsyncConfig
     * @param \Magento\Framework\MessageQueue\ConnectionTypeResolver $connectionResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        WebApiAsyncConfig $webapiAsyncConfig,
        ConnectionTypeResolver $connectionResolver,
        LoggerInterface $logger
    ) {
        $this->webapiAsyncConfig = $webapiAsyncConfig;
        $this->connectionTypeResolver = $connectionResolver;
        $this->logger = $logger;
    }

    /**
     * Generate publisher configuration based on remote services declarations
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
        $asyncServicesData = $this->webapiAsyncConfig->getServices();
        $result = [];
        foreach ($asyncServicesData as $serviceData) {
            $topicName = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_TOPIC];
            $result[$topicName] =
                [
                    'topic'       => $topicName,
                    'disabled'    => false,
                    'connections' => [
                        'amqp' => [
                            'name'     => 'amqp',
                            'exchange' => 'magento',
                            'disabled' => false,
                        ],
                    ],
                ];
        }

        return $result;
    }
}
