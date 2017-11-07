<?php

namespace Sunspikes\Ratelimit\Cache;

abstract class AbstractCacheItem
{
    /**
     * @param string $serialized
     * @return ThrottlerItemInterface
     */
    public function unserialize($serialized)
    {
        $array = json_decode($serialized, true);
        $this->fromArray($array);
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return json_encode($this->toArray());
    }

    /**
     * Wake up call to build the cache item object from array representation
     *
     * @param array $array
     */
    abstract protected function fromArray(array $array);

    /**
     * Get the array representation of the object
     *
     * @return array
     */
    abstract protected function toArray(): array;
}