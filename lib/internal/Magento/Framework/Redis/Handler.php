<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Redis;

use Cm\RedisSession\ConnectionFailedException;
use Cm\RedisSession\Handler\LoggerInterface;

class Handler
{
    /**
     * Default connection timeout
     */
    const DEFAULT_TIMEOUT = 2.5;

    /**
     * Default compression threshold
     */
    const DEFAULT_COMPRESSION_THRESHOLD = 2048;

    /**
     * Default compression library
     */
    const DEFAULT_COMPRESSION_LIBRARY = 'gzip';

    /**
     * Default log level
     */
    const DEFAULT_LOG_LEVEL = LoggerInterface::ALERT;

    /**
     * Maximum number of processes that can wait for a lock on one session
     */
    const DEFAULT_MAX_CONCURRENCY = 6;

    /**
     * Default host
     */
    const DEFAULT_HOST = '127.0.0.1';

    /**
     * Default port
     */
    const DEFAULT_PORT = 6379;

    /**
     * Default database
     */
    const DEFAULT_DATABASE = 0;

    /**
     * @var \Credis_Client
     */
    private $redis;

    /**
     * @var string
     */
    private $compressionThreshold;

    /**
     * @var string
     */
    private $compressionLibrary;

    /**
     * @var int
     */
    private $maxConcurrency;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     *
     * @throws ConnectionFailedException
     */
    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;

        $this->logger->setLogLevel($this->config->getLogLevel()
            ? : self::DEFAULT_LOG_LEVEL);
        $timeStart = microtime(true);

        // Database config
        $host = $this->config->getHost() ? : self::DEFAULT_HOST;
        $port = $this->config->getPort() ? : self::DEFAULT_PORT;
        $pass = $this->config->getPassword() ? : null;
        $timeout = $this->config->getTimeout() ? : self::DEFAULT_TIMEOUT;
        $persistent = $this->config->getPersistentIdentifier() ? : '';
        $dbNum = $this->config->getDatabase() ? : self::DEFAULT_DATABASE;

        // General config
        $this->compressionThreshold = $this->config->getCompressionThreshold()
            ? : self::DEFAULT_COMPRESSION_THRESHOLD;
        $this->compressionLibrary = $this->config->getCompressionLibrary()
            ? : self::DEFAULT_COMPRESSION_LIBRARY;
        $this->maxConcurrency = $this->config->getMaxConcurrency()
            ? : self::DEFAULT_MAX_CONCURRENCY;

        $this->redis = new \Credis_Client($host, $port, $timeout, $persistent, $dbNum, $pass);
        if ($this->hasConnection() == false) {
            throw new ConnectionFailedException('Unable to connect to Redis');
        }

        // Destructor order cannot be predicted
        $this->redis->setCloseOnDestruct(false);
        $this->log(
            sprintf(
                "%s initialized for connection to %s:%s after %.5f seconds",
                get_class($this),
                $this->redis->getHost(),
                $this->redis->getPort(),
                (microtime(true) - $timeStart)
            )
        );
    }

    /**
     * REdis Client
     * @return \Credis_Client
     */
    public function getClient(){
        return $this->redis;
    }

    /**
     * @param $msg
     * @param $level
     */
    private function log($msg, $level = LoggerInterface::DEBUG)
    {
        $this->logger->log("{$this->getPid()}: $msg", $level);
    }

    /**
     * Check Redis connection
     *
     * @return bool
     */
    public function hasConnection()
    {
        try {
            $this->redis->connect();
            $this->log("Connected to Redis");

            return true;
        } catch (\Exception $e) {
            $this->logger->logException($e);
            $this->log('Unable to connect to Redis');

            return false;
        }
    }

    /**
     * Overridden to prevent calling getLifeTime at shutdown
     *
     * @return bool
     */
    public function close()
    {
        $this->log("Closing connection");
        if ($this->redis) {
            $this->redis->close();
        }

        return true;
    }

    /**
     * Get pid
     *
     * @return string
     */
    private function getPid()
    {
        return gethostname() . '|' . getmypid();
    }

    /**
     * Check if pid exists
     *
     * @param $pid
     *
     * @return bool
     */
    private function pidExists($pid)
    {
        list($host, $pid) = explode('|', $pid);
        if (PHP_OS != 'Linux' || $host != gethostname()) {
            return true;
        }

        return @file_exists('/proc/' . $pid);
    }

    /**
     * Encode compressed data
     *
     * @param string $data
     * @return string
     */
    public function encodeData($data)
    {
        $originalDataSize = strlen($data);
        if ($this->compressionThreshold > 0 && $this->compressionLibrary != 'none' && $originalDataSize >= $this->compressionThreshold) {
            $this->log(sprintf("Compressing %s bytes with %s", $originalDataSize,$this->compressionLibrary));
            $timeStart = microtime(true);
            $prefix = ':'.substr($this->compressionLibrary,0,2).':';
            switch($this->compressionLibrary) {
                case 'snappy': $data = snappy_compress($data); break;
                case 'lzf':    $data = lzf_compress($data); break;
                case 'lz4':    $data = lz4_compress($data); $prefix = ':l4:'; break;
                case 'gzip':   $data = gzcompress($data, 1); break;
            }
            if($data) {
                $data = $prefix.$data;
                $this->log(
                    sprintf(
                        "Data compressed by %.1f percent in %.5f seconds",
                        ($originalDataSize == 0 ? 0 : (100 - (strlen($data) / $originalDataSize * 100))),
                        (microtime(true) - $timeStart)
                    )
                );
            } else {
                $this->log(
                    sprintf("Could not compress session data using %s", $this->compressionLibrary),
                    LoggerInterface::WARNING
                );
            }
        }
        return $data;
    }

    /**
     * Decode compressed data
     *
     * @param string $data
     * @return string
     */
    public function decodeData($data)
    {
        switch (substr($data,0,4)) {
            // asking the data which library it uses allows for transparent changes of libraries
            case ':sn:': $data = snappy_uncompress(substr($data,4)); break;
            case ':lz:': $data = lzf_decompress(substr($data,4)); break;
            case ':l4:': $data = lz4_uncompress(substr($data,4)); break;
            case ':gz:': $data = gzuncompress(substr($data,4)); break;
        }
        return $data;
    }
}
