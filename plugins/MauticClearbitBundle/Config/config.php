<?php

return [
    'name'        => 'Clearbit',
    'description' => 'Enables integration with Clearbit for contact and company lookup',
    'version'     => '1.0',
    'author'      => 'Werner Garcia',

    'routes' => [
        'public' => [
            'mautic_plugin_clearbit_index' => [
                'path'       => '/clearbit/callback',
                'controller' => 'MauticPlugin\MauticClearbitBundle\Controller\PublicController::callbackAction',
            ],
        ],
        'main' => [
            'mautic_plugin_clearbit_action' => [
                'path'       => '/clearbit/{objectAction}/{objectId}',
                'controller' => 'MauticPlugin\MauticClearbitBundle\Controller\ClearbitController::executeAction',
            ],
        ],
    ],

    'services' => [
        'forms' => [
            'mautic.form.type.clearbit_lookup' => [
                'class' => 'MauticPlugin\MauticClearbitBundle\Form\Type\LookupType',
            ],
            'mautic.form.type.clearbit_batch_lookup' => [
                'class' => 'MauticPlugin\MauticClearbitBundle\Form\Type\BatchLookupType',
            ],
        ],
        'others' => [
            'mautic.plugin.clearbit.lookup_helper' => [
                'class'     => 'MauticPlugin\MauticClearbitBundle\Helper\LookupHelper',
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.helper.user',
                    'monolog.logger.mautic',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.clearbit' => [
                'class'     => \MauticPlugin\MauticClearbitBundle\Integration\ClearbitIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
    ],
];
