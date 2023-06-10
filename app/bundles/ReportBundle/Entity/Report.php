<?php

namespace Mautic\ReportBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Mautic\EmailBundle\Validator as EmailAssert;
use Mautic\ReportBundle\Scheduler\Enum\SchedulerEnum;
use Mautic\ReportBundle\Scheduler\Exception\ScheduleNotValidException;
use Mautic\ReportBundle\Scheduler\SchedulerInterface;
use Mautic\ReportBundle\Scheduler\Validator as ReportAssert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Report extends FormEntity implements SchedulerInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var bool
     */
    private $system = false;

    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var array
     */
    private $tableOrder = [];

    /**
     * @var array
     */
    private $graphs = [];

    /**
     * @var array
     */
    private $groupBy = [];

    /**
     * @var array
     */
    private $aggregators = [];

    /**
     * @var array|null
     */
    private $settings = [];

    /**
     * @var bool
     */
    private $isScheduled = false;

    /**
     * @var string|null
     */
    private $toAddress;

    /**
     * @var string|null
     */
    private $scheduleUnit;

    /**
     * @var string|null
     */
    private $scheduleDay;

    /**
     * @var string|null
     */
    private $scheduleMonthFrequency;

    public function __clone()
    {
        $this->id = null;

        parent::__clone();
    }

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('reports')
            ->setCustomRepositoryClass(ReportRepository::class);

        $builder->addIdColumns();

        $builder->addField('system', Types::BOOLEAN, ['columnName'=>'`system`']);

        $builder->addField('source', Types::STRING);

        $builder->createField('columns', Types::ARRAY)
            ->nullable()
            ->build();

        $builder->createField('filters', Types::ARRAY)
            ->nullable()
            ->build();

        $builder->createField('tableOrder', Types::ARRAY)
            ->columnName('table_order')
            ->nullable()
            ->build();

        $builder->createField('graphs', Types::ARRAY)
            ->nullable()
            ->build();

        $builder->createField('groupBy', Types::ARRAY)
            ->columnName('group_by')
            ->nullable()
            ->build();

        $builder->createField('aggregators', Types::ARRAY)
            ->columnName('aggregators')
            ->nullable()
            ->build();

        $builder->createField('settings', Types::JSON)
            ->columnName('settings')
            ->nullable()
            ->build();

        $builder->createField('isScheduled', Types::BOOLEAN)
            ->columnName('is_scheduled')
            ->build();

        $builder->addNullableField('scheduleUnit', Types::STRING, 'schedule_unit');
        $builder->addNullableField('toAddress', Types::STRING, 'to_address');
        $builder->addNullableField('scheduleDay', Types::STRING, 'schedule_day');
        $builder->addNullableField('scheduleMonthFrequency', Types::STRING, 'schedule_month_frequency');
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new NotBlank([
            'message' => 'mautic.core.name.required',
        ]));

        $metadata->addPropertyConstraint('toAddress', new EmailAssert\MultipleEmailsValid());

        $metadata->addConstraint(new ReportAssert\ScheduleIsValid());
    }

    /**
     * Prepares the metadata for API usage.
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('report')
            ->addListProperties(
                [
                    'id',
                    'name',
                    'description',
                    'system',
                    'isScheduled',
                ]
            )
            ->addProperties(
                [
                    'source',
                    'columns',
                    'filters',
                    'tableOrder',
                    'graphs',
                    'groupBy',
                    'settings',
                    'aggregators',
                    'scheduleUnit',
                    'toAddress',
                    'scheduleDay',
                    'scheduleMonthFrequency',
                ]
            )
            ->build();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     *
     * @return Report
     */
    public function setName($name)
    {
        $this->isChanged('name', $name);
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $system
     *
     * @return Report
     */
    public function setSystem($system)
    {
        $this->isChanged('system', $system);
        $this->system = $system;

        return $this;
    }

    /**
     * @return int
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return Report
     */
    public function setSource($source)
    {
        $this->isChanged('source', $source);
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed[] $columns
     *
     * @return Report
     */
    public function setColumns($columns)
    {
        $this->isChanged('columns', $columns);
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param mixed[] $filters
     *
     * @return Report
     */
    public function setFilters($filters)
    {
        $this->isChanged('filters', $filters);
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get filter value from a specific filter.
     *
     * @param string $column
     *
     * @return mixed
     *
     * @throws \UnexpectedValueException
     */
    public function getFilterValue($column)
    {
        foreach ($this->getFilters() as $field) {
            if ($column === $field['column']) {
                return $field['value'];
            }
        }

        throw new \UnexpectedValueException("Column {$column} doesn't have any filter.");
    }

    /**
     * Get filter values from a specific filter.
     *
     * @param string $column
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    public function getFilterValues($column)
    {
        $values = [];
        foreach ($this->getFilters() as $field) {
            if ($column === $field['column']) {
                $values[] = $field['value'];
            }
        }

        if (empty($values)) {
            throw new \UnexpectedValueException("Column {$column} doesn't have any filter.");
        }

        return $values;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(mixed $description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getTableOrder()
    {
        return $this->tableOrder;
    }

    public function setTableOrder(array $tableOrder)
    {
        $this->isChanged('tableOrder', $tableOrder);

        $this->tableOrder = $tableOrder;
    }

    /**
     * @return mixed
     */
    public function getGraphs()
    {
        return $this->graphs;
    }

    public function setGraphs(array $graphs)
    {
        $this->isChanged('graphs', $graphs);

        $this->graphs = $graphs;
    }

    /**
     * @return mixed
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    public function setGroupBy(array $groupBy)
    {
        $this->isChanged('groupBy', $groupBy);

        $this->groupBy = $groupBy;
    }

    /**
     * @return mixed
     */
    public function getAggregators()
    {
        return $this->aggregators;
    }

    /**
     * @return array
     */
    public function getAggregatorColumns()
    {
        return array_map(function ($aggregator) {
            return $aggregator['column'];
        }, $this->getAggregators());
    }

    /**
     * @return array
     */
    public function getOrderColumns()
    {
        return array_map(function ($order) {
            return $order['column'];
        }, $this->getTableOrder());
    }

    /**
     * @return array
     */
    public function getSelectAndAggregatorAndOrderAndGroupByColumns()
    {
        return array_merge($this->getSelectAndAggregatorColumns(), $this->getOrderColumns(), $this->getGroupBy());
    }

    /**
     * @return array
     */
    public function getSelectAndAggregatorColumns()
    {
        return array_merge($this->getColumns(), $this->getAggregatorColumns());
    }

    public function setAggregators(array $aggregators)
    {
        $this->isChanged('aggregators', $aggregators);

        $this->aggregators = $aggregators;
    }

    public function setSettings(array $settings)
    {
        $this->isChanged('settings', $settings);

        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return bool
     */
    public function isScheduled()
    {
        return $this->isScheduled;
    }

    /**
     * @param bool $isScheduled
     */
    public function setIsScheduled($isScheduled)
    {
        $this->isChanged('isScheduled', $isScheduled);

        $this->isScheduled = $isScheduled;
    }

    public function getToAddress(): ?string
    {
        return $this->toAddress;
    }

    public function setToAddress(?string $toAddress)
    {
        $this->isChanged('toAddress', $toAddress);

        $this->toAddress = $toAddress;
    }

    public function getScheduleUnit(): ?string
    {
        return $this->scheduleUnit;
    }

    public function setScheduleUnit(?string $scheduleUnit)
    {
        $this->isChanged('scheduleUnit', $scheduleUnit);

        $this->scheduleUnit = $scheduleUnit;
    }

    public function getScheduleDay(): ?string
    {
        return $this->scheduleDay;
    }

    public function setScheduleDay(?string $scheduleDay)
    {
        $this->isChanged('scheduleDay', $scheduleDay);

        $this->scheduleDay = $scheduleDay;
    }

    public function getScheduleMonthFrequency(): ?string
    {
        return $this->scheduleMonthFrequency;
    }

    public function setScheduleMonthFrequency(?string $scheduleMonthFrequency)
    {
        $this->scheduleMonthFrequency = $scheduleMonthFrequency;
    }

    public function setAsNotScheduled()
    {
        $this->setIsScheduled(false);
        $this->setToAddress(null);
        $this->setScheduleUnit(null);
        $this->setScheduleDay(null);
        $this->setScheduleMonthFrequency(null);
    }

    public function setAsScheduledNow(string $email): void
    {
        $this->setIsScheduled(true);
        $this->setToAddress($email);
        $this->setScheduleUnit(SchedulerEnum::UNIT_NOW);
    }

    public function ensureIsDailyScheduled()
    {
        $this->setIsScheduled(true);
        $this->setScheduleUnit(SchedulerEnum::UNIT_DAILY);
        $this->setScheduleDay(null);
        $this->setScheduleMonthFrequency(null);
    }

    /**
     * @throws ScheduleNotValidException
     */
    public function ensureIsMonthlyScheduled()
    {
        if (
            !in_array($this->getScheduleMonthFrequency(), SchedulerEnum::getMonthFrequencyForSelect()) ||
            !in_array($this->getScheduleDay(), SchedulerEnum::getDayEnumForSelect())
        ) {
            throw new ScheduleNotValidException();
        }
        $this->setIsScheduled(true);
        $this->setScheduleUnit(SchedulerEnum::UNIT_MONTHLY);
    }

    /**
     * @throws ScheduleNotValidException
     */
    public function ensureIsWeeklyScheduled()
    {
        if (!in_array($this->getScheduleDay(), SchedulerEnum::getDayEnumForSelect())) {
            throw new ScheduleNotValidException();
        }
        $this->setIsScheduled(true);
        $this->setScheduleUnit(SchedulerEnum::UNIT_WEEKLY);
        $this->setScheduleMonthFrequency(null);
    }

    public function isScheduledNow(): bool
    {
        return SchedulerEnum::UNIT_NOW === $this->getScheduleUnit();
    }

    /**
     * @return bool
     */
    public function isScheduledDaily()
    {
        return SchedulerEnum::UNIT_DAILY === $this->getScheduleUnit();
    }

    /**
     * @return bool
     */
    public function isScheduledWeekly()
    {
        return SchedulerEnum::UNIT_WEEKLY === $this->getScheduleUnit();
    }

    /**
     * @return bool
     */
    public function isScheduledMonthly()
    {
        return SchedulerEnum::UNIT_MONTHLY === $this->getScheduleUnit();
    }

    /**
     * @return bool
     */
    public function isScheduledWeekDays()
    {
        return SchedulerEnum::DAY_WEEK_DAYS === $this->getScheduleDay();
    }
}
