<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Redis;

use Magento\Framework\KeyValue\StorageInterface;
use Magento\Framework\Redis\Handler;
use Credis_Client;

/**
 * Redis implementation StorageInterface to persist key-value data in Magento
 */
class Storage implements StorageInterface
{
    /**
     * @var \Magento\Framework\Redis\Handler
     */
    private $redisHandler;

    /**
     * @var \Credis_Client
     */
    private $redisClient;

    /**
     * Init dependencies.
     *
     * @param \Credis_Client $credisClient
     */
    public function __construct(
        Handler $redisHandler
    ) {
        $this->redisHandler = $redisHandler;
        $this->redisClient = $redisHandler->getClient();
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function get(string $key)
    {
        return (string)$this->redisClient->get($key);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return string
     * @throws \Exception
     */
    public function add(string $key, string $value)
    {
        if ($this->redisClient->exists($key) === true) {
            throw new \Exception(__('Can\'t add key. Already exists.'));
        }

        return $this->redisClient->set($key, $value);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key)
    {
        $this->redisClient->del($key);
    }

    /**
     * Implement Tags as a Redis Sets
     * According to
     * https://www.compose.com/articles/how-to-handle-tagged-data-with-redis-sets/
     *
     * Set is a key which can have arbitrary number of distinct values
     * We store every tag as a Set key. We add the hash key as a value of the
     * set.
     *
     * Later, when we need to retrieve values for the certain tag,
     * we retrieve all the values from the set -- it will be arrey of hash keys
     *
     * Then, for every hash key we retrieve value from the hash table
     *
     * @param $key
     * @param $value
     * @param $tags
     */
    public function set($key, $value, $tags)
    {
        $this->redisClient->hSet($key, 'body', $value);
        $this->addTags($key, $tags);
    }

    /**
     * Retrieve all the values from the set -- it will be arrey of hash keys
     * Then, for every hash key we retrieve value from the hash table
     *
     * @param $tag
     *
     * @return array
     */
    public function getByTag($tag)
    {
        $result = [];
        $hashKeys = $this->sMembers($tag);
        foreach ($hashKeys as $hashKey) {
            $result[] = $this->hGet($hashKey, 'body');
        }

        return $result;
    }

    /**
     * @param string $key
     * @param array $tags
     *
     * @return bool
     */
    public function addTags(string $key, array $tags)
    {
        try {
            foreach ($tags as $tag) {
                $this->redisClient->sAdd($tag, $key);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @todo Must remove all or none.
     *
     * @param string $key
     * @param array $tags
     *
     * @return bool
     */
    public function removeTags(string $key, array $tags)
    {
        try {
            foreach ($tags as $tag) {
                $this->redisClient->sPop($tag, $key);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;

    }

    /**
     * @param array $tags
     *
     * @return array
     */
    public function getByTags(array $tags)
    {
        $result = [];
        foreach ($tags as $tag) {
            $result[$tag] = $this->redisClient->sMembers($tag);
        }

        return $result;
    }
}