<?php

namespace Sunspikes\Ratelimit\Throttle\Settings;

final class FixedWindowSettings implements ThrottleSettingsInterface
{
    /**
     * @var int|null
     */
    private $limit;

    /**
     * @var int|null
     */
    private $time;

    /**
     * @param int|null $limit
     * @param int|null $time
     */
    public function __construct($limit = null, $time = null)
    {
        $this->limit = $limit;
        $this->time = $time;
    }

    /**
     * @inheritdoc
     */
    public function merge(ThrottleSettingsInterface $settings)
    {
        if (!$settings instanceof self) {
            throw new \InvalidArgumentException(
                sprintf('Unable to merge %s into %s', get_class($settings), get_class($this))
            );
        }

        return new self(
            null === $settings->getLimit() ? $this->limit : $settings->getLimit(),
            null === $settings->getTime() ? $this->time : $settings->getTime()
        );
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return null !== $this->limit && null !== $this->time;
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int|null
     */
    public function getTime()
    {
        return $this->time;
    }
}
