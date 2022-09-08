<?php

namespace Mautic\CoreBundle\Event;

/**
 * Class BuilderEvent.
 */
class UpgradeEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var array
     */
    protected $status = [];

    public function __construct(array $status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function isSuccessful()
    {
        if (array_key_exists('success', $this->status)) {
            return (bool) $this->status['success'];
        }

        return false;
    }
}
