<?php


return [
    'routes' => [
        'public' => [
            // OAuth2
            'fos_oauth_server_token' => [
                'path'       => '/oauth/v2/token',
                'controller' => 'fos_oauth_server.controller.token:tokenAction',
                'method'     => 'GET|POST',
            ],
            'fos_oauth_server_authorize' => [
                'path'       => '/oauth/v2/authorize',
                'controller' => 'MauticApiBundle:oAuth2/Authorize:authorize',
                'method'     => 'GET|POST',
            ],
            'mautic_oauth2_server_auth_login' => [
                'path'       => '/oauth/v2/authorize_login',
                'controller' => 'MauticApiBundle:oAuth2/Security:login',
                'method'     => 'GET|POST',
            ],
            'mautic_oauth2_server_auth_login_check' => [
                'path'       => '/oauth/v2/authorize_login_check',
                'controller' => 'MauticApiBundle:oAuth2/Security:loginCheck',
                'method'     => 'GET|POST',
            ],
        ],
        'main' => [
            // Clients
            'mautic_client_index' => [
                'path'       => '/credentials/{page}',
                'controller' => 'MauticApiBundle:Client:index',
            ],
            'mautic_client_action' => [
                'path'       => '/credentials/{objectAction}/{objectId}',
                'controller' => 'MauticApiBundle:Client:execute',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'items' => [
                'mautic.api.client.menu.index' => [
                    'route'     => 'mautic_client_index',
                    'iconClass' => 'fa-puzzle-piece',
                    'access'    => 'api:clients:view',
                    'checks'    => [
                        'parameters' => [
                            'api_enabled' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'services' => [
        'controllers' => [
            'mautic.api.oauth2.authorize_controller' => [
                'class'     => \Mautic\ApiBundle\Controller\oAuth2\AuthorizeController::class,
                'arguments' => [
                    'request_stack',
                    'fos_oauth_server.authorize.form',
                    'fos_oauth_server.authorize.form.handler.default',
                    'fos_oauth_server.server',
                    'templating',
                    'security.token_storage',
                    'router',
                    'fos_oauth_server.client_manager.default',
                    'event_dispatcher',
                    'session',
                ],
            ],
        ],
        'events' => [
            'mautic.api.subscriber' => [
                'class'     => \Mautic\ApiBundle\EventListener\ApiSubscriber::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'translator',
                ],
            ],
            'mautic.api.client.subscriber' => [
                'class'     => \Mautic\ApiBundle\EventListener\ClientSubscriber::class,
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                ],
            ],
            'mautic.api.configbundle.subscriber' => [
                'class' => \Mautic\ApiBundle\EventListener\ConfigSubscriber::class,
            ],
            'mautic.api.search.subscriber' => [
                'class'     => \Mautic\ApiBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'mautic.api.model.client',
                    'mautic.security',
                    'mautic.helper.templating',
                ],
            ],
            'mautic.api.rate_limit_generate_key.subscriber' => [
              'class'     => \Mautic\ApiBundle\EventListener\RateLimitGenerateKeySubscriber::class,
              'arguments' => [
                'mautic.helper.core_parameters',
              ],
            ],
        ],
        'forms' => [
            'mautic.form.type.apiclients' => [
                'class'     => \Mautic\ApiBundle\Form\Type\ClientType::class,
                'arguments' => [
                    'request_stack',
                    'translator',
                    'validator',
                    'session',
                    'router',
                ],
            ],
            'mautic.form.type.apiconfig' => [
                'class' => 'Mautic\ApiBundle\Form\Type\ConfigType',
            ],
        ],
        'helpers' => [
            'mautic.api.helper.entity_result' => [
                'class' => \Mautic\ApiBundle\Helper\EntityResultHelper::class,
            ],
        ],
        'other' => [
            'mautic.api.oauth.event_listener' => [
                'class'     => 'Mautic\ApiBundle\EventListener\OAuthEventListener',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'mautic.security',
                    'translator',
                ],
                'tags' => [
                    'kernel.event_listener',
                    'kernel.event_listener',
                ],
                'tagArguments' => [
                    [
                        'event'  => 'fos_oauth_server.pre_authorization_process',
                        'method' => 'onPreAuthorizationProcess',
                    ],
                    [
                        'event'  => 'fos_oauth_server.post_authorization_process',
                        'method' => 'onPostAuthorizationProcess',
                    ],
                ],
            ],
            'fos_oauth_server.security.authentication.listener.class' => 'Mautic\ApiBundle\Security\OAuth2\Firewall\OAuthListener',
            'jms_serializer.metadata.annotation_driver'               => 'Mautic\ApiBundle\Serializer\Driver\AnnotationDriver',
            'jms_serializer.metadata.api_metadata_driver'             => [
                'class' => 'Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver',
            ],
            'mautic.validator.oauthcallback' => [
                'class' => 'Mautic\ApiBundle\Form\Validator\Constraints\OAuthCallbackValidator',
                'tag'   => 'validator.constraint_validator',
            ],
        ],
        'models' => [
            'mautic.api.model.client' => [
                'class'     => 'Mautic\ApiBundle\Model\ClientModel',
                'arguments' => [
                    'request_stack',
                ],
            ],
        ],
    ],

    'parameters' => [
        'api_enabled'                       => false,
        'api_enable_basic_auth'             => false,
        'api_oauth2_access_token_lifetime'  => 60,
        'api_oauth2_refresh_token_lifetime' => 14,
        'api_batch_max_limit'               => 200,
        'api_rate_limiter_limit'            => 0,
        'api_rate_limiter_cache'            => [
            'adapter' => 'cache.adapter.filesystem',
        ],
    ],
];
