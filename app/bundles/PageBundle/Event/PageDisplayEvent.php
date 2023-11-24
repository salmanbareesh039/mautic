<?php

namespace Mautic\PageBundle\Event;

use Mautic\PageBundle\Entity\Page;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PageDisplayEvent.
 */
class PageDisplayEvent extends Event
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var array
     */
    private $params;

    /**
     * PageDisplayEvent constructor.
     */
    public function __construct($content, Page $page, array $params = [], private bool $trackingDisabled = false)
    {
        $this->page    = $page;
        $this->content = $content;
        $this->params  = $params;
    }

    /**
     * Returns the Page entity.
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Get page content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set page content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get params.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set params.
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /** If tracking is disabled no record for user should be created. */
    public function isTrackingDisabled(): bool
    {
        return $this->trackingDisabled;
    }

    /** If tracking is disabled no record for user should be created. */
    public function setTrackingDisabled(bool $trackingDisabled = true): PageDisplayEvent
    {
        $this->trackingDisabled = $trackingDisabled;

        return $this;
    }
}
