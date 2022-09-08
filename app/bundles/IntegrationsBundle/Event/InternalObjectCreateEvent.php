<?php

declare(strict_types=1);

namespace Mautic\IntegrationsBundle\Event;

use Mautic\IntegrationsBundle\Entity\ObjectMapping;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\ObjectInterface;

class InternalObjectCreateEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var ObjectInterface
     */
    private $object;

    /**
     * @var array
     */
    private $createObjects;

    /**
     * @var ObjectMapping[]
     */
    private $objectMappings = [];

    public function __construct(ObjectInterface $object, array $createObjects)
    {
        $this->object        = $object;
        $this->createObjects = $createObjects;
    }

    public function getObject(): ObjectInterface
    {
        return $this->object;
    }

    public function getCreateObjects(): array
    {
        return $this->createObjects;
    }

    /**
     * @return ObjectMapping[]
     */
    public function getObjectMappings(): array
    {
        return $this->objectMappings;
    }

    /**
     * @param ObjectMapping[] $objectMappings
     */
    public function setObjectMappings(array $objectMappings): void
    {
        $this->objectMappings = $objectMappings;
    }
}
