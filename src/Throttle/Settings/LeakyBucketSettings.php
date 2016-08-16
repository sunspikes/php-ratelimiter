<?php

namespace Sunspikes\Ratelimit\Throttle\Settings;

final class LeakyBucketSettings implements ThrottleSettingsInterface
{
    /**
     * @var int|null
     */
    private $tokenLimit;

    /**
     * @var int|null
     */
    private $timeLimit;

    /**
     * @var int|null
     */
    private $threshold;

    /**
     * @var int|null
     */
    private $cacheTtl;

    /**
     * @param int|null $tokenLimit
     * @param int|null $timeLimit
     * @param int|null $threshold
     * @param int|null $cacheTtl
     */
    public function __construct($tokenLimit = null, $timeLimit = null, $threshold = null, $cacheTtl = null)
    {
        $this->tokenLimit = $tokenLimit;
        $this->timeLimit = $timeLimit;
        $this->threshold = $threshold;
        $this->cacheTtl = $cacheTtl;
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
            null === $settings->getTokenLimit() ? $this->tokenLimit : $settings->getTokenLimit(),
            null === $settings->getTimeLimit() ? $this->timeLimit : $settings->getTimeLimit(),
            null === $settings->getThreshold() ? $this->threshold : $settings->getThreshold(),
            null === $settings->getCacheTtl() ? $this->cacheTtl : $settings->getCacheTtl()
        );
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return null !== $this->tokenLimit && null !== $this->timeLimit && 0 !== $this->timeLimit;
    }

    /**
     * @return int|null
     */
    public function getTokenLimit()
    {
        return $this->tokenLimit;
    }

    /**
     * @return int|null
     */
    public function getTimeLimit()
    {
        return $this->timeLimit;
    }

    /**
     * @return int|null
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * @return int|null
     */
    public function getCacheTtl()
    {
        return $this->cacheTtl;
    }
}
