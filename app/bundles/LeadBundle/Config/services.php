<?php

declare(strict_types=1);

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use Mautic\LeadBundle\Form\Validator\Constraints\LeadListAccess;
use Mautic\LeadBundle\Form\Validator\Constraints\UniqueUserAlias;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Deduplicate/Exception',
        'Field/DTO',
        'Field/Event',
        'Segment/ContactSegmentFilter.php',
        'Segment/ContactSegmentFilterCrate.php',
        'Segment/Decorator',
        'Segment/DoNotContact',
        'Segment/IntegrationCampaign',
        'Segment/Query',
        'Segment/Stat',
    ];

    $services->load('Mautic\\LeadBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MauticCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('Mautic\\LeadBundle\\Entity\\', '../Entity/*Repository.php');
    $services->alias('mautic.lead.model.lead', \Mautic\LeadBundle\Model\LeadModel::class);
    $services->set(LeadListAccess::class)
        ->arg('$message', 'mautic.lead.lists.failed');
    $services->set(UniqueUserAlias::class)
        ->arg('$message', 'This alias is already in use.')
        ->arg('$field', '');
};
