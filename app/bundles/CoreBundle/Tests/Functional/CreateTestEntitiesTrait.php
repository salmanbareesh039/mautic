<?php

declare(strict_types=1);

namespace Mautic\CoreBundle\Tests\Functional;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CategoryBundle\Entity\Category;
use Mautic\EmailBundle\Entity\Email;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadCategory;
use Mautic\LeadBundle\Entity\LeadList;

trait CreateTestEntitiesTrait
{
    private function createLead(string $firstName, string $lastName = '', string $emailId = ''): Lead
    {
        $lead = new Lead();
        $lead->setFirstname($firstName);

        if ($lastName) {
            $lead->setLastname($lastName);
        }

        if ($emailId) {
            $lead->setEmail($emailId);
        }

        $this->em->persist($lead);

        return $lead;
    }

    private function createCampaign(string $campaignName): Campaign
    {
        $campaign = new Campaign();
        $campaign->setName($campaignName);
        $campaign->setIsPublished(true);
        $this->em->persist($campaign);

        return $campaign;
    }

    private function createEvent(string $name, Campaign $campaign, string $type, string $eventType, array $property = null): Event
    {
        $event = new Event();
        $event->setName($name);
        $event->setCampaign($campaign);
        $event->setType($type);
        $event->setEventType($eventType);
        $event->setTriggerInterval(1);
        $event->setProperties($property);
        $event->setTriggerMode('immediate');
        $this->em->persist($event);

        return $event;
    }

    /**
     * @param mixed[] $filters
     */
    private function createSegment(string $alias, array $filters): LeadList
    {
        $segment = new LeadList();
        $segment->setAlias($alias);
        $segment->setName($alias);
        $segment->setFilters($filters);
        $this->em->persist($segment);

        return $segment;
    }

    private function createCategory(string $name, string $alias, string $bundle = 'global'): Category
    {
        $category = new Category();
        $category->setTitle($name);
        $category->setAlias($alias);
        $category->setBundle($bundle);

        $this->em->persist($category);

        return $category;
    }

    private function createLeadCategory(Lead $lead, Category $category, bool $flag): void
    {
        $leadCategory = new LeadCategory();
        $leadCategory->setLead($lead);
        $leadCategory->setCategory($category);
        $leadCategory->setDateAdded(new \DateTime());
        $leadCategory->setManuallyAdded($flag);
        $leadCategory->setManuallyRemoved(!$flag);

        $this->em->persist($leadCategory);
    }

    private function createEmail(string $name): Email
    {
        $email = new Email();
        $email->setName($name);
        $email->setIsPublished(true);

        $this->em->persist($email);

        return $email;
    }
}
