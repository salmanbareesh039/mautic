<?php

namespace Mautic\LeadBundle\Event;

use Mautic\LeadBundle\Entity\Lead;

class ContactIdentificationEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var array
     */
    private $clickthrough;

    /**
     * @var Lead
     */
    private $identifiedContact;

    /**
     * @var string
     */
    private $identifiedByChannel;

    /**
     * ContactIdentificationEvent constructor.
     */
    public function __construct(array $clickthrough)
    {
        $this->clickthrough = $clickthrough;
    }

    /**
     * @return array
     */
    public function getClickthrough()
    {
        return $this->clickthrough;
    }

    /**
     * @param string $channel
     */
    public function setIdentifiedContact(Lead $contact, $channel)
    {
        $this->identifiedContact   = $contact;
        $this->identifiedByChannel = $channel;

        $this->stopPropagation();
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifiedByChannel;
    }

    /**
     * @return Lead
     */
    public function getIdentifiedContact()
    {
        return $this->identifiedContact;
    }
}
