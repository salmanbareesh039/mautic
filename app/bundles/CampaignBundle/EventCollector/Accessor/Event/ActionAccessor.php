<?php

namespace Mautic\CampaignBundle\EventCollector\Accessor\Event;

class ActionAccessor extends AbstractEventAccessor
{
    public function __construct(array $config)
    {
        $this->systemProperties[] = 'batchEventName';

        parent::__construct($config);
    }

    public function getBatchEventName()
    {
        return $this->getProperty('batchEventName');
    }
}
