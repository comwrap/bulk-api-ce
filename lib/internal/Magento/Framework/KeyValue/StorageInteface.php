<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\KeyValue;

/**
 * Standard interface to persist key-value data in Magento
 *
 * @api
 *
 * @package Magento\Framework\KeyValue
 */
interface StorageInterface
{
    /**
     * @param string $key
     *
     * @return string
     */
    public function get(string $key): string;

    /**
     * Throw an exception if the record exists. Maybe also add `update()` method
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function add(string $key, string $value): bool;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * @param string[] $tags
     *
     * @return string[]
     */
    public function getByTags(array $tags): array;

    /**
     * Must add all or none.
     *
     * @param string $key
     * @param string[] $tags
     *
     * @return bool
     */
    public function addTags(string $key, array $tags): bool;

    /**
     * Must remove all or none.
     *
     * @param string $key
     * @param array $tags
     *
     * @return bool
     */
    public function removeTags(string $key, array $tags): bool;
}