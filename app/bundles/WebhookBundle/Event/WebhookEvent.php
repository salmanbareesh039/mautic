<?php

namespace Mautic\WebhookBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use Mautic\WebhookBundle\Entity\Webhook;

/**
 * Class WebhookEvent.
 */
class WebhookEvent extends CommonEvent
{
    /**
     * @var Webhook
     */
    protected $entity;

    /**
     * @param bool   $isNew
     * @param string $reason
     */
    public function __construct(Webhook $webhook, protected $isNew = false, private $reason = '')
    {
        $this->entity = $webhook;
    }

    /**
     * Returns the Webhook entity.
     *
     * @return Webhook
     */
    public function getWebhook()
    {
        return $this->entity;
    }

    /**
     * Sets the Webhook entity.
     */
    public function setWebhook(Webhook $webhook)
    {
        $this->entity = $webhook;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
