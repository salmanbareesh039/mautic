<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCrmBundle\EventListener;

use Mautic\LeadBundle\Event as Events;
use Mautic\LeadBundle\LeadEvents;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticCrmBundle\Integration\Pipedrive\Export\LeadExport;
use MauticPlugin\MauticCrmBundle\Integration\PipedriveIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var LeadExport
     */
    private $leadExport;

    public function __construct(IntegrationHelper $integrationHelper, LeadExport $leadExport = null)
    {
        $this->integrationHelper = $integrationHelper;
        $this->leadExport        = $leadExport;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_POST_SAVE      => ['onLeadPostSave', 0],
            LeadEvents::LEAD_POINTS_CHANGE  => ['onLeadPointsChange', 0],
            LeadEvents::LEAD_PRE_DELETE     => ['onLeadPostDelete', 255],
            LeadEvents::LEAD_COMPANY_CHANGE => ['onLeadCompanyChange', 0],
        ];
    }

    public function onLeadPostSave(Events\LeadEvent $event)
    {
        $lead = $event->getLead();
        $this->syncContactToIntegration($lead);
    }

    public function onLeadPointsChange(Events\PointsChangeEvent $event)
    {
        $lead              = $event->getLead();
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false !== $integrationObject && $integrationObject->shouldImportDataToPipedrive()) {
            $leadFields = $this->integrationHelper->getIntegrationSettings()->getFeatureSettings()['leadFields'];
            if (false !== array_search('points', $leadFields)) {
                $this->syncContactToIntegration($lead);
            }
        }
    }

    public function onLeadPostDelete(Events\LeadEvent $event)
    {
        $lead = $event->getLead();
        if ($lead->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }
        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive()) {
            return;
        }
        $this->leadExport->setIntegration($integrationObject);

        $changes = $lead->getChanges(true);
        if (!empty($changes['dateIdentified'])) {
            $this->leadExport->create($lead);
        } else {
            $this->leadExport->update($lead);
        }
    }

    protected function syncContactToIntegration(\Mautic\LeadBundle\Entity\Lead $lead): void
    {
        if ($lead->isAnonymous()) {
            // Ignore this contact
            return;
        }
        if ($lead->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }
        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive()) {
            return;
        }
        $this->leadExport->setIntegration($integrationObject);

        $changes = $lead->getChanges(true);

        if (empty($changes)) {
            return;
        }

        if (!empty($changes['dateIdentified'])) {
            $this->leadExport->create($lead);
        } else {
            $this->leadExport->update($lead);
        }
    }
}
