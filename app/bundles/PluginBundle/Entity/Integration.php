<?php

namespace Mautic\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\CommonEntity;

/**
 * Class Integration.
 */
class Integration extends CommonEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Plugin|null
     */
    private $plugin;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isPublished = false;

    /**
     * @var array
     */
    private $supportedFeatures = [];

    /**
     * @var array
     */
    private $apiKeys = [];

    /**
     * @var array
     */
    private $featureSettings = [];

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('plugin_integration_settings')
            ->setCustomRepositoryClass('Mautic\PluginBundle\Entity\IntegrationRepository');

        $builder->createField('id', 'integer')
            ->makePrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createManyToOne('plugin', 'Plugin')
            ->inversedBy('integrations')
            ->addJoinColumn('plugin_id', 'id', true, false, 'CASCADE')
            ->build();

        $builder->addField('name', 'string');

        $builder->createField('isPublished', 'boolean')
            ->columnName('is_published')
            ->build();

        $builder->createField('supportedFeatures', 'array')
            ->columnName('supported_features')
            ->nullable()
            ->build();

        $builder->createField('apiKeys', 'array')
            ->columnName('api_keys')
            ->build();

        $builder->createField('featureSettings', 'array')
            ->columnName('feature_settings')
            ->nullable()
            ->build();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }

    /**
     * @return Integration
     */
    public function setPlugin(mixed $plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Integration
     */
    public function setName(mixed $name)
    {
        $this->isChanged('name', $name);

        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * @return Integration
     */
    public function setIsPublished(mixed $isPublished)
    {
        $this->isChanged('isPublished', $isPublished);

        $this->isPublished = $isPublished;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    /**
     * @return mixed
     */
    public function getSupportedFeatures()
    {
        return $this->supportedFeatures;
    }

    /**
     * @return Integration
     */
    public function setSupportedFeatures(mixed $supportedFeatures)
    {
        $this->isChanged('supportedFeatures', $supportedFeatures);

        $this->supportedFeatures = $supportedFeatures;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiKeys()
    {
        return $this->apiKeys;
    }

    /**
     * @return Integration
     */
    public function setApiKeys(mixed $apiKeys)
    {
        $this->apiKeys = $apiKeys;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFeatureSettings()
    {
        return $this->featureSettings;
    }

    /**
     * @return Integration
     */
    public function setFeatureSettings(mixed $featureSettings)
    {
        $this->isChanged('featureSettings', $featureSettings);

        $this->featureSettings = $featureSettings;

        return $this;
    }
}
