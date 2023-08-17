<?php

namespace Mautic\LeadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CategoryBundle\Entity\Category;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

/**
 * Class LeadCategory.
 */
class LeadCategory
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Category
     **/
    private $category;

    /**
     * @var Lead
     */
    private $lead;

    /**
     * @var \DateTimeInterface
     */
    private $dateAdded;

    /**
     * @var bool
     */
    private $manuallyRemoved = false;

    /**
     * @var bool
     */
    private $manuallyAdded = false;

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('lead_categories')
            ->setCustomRepositoryClass('Mautic\LeadBundle\Entity\LeadCategoryRepository');

        $builder->addId();

        $builder->createManyToOne('category', 'Mautic\CategoryBundle\Entity\Category')
            ->addJoinColumn('category_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->addLead(false, 'CASCADE', false);

        $builder->addDateAdded();

        $builder->createField('manuallyRemoved', 'boolean')
            ->columnName('manually_removed')
            ->build();

        $builder->createField('manuallyAdded', 'boolean')
            ->columnName('manually_added')
            ->build();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param \DateTime $date
     */
    public function setDateAdded($date)
    {
        $this->dateAdded = $date;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param Lead $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return bool
     */
    public function getManuallyRemoved()
    {
        return $this->manuallyRemoved;
    }

    /**
     * @param bool $manuallyRemoved
     */
    public function setManuallyRemoved($manuallyRemoved)
    {
        $this->manuallyRemoved = $manuallyRemoved;
    }

    /**
     * @return bool
     */
    public function wasManuallyRemoved()
    {
        return $this->manuallyRemoved;
    }

    /**
     * @return bool
     */
    public function getManuallyAdded()
    {
        return $this->manuallyAdded;
    }

    /**
     * @param bool $manuallyAdded
     */
    public function setManuallyAdded($manuallyAdded)
    {
        $this->manuallyAdded = $manuallyAdded;
    }

    /**
     * @return bool
     */
    public function wasManuallyAdded()
    {
        return $this->manuallyAdded;
    }
}
