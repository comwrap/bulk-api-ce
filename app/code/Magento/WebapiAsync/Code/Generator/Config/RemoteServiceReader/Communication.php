<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\Communication\Config\ReflectionGenerator;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Psr\Log\LoggerInterface;

/**
 * Remote service reader with auto generated configuration for communication.xml
 */
class Communication implements \Magento\Framework\Config\ReaderInterface
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
     * @var \Magento\Framework\Communication\Config\ReflectionGenerator
     */
    private $reflectionGenerator;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\AsynchronousOperations\Model\ConfigInterface $webapiAsyncConfig
     * @param \Magento\Framework\Communication\Config\ReflectionGenerator $reflectionGenerator
     * @param \Magento\Framework\MessageQueue\ConnectionTypeResolver $connectionResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        WebApiAsyncConfig $webapiAsyncConfig,
        ReflectionGenerator $reflectionGenerator,
        ConnectionTypeResolver $connectionResolver,
        LoggerInterface $logger
    ) {
        $this->webapiAsyncConfig = $webapiAsyncConfig;
        $this->reflectionGenerator = $reflectionGenerator;
        $this->connectionTypeResolver = $connectionResolver;
        $this->logger = $logger;
    }

    /**
     * Generate communication configuration based on remote services declarations
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
            $serviceClass = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_INTERFACE];
            $serviceMethod = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_METHOD];

            $topicConfig = $this->reflectionGenerator->generateTopicConfigForServiceMethod(
                $topicName,
                $serviceClass,
                $serviceMethod,
                [
                    WebApiAsyncConfig::DEFAULT_HANDLER_NAME => [
                        CommunicationConfig::HANDLER_TYPE => $serviceClass,
                        CommunicationConfig::HANDLER_METHOD => $serviceMethod,
                    ],
                ]
            );
            $rewriteTopicParams = [
                CommunicationConfig::TOPIC_IS_SYNCHRONOUS => false,
                CommunicationConfig::TOPIC_RESPONSE => null,
            ];
            $result[$topicName] = array_merge($topicConfig, $rewriteTopicParams);
        }
        $result[WebApiAsyncConfig::SYSTEM_TOPIC_NAME] = WebApiAsyncConfig::SYSTEM_TOPIC_CONFIGURATION;

        return [CommunicationConfig::TOPICS => $result];
    }
}
