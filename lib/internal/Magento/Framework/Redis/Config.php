<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Redis;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Redis session save handler
 */
class Config implements ConfigInterface
{
    const KEY_VALUE_STORAGE_NODE = 'key_value_storage';
    /**
     * Configuration path for log level
     */
    const PARAM_LOG_LEVEL = self::KEY_VALUE_STORAGE_NODE . '/redis/log_level';

    /**
     * Configuration path for host
     */
    const PARAM_HOST = self::KEY_VALUE_STORAGE_NODE . '/redis/host';

    /**
     * Configuration path for port
     */
    const PARAM_PORT = self::KEY_VALUE_STORAGE_NODE . '/redis/port';

    /**
     * Configuration path for database
     */
    const PARAM_DATABASE = self::KEY_VALUE_STORAGE_NODE . '/redis/database';

    /**
     * Configuration path for password
     */
    const PARAM_PASSWORD = self::KEY_VALUE_STORAGE_NODE . '/redis/password';

    /**
     * Configuration path for connection timeout
     */
    const PARAM_TIMEOUT = self::KEY_VALUE_STORAGE_NODE . '/redis/timeout';

    /**
     * Configuration path for persistent identifier
     */
    const PARAM_PERSISTENT_IDENTIFIER = self::KEY_VALUE_STORAGE_NODE . '/redis/persistent_identifier';

    /**
     * Configuration path for compression threshold
     */
    const PARAM_COMPRESSION_THRESHOLD = self::KEY_VALUE_STORAGE_NODE . '/redis/compression_threshold';

    /**
     * Configuration path for compression library
     */
    const PARAM_COMPRESSION_LIBRARY = self::KEY_VALUE_STORAGE_NODE . '/redis/compression_library';

    /**
     * Configuration path for maximum number of processes that can wait for a
     * lock on one session
     */
    const PARAM_MAX_CONCURRENCY = self::KEY_VALUE_STORAGE_NODE . '/redis/max_concurrency';

    /**
     * Configuration path for minimum session lifetime
     */
    const PARAM_MAX_LIFETIME = self::KEY_VALUE_STORAGE_NODE . '/redis/max_lifetime';

    /**
     * Configuration path for min
     */
    const PARAM_MIN_LIFETIME = self::KEY_VALUE_STORAGE_NODE . '/redis/min_lifetime';

    /**
     * Configuration path for lifetime of session for non-bots on the first
     * write
     */
    const PARAM_FIRST_LIFETIME = self::KEY_VALUE_STORAGE_NODE . '/redis/first_lifetime';

    /**
     * Configuration path for number of seconds to wait before trying to break
     * the lock
     */
    const PARAM_BREAK_AFTER = self::KEY_VALUE_STORAGE_NODE . '/redis/break_after';

    /**
     * Configuration path for comma separated list of sentinel servers
     */
    const PARAM_SENTINEL_SERVERS = self::KEY_VALUE_STORAGE_NODE . '/redis/sentinel_servers';

    /**
     * Configuration path for sentinel master
     */
    const PARAM_SENTINEL_MASTER = self::KEY_VALUE_STORAGE_NODE . '/redis/sentinel_master';

    /**
     * Configuration path for verify sentinel master flag
     */
    const PARAM_SENTINEL_VERIFY_MASTER = self::KEY_VALUE_STORAGE_NODE . '/redis/sentinel_verify_master';

    /**
     * Configuration path for number of sentinel connection retries
     */
    const PARAM_SENTINEL_CONNECT_RETRIES = self::KEY_VALUE_STORAGE_NODE . '/redis/sentinel_connect_retries';

    /**
     * Cookie lifetime config path
     */
    const XML_PATH_COOKIE_LIFETIME = 'web/cookie/cookie_lifetime';

    /**
     * Admin session lifetime config path
     */
    const XML_PATH_ADMIN_SESSION_LIFETIME = 'admin/security/session_lifetime';

    /**
     * Session max lifetime
     */
    const SESSION_MAX_LIFETIME = 31536000;

    /**
     * Try to break lock for at most this many seconds
     */
    const DEFAULT_FAIL_AFTER = 15;

    /**
     * Deployment config
     *
     * @var DeploymentConfig $deploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param State $appState
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        State $appState,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->appState = $appState;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogLevel()
    {
        return $this->deploymentConfig->get(self::PARAM_LOG_LEVEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->deploymentConfig->get(self::PARAM_HOST);
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->deploymentConfig->get(self::PARAM_PORT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase()
    {
        return $this->deploymentConfig->get(self::PARAM_DATABASE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->deploymentConfig->get(self::PARAM_PASSWORD);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout()
    {
        return $this->deploymentConfig->get(self::PARAM_TIMEOUT);
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentIdentifier()
    {
        return $this->deploymentConfig->get(self::PARAM_PERSISTENT_IDENTIFIER);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompressionThreshold()
    {
        return $this->deploymentConfig->get(self::PARAM_COMPRESSION_THRESHOLD);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompressionLibrary()
    {
        return $this->deploymentConfig->get(self::PARAM_COMPRESSION_LIBRARY);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxConcurrency()
    {
        return $this->deploymentConfig->get(self::PARAM_MAX_CONCURRENCY);
    }
}
