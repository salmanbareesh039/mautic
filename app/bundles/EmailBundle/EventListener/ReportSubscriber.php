<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\CoreBundle\Doctrine\Provider\GeneratedColumnsProviderInterface;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Helper\Chart\PieChart;
use Mautic\EmailBundle\Entity\StatRepository;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Model\CompanyReportData;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\Event\ReportGraphEvent;
use Mautic\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubscriber implements EventSubscriberInterface
{
    const CONTEXT_EMAILS       = 'emails';
    const CONTEXT_EMAIL_STATS  = 'email.stats';
    const EMAILS_PREFIX        = 'e';
    const EMAIL_STATS_PREFIX   = 'es';
    const EMAIL_VARIANT_PREFIX = 'vp';
    const DNC_PREFIX           = 'dnc';
    const CLICK_PREFIX         = 'cut';

    const DNC_COLUMNS = [
        'unsubscribed' => [
            'alias'   => 'unsubscribed',
            'label'   => 'mautic.email.report.unsubscribed',
            'formula' => self::DNC_PREFIX.'.unsubscribed',
            'type'    => 'string',
        ],
        'unsubscribed_ratio' => [
            'alias'   => 'unsubscribed_ratio',
            'label'   => 'mautic.email.report.unsubscribed_ratio',
            'type'    => 'string',
            'formula' => 'ROUND((dnc.unsubscribed/e.sent_count)*100,2)',
            'suffix'  => '%',
        ],
        'bounced' => [
            'alias'   => 'bounced',
            'label'   => 'mautic.email.report.bounced',
            'formula' => self::DNC_PREFIX.'.bounced',
            'type'    => 'string',
        ],
        'bounced_ratio' => [
            'alias'    => 'bounced_ratio',
            'label'    => 'mautic.email.report.bounced_ratio',
            'type'     => 'string',
            'formula'  => 'ROUND((dnc.bounced/e.sent_count)*100,2)',
            'suffix'   => '%',
        ],
    ];

    const EMAIL_STATS_COLUMNS = [
        self::EMAIL_STATS_PREFIX.'.email_address' => [
            'label' => 'mautic.email.report.stat.email_address',
            'type'  => 'email',
        ],
        self::EMAIL_STATS_PREFIX.'.date_sent' => [
            'label'          => 'mautic.email.report.stat.date_sent',
            'type'           => 'datetime',
            'groupByFormula' => 'DATE('.self::EMAIL_STATS_PREFIX.'.date_sent)',
        ],
        self::EMAIL_STATS_PREFIX.'.is_read' => [
            'label' => 'mautic.email.report.stat.is_read',
            'type'  => 'bool',
        ],
        self::EMAIL_STATS_PREFIX.'.is_failed' => [
            'label' => 'mautic.email.report.stat.is_failed',
            'type'  => 'bool',
        ],
        self::EMAIL_STATS_PREFIX.'.viewed_in_browser' => [
            'label' => 'mautic.email.report.stat.viewed_in_browser',
            'type'  => 'bool',
        ],
        self::EMAIL_STATS_PREFIX.'.date_read' => [
            'label'          => 'mautic.email.report.stat.date_read',
            'type'           => 'datetime',
            'groupByFormula' => 'DATE('.self::EMAIL_STATS_PREFIX.'.date_read)',
        ],
        self::EMAIL_STATS_PREFIX.'.retry_count' => [
            'label' => 'mautic.email.report.stat.retry_count',
            'type'  => 'int',
        ],
        self::EMAIL_STATS_PREFIX.'.source' => [
            'label' => 'mautic.report.field.source',
            'type'  => 'string',
        ],
        self::EMAIL_STATS_PREFIX.'.source_id' => [
            'label' => 'mautic.report.field.source_id',
            'type'  => 'int',
        ],
    ];

    const EMAIL_VARIANT_COLUMNS = [
        self::EMAIL_VARIANT_PREFIX.'.id' => [
            'label' => 'mautic.email.report.variant_parent_id',
            'type'  => 'int',
        ],
        self::EMAIL_VARIANT_PREFIX.'.subject' => [
            'label' => 'mautic.email.report.variant_parent_subject',
            'type'  => 'string',
        ],
    ];

    const CLICK_COLUMNS = [
        'hits' => [
            'alias'   => 'hits',
            'label'   => 'mautic.email.report.hits_count',
            'type'    => 'string',
            'formula' => 'IFNULL('.self::CLICK_PREFIX.'.hits, 0)',
        ],
        'unique_hits' => [
            'alias'   => 'unique_hits',
            'label'   => 'mautic.email.report.unique_hits_count',
            'type'    => 'string',
            'formula' => 'IFNULL('.self::CLICK_PREFIX.'.unique_hits, 0)',
        ],
        'hits_ratio' => [
            'alias'   => 'hits_ratio',
            'label'   => 'mautic.email.report.hits_ratio',
            'type'    => 'string',
            'formula' => 'IFNULL(ROUND('.self::CLICK_PREFIX.'.hits/('.self::EMAILS_PREFIX.'.sent_count)*100, 1), \'0.0\')',
            'suffix'  => '%',
        ],
        'unique_ratio' => [
            'alias'   => 'unique_ratio',
            'label'   => 'mautic.email.report.unique_ratio',
            'type'    => 'string',
            'formula' => 'IFNULL(ROUND('.self::CLICK_PREFIX.'.unique_hits/('.self::EMAILS_PREFIX.'.sent_count)*100, 1), \'0.0\')',
            'suffix'  => '%',
        ],
    ];

    const STAT_COLUMNS = [
        'e.sent_count',
        'e.read_count',
        'read_ratio',
    ];

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var CompanyReportData
     */
    private $companyReportData;

    /**
     * @var GeneratedColumnsProviderInterface
     */
    private $generatedColumnsProvider;

    /**
     * @var StatRepository
     */
    private $statRepository;

    public function __construct(
        Connection $db,
        CompanyReportData $companyReportData,
        StatRepository $statRepository,
        GeneratedColumnsProviderInterface $generatedColumnsProvider
    ) {
        $this->db                       = $db;
        $this->companyReportData        = $companyReportData;
        $this->statRepository           = $statRepository;
        $this->generatedColumnsProvider = $generatedColumnsProvider;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ReportEvents::REPORT_ON_BUILD          => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE       => ['onReportGenerate', 0],
            ReportEvents::REPORT_ON_GRAPH_GENERATE => ['onReportGraphGenerate', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     */
    public function onReportBuilder(ReportBuilderEvent $event)
    {
        if (!$event->checkContext([self::CONTEXT_EMAILS, self::CONTEXT_EMAIL_STATS])) {
            return;
        }

        $prefix  = self::EMAILS_PREFIX.'.';
        $columns = [
            $prefix.'subject' => [
                'label' => 'mautic.email.subject',
                'type'  => 'string',
            ],
            $prefix.'lang' => [
                'label' => 'mautic.core.language',
                'type'  => 'string',
            ],
            $prefix.'read_count' => [
                'label' => 'mautic.email.report.read_count',
                'type'  => 'int',
            ],
            'read_ratio' => [
                'alias'   => 'read_ratio',
                'label'   => 'mautic.email.report.read_ratio',
                'type'    => 'string',
                'formula' => 'IFNULL(ROUND(('.$prefix.'read_count/'.$prefix.'sent_count)*100, 1), \'0.0\')',
                'suffix'  => '%',
            ],
            $prefix.'sent_count' => [
                'label' => 'mautic.email.report.sent_count',
                'type'  => 'int',
            ],
            $prefix.'revision' => [
                'label' => 'mautic.email.report.revision',
                'type'  => 'int',
            ],
            $prefix.'variant_start_date' => [
                'label'          => 'mautic.email.report.variant_start_date',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE('.$prefix.'variant_start_date)',
            ],
            $prefix.'variant_sent_count' => [
                'label' => 'mautic.email.report.variant_sent_count',
                'type'  => 'int',
            ],
            $prefix.'variant_read_count' => [
                'label' => 'mautic.email.report.variant_read_count',
                'type'  => 'int',
            ],
        ];

        $columns = array_merge(
            $columns,
            $event->getStandardColumns($prefix, [], 'mautic_email_action'),
            $event->getCategoryColumns(),
            self::DNC_COLUMNS,
            self::EMAIL_VARIANT_COLUMNS,
            self::CLICK_COLUMNS
        );
        $data = [
            'display_name' => 'mautic.email.emails',
            'columns'      => $columns,
        ];
        $event->addTable(self::CONTEXT_EMAILS, $data);
        $context = self::CONTEXT_EMAILS;
        $event->addGraph($context, 'pie', 'mautic.email.graph.pie.read.ingored.unsubscribed.bounced');

        if ($event->checkContext(self::CONTEXT_EMAIL_STATS)) {
            // Ratios are not applicable for individual stats
            unset($columns['read_ratio'], $columns['unsubscribed_ratio'], $columns['bounced_ratio'], $columns['hits_ratio'], $columns['unique_ratio']);

            // Email counts are not applicable for individual stats
            unset($columns[$prefix.'sent_count'], $columns[$prefix.'read_count'], $columns[$prefix.'variant_sent_count'], $columns[$prefix.'variant_read_count']);

            // Prevent null DNC records from filtering the results
            $columns['unsubscribed']['type']    = 'bool';
            $columns['unsubscribed']['formula'] = 'IF(dnc.id IS NOT NULL AND dnc.reason='.DoNotContact::UNSUBSCRIBED.', 1, 0)';

            $columns['bounced']['type']    = 'bool';
            $columns['bounced']['formula'] = 'IF(dnc.id IS NOT NULL AND dnc.reason='.DoNotContact::BOUNCED.', 1, 0)';

            // clicked column for individual stats
            $columns['is_hit'] = [
                'alias'   => 'is_hit',
                'label'   => 'mautic.email.report.is_hit',
                'type'    => 'bool',
                'formula' => 'IF('.self::CLICK_PREFIX.'.hits is NULL, 0, 1)',
            ];

            // time between sent and read
            $columns['read_delay'] = [
                'alias'   => 'read_delay',
                'label'   => 'mautic.email.report.read.delay',
                'type'    => 'string',
                'formula' => 'IF(es.date_read IS NOT NULL, TIMEDIFF(es.date_read, es.date_sent), \'-\')',
            ];

            $data = [
                'display_name' => 'mautic.email.stats.report.table',
                'columns'      => array_merge(
                    $columns,
                    self::EMAIL_STATS_COLUMNS,
                    $event->getCampaignByChannelColumns(),
                    $event->getLeadColumns(),
                    $event->getIpColumn(),
                    $this->companyReportData->getCompanyData()
                ),
            ];
            $event->addTable(self::CONTEXT_EMAIL_STATS, $data, self::CONTEXT_EMAILS);

            // Register Graphs
            $context = self::CONTEXT_EMAIL_STATS;
            $event->addGraph($context, 'line', 'mautic.email.graph.line.stats');
            $event->addGraph($context, 'pie', 'mautic.email.graph.pie.ignored.read.failed');
            $event->addGraph($context, 'table', 'mautic.email.table.most.emails.sent');
            $event->addGraph($context, 'table', 'mautic.email.table.most.emails.read');
            $event->addGraph($context, 'table', 'mautic.email.table.most.emails.read.percent');
            $event->addGraph($context, 'table', 'mautic.email.table.most.emails.unsubscribed');
            $event->addGraph($context, 'table', 'mautic.email.table.most.emails.bounced');
            $event->addGraph($context, 'table', 'mautic.email.table.most.emails.failed');
        }
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        $context    = $event->getContext();
        $qb         = $event->getQueryBuilder();
        $hasGroupBy = $event->hasGroupBy();

        // channel_url_trackables subquery
        $qbcut             = $this->db->createQueryBuilder();
        $useDncColumns     = $event->usesColumn(array_keys(self::DNC_COLUMNS));
        $useVariantColumns = $event->usesColumn(array_keys(self::EMAIL_VARIANT_COLUMNS));
        $useClickColumns   = $event->usesColumn(array_keys(self::CLICK_COLUMNS)) || $event->usesColumn('is_hit');
        $useStatColumns    = $event->usesColumn(self::STAT_COLUMNS);

        switch ($context) {
            case self::CONTEXT_EMAILS:
                $qb->from(MAUTIC_TABLE_PREFIX.'emails', self::EMAILS_PREFIX)
                    ->leftJoin(self::EMAILS_PREFIX, MAUTIC_TABLE_PREFIX.'emails', self::EMAIL_VARIANT_PREFIX, 'vp.id = e.variant_parent_id');

                $event->addCategoryLeftJoin($qb, self::EMAILS_PREFIX);

                $hideDateRangeFilter = $event->getReport()->getSettings()['hideDateRangeFilter'] ?? null;

                if ($hideDateRangeFilter) {
                    if ($useClickColumns) {
                        $qbcut->select(
                            'COUNT(cut2.channel_id) AS trackable_count, SUM(cut2.hits) AS hits',
                            'SUM(cut2.unique_hits) AS unique_hits',
                            'cut2.channel_id'
                        )
                            ->from(MAUTIC_TABLE_PREFIX.'channel_url_trackables', 'cut2')
                            ->where('cut2.channel = \'email\'')
                            ->groupBy('cut2.channel_id');
                        $qb->leftJoin(self::EMAILS_PREFIX, sprintf('(%s)', $qbcut->getSQL()), self::CLICK_PREFIX, 'e.id = cut.channel_id');
                    }

                    if ($useDncColumns) {
                        $this->addDNCTableForEmails($qb);
                        // based DNC subquery contains also dateTo/dateFrom, we need cover it
                        $qb->setParameter(':dateTo', (new \DateTime())->format('Y-m-d'));
                        $qb->setParameter(':dateFrom', (new \DateTime())->modify('-999 years')->format('Y-m-d'));
                    }
                } else {
                    if ($useStatColumns || $useClickColumns) {
                        $subQuery = $this->db->createQueryBuilder();
                        $subQuery->select(
                            'COUNT(es.email_id) as sent_count',
                            'SUM(es.is_read) as read_count',
                            'es.email_id as email_id'
                        )
                            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
                            ->where(sprintf('%1$s BETWEEN :dateFrom AND :dateTo', 'es.date_sent'))
                            ->groupBy('es.email_id');
                        $qb->innerJoin('e', sprintf('(%s)', $subQuery->getSQL()), 'stats', 'e.id = stats.email_id');
                        $event->setColumnFormula('e.sent_count', 'stats.sent_count');
                        $event->setColumnFormula('e.read_count', 'stats.read_count');
                        $event->setColumnFormula('read_ratio', 'ROUND((stats.read_count/stats.sent_count)*100,1)');
                    }

                    if ($useDncColumns) {
                        $event->setColumnFormula('unsubscribed', 'IFNULL(dnc.unsubscribed, 0)');
                        $event->setColumnFormula('bounced', 'IFNULL(dnc.bounced, 0)');
                        $event->setColumnFormula(
                            'unsubscribed_ratio',
                            'ROUND(IFNULL(('.self::DNC_PREFIX.'.unsubscribed/stats.sent_count)*100,0),2)'
                        );
                        $event->setColumnFormula(
                            'bounced_ratio',
                            'ROUND(IFNULL(('.self::DNC_PREFIX.'.bounced/stats.sent_count)*100, 0),2)'
                        );

                        $this->addDNCTableForEmails($qb);
                    }

                    if ($useClickColumns) {
                        // clicks stats with date range
                        $subQuery = $this->db->createQueryBuilder();
                        $subQuery->select(
                            'COUNT(ph.id) as hits',
                            'COUNT(DISTINCT ph.lead_id) as unique_hits',
                            'ph.email_id as email_id'
                        )
                            ->from(MAUTIC_TABLE_PREFIX.'page_hits', 'ph')
                            ->where(sprintf('%1$s BETWEEN :dateFrom AND :dateTo', 'ph.date_hit'))
                            ->andWhere('ph.email_id IS NOT NULL')
                            ->groupBy('ph.email_id');
                        $qb->leftJoin('e', sprintf('(%s)', $subQuery->getSQL()), 'hits', 'e.id = hits.email_id');
                        $event->setColumnFormula('hits', 'IFNULL(hits.hits, 0)');
                        $event->setColumnFormula('unique_hits', 'IFNULL(hits.unique_hits, 0)');
                        $event->setColumnFormula('hits_ratio', 'ROUND(IFNULL((hits.hits/stats.sent_count)*100, 0),1)');
                        $event->setColumnFormula(
                            'unique_ratio',
                            'ROUND(IFNULL((hits.unique_hits/stats.sent_count)*100, 0),1)'
                        );
                    }

                    $event->applyDateFilters($qb, 'date_added', self::EMAILS_PREFIX);
                }

                if (!$hasGroupBy) {
                    $qb->groupBy('e.id');
                }

                break;
            case self::CONTEXT_EMAIL_STATS:
                $qb->from(MAUTIC_TABLE_PREFIX.'email_stats', self::EMAIL_STATS_PREFIX);

                if ($event->usesColumnWithPrefix(self::EMAILS_PREFIX)
                    || $event->usesColumnWithPrefix(ReportGeneratorEvent::CATEGORY_PREFIX)
                    || $useVariantColumns
                ) {
                    $qb->leftJoin(self::EMAIL_STATS_PREFIX, MAUTIC_TABLE_PREFIX.'emails', self::EMAILS_PREFIX, 'e.id = es.email_id');
                }

                if ($useVariantColumns) {
                    $qb->leftJoin(self::EMAILS_PREFIX, MAUTIC_TABLE_PREFIX.'emails', self::EMAIL_VARIANT_PREFIX, 'vp.id = e.variant_parent_id');
                }

                if ($useDncColumns) {
                    $this->addDNCTableForEmailStats($qb);
                }

                $event->addCategoryLeftJoin($qb, self::EMAILS_PREFIX)
                    ->addLeadLeftJoin($qb, self::EMAIL_STATS_PREFIX)
                    ->addIpAddressLeftJoin($qb, self::EMAIL_STATS_PREFIX)
                    ->applyDateFilters($qb, 'date_sent', self::EMAIL_STATS_PREFIX);
                if ($useClickColumns) {
                    $qbcut->select(
                        'COUNT(ph.id) AS hits',
                        'COUNT(DISTINCT(ph.redirect_id)) AS unique_hits',
                        'cut2.channel_id',
                        'ph.lead_id'
                    )
                        ->from(MAUTIC_TABLE_PREFIX.'channel_url_trackables', 'cut2')
                        ->join(
                            'cut2',
                            MAUTIC_TABLE_PREFIX.'page_hits',
                            'ph',
                            'cut2.redirect_id = ph.redirect_id AND cut2.channel_id = ph.source_id'
                        )
                        ->where('cut2.channel = \'email\' AND ph.source = \'email\'')
                        ->groupBy('cut2.channel_id, ph.lead_id');

                    if ($event->hasFilter('e.id')) {
                        $filterParam = $event->createParameterName();
                        $qbcut->andWhere($qb->expr()->in('cut2.channel_id', ":{$filterParam}"));
                        $qb->setParameter($filterParam, $event->getFilterValues('e.id'), Connection::PARAM_INT_ARRAY);
                    }

                    $qb->leftJoin(
                        self::EMAIL_STATS_PREFIX,
                        "({$qbcut->getSQL()})",
                        self::CLICK_PREFIX,
                        'es.email_id = cut.channel_id AND es.lead_id = cut.lead_id'
                    );
                }

                $event->addCampaignByChannelJoin(
                    $qb,
                    self::EMAIL_STATS_PREFIX,
                    'email',
                    ReportGeneratorEvent::CONTACT_PREFIX,
                    'email_id'
                );

                if ($this->companyReportData->eventHasCompanyColumns($event)) {
                    $event->addCompanyLeftJoin($qb);
                }

                if (!$event->hasGroupBy()) {
                    $qb->groupBy('es.id');
                }

                break;
        }
        $event->setQueryBuilder($qb);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGraphGenerate(ReportGraphEvent $event)
    {
        $graphs = $event->getRequestedGraphs();

        if (!$event->checkContext([self::CONTEXT_EMAIL_STATS, self::CONTEXT_EMAILS])) {
            return;
        }

        if ($event->checkContext(self::CONTEXT_EMAILS) && !in_array('mautic.email.graph.pie.read.ingored.unsubscribed.bounced', $graphs)) {
            return;
        }

        $qb = $event->getQueryBuilder();
        foreach ($graphs as $g) {
            $options      = $event->getOptions($g);
            $queryBuilder = clone $qb;
            /** @var ChartQuery $chartQuery */
            $chartQuery   = clone $options['chartQuery'];
            $origQuery    = clone $queryBuilder;
            // just limit date for contacts emails
            if ($event->checkContext(self::CONTEXT_EMAIL_STATS)) {
                $chartQuery->applyDateFilters($queryBuilder, 'date_sent', self::EMAIL_STATS_PREFIX);
            }

            switch ($g) {
                case 'mautic.email.graph.line.stats':
                    $chartQuery->setGeneratedColumnProvider($this->generatedColumnsProvider);
                    $chart     = new LineChart(null, $options['dateFrom'], $options['dateTo']);
                    $sendQuery = clone $queryBuilder;
                    $readQuery = clone $origQuery;
                    $readQuery->andWhere($qb->expr()->isNotNull('date_read'));
                    $failedQuery = clone $queryBuilder;
                    $failedQuery->andWhere($qb->expr()->eq('es.is_failed', ':true'));
                    $failedQuery->setParameter('true', true, 'boolean');
                    $chartQuery->applyDateFilters($readQuery, 'date_read', self::EMAIL_STATS_PREFIX);
                    $chartQuery->modifyTimeDataQuery($sendQuery, 'date_sent', self::EMAIL_STATS_PREFIX);
                    $chartQuery->modifyTimeDataQuery($readQuery, 'date_read', self::EMAIL_STATS_PREFIX);
                    $chartQuery->modifyTimeDataQuery($failedQuery, 'date_sent', self::EMAIL_STATS_PREFIX);
                    $sends  = $chartQuery->loadAndBuildTimeData($sendQuery);
                    $reads  = $chartQuery->loadAndBuildTimeData($readQuery);
                    $failes = $chartQuery->loadAndBuildTimeData($failedQuery);
                    $chart->setDataset($options['translator']->trans('mautic.email.sent.emails'), $sends);
                    $chart->setDataset($options['translator']->trans('mautic.email.read.emails'), $reads);
                    $chart->setDataset($options['translator']->trans('mautic.email.failed.emails'), $failes);
                    $data         = $chart->render();
                    $data['name'] = $g;

                    $event->setGraph($g, $data);
                    break;

                case 'mautic.email.graph.pie.ignored.read.failed':
                    $counts = $this->statRepository->getIgnoredReadFailed($queryBuilder);
                    $chart  = new PieChart();
                    $chart->setDataset($options['translator']->trans('mautic.email.read.emails'), $counts['read']);
                    $chart->setDataset($options['translator']->trans('mautic.email.failed.emails'), $counts['failed']);
                    $chart->setDataset(
                        $options['translator']->trans('mautic.email.ignored.emails'),
                        $counts['ignored']
                    );
                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-flag-checkered',
                        ]
                    );
                    break;

                case 'mautic.email.graph.pie.read.ingored.unsubscribed.bounced':
                    $queryBuilder->select('SUM(e.sent_count) as sent_count, SUM(e.read_count) as read_count, SUM(dnc.unsubscribed) as unsubscribed, SUM(dnc.bounced) as bounced');
                    $queryBuilder->resetQueryPart('groupBy');
                    $this->addDNCTableForEmails($queryBuilder);
                    $counts = $queryBuilder->execute()->fetch();
                    $chart  = new PieChart();
                    $chart->setDataset(
                        $options['translator']->trans('mautic.email.stat.read'),
                        $counts['read_count'] ?? 0
                    );
                    $chart->setDataset(
                        $options['translator']->trans('mautic.email.graph.pie.ignored.read.failed.ignored'),
                        (($counts['sent_count'] ?? 0) - ($counts['read_count'] ?? 0))
                    );
                    $chart->setDataset(
                        $options['translator']->trans('mautic.email.unsubscribed'),
                        $counts['unsubscribed'] ?? 0
                    );
                    $chart->setDataset(
                        $options['translator']->trans('mautic.email.bounced'),
                        $counts['bounced'] ?? 0
                    );

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-flag-checkered',
                        ]
                    );
                    break;

                case 'mautic.email.table.most.emails.sent':
                    $this->joinEmailsTableIfMissing($queryBuilder, $event);
                    $queryBuilder->select('e.id, e.subject as title, SUM(DISTINCT e. sent_count) as sent')
                        ->groupBy('e.id, e.subject')
                        ->orderBy('sent', 'DESC');
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->statRepository->getMostEmails($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-paper-plane-o';
                    $graphData['link']      = 'mautic_email_action';
                    $event->setGraph($g, $graphData);
                    break;

                case 'mautic.email.table.most.emails.read':
                    $this->joinEmailsTableIfMissing($queryBuilder, $event);
                    $queryBuilder->select('e.id, e.subject as title, SUM(DISTINCT e. read_count) as opens')
                        ->groupBy('e.id, e.subject')
                        ->orderBy('opens', 'DESC');
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->statRepository->getMostEmails($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-eye';
                    $graphData['link']      = 'mautic_email_action';
                    $event->setGraph($g, $graphData);
                    break;

                case 'mautic.email.table.most.emails.failed':
                    $this->joinEmailsTableIfMissing($queryBuilder, $event);
                    $queryBuilder->select(
                        'e.id, e.subject as title, count(CASE WHEN es.is_failed THEN 1 ELSE null END) as failed'
                    )
                        ->having('count(CASE WHEN es.is_failed THEN 1 ELSE null END) > 0')
                        ->groupBy('e.id, e.subject')
                        ->orderBy('failed', 'DESC');
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->statRepository->getMostEmails($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-exclamation-triangle';
                    $graphData['link']      = 'mautic_email_action';
                    $event->setGraph($g, $graphData);
                    break;

                case 'mautic.email.table.most.emails.unsubscribed':
                    $this->joinEmailsTableIfMissing($queryBuilder, $event);
                    $this->addDNCTableForEmailStats($queryBuilder);
                    $queryBuilder->select(
                        'e.id, e.subject as title, count(CASE WHEN dnc.id  and dnc.reason = '.DoNotContact::UNSUBSCRIBED.' THEN 1 ELSE null END) as unsubscribed'
                    )
                        ->having(
                            'count(CASE WHEN dnc.id and dnc.reason = '.DoNotContact::UNSUBSCRIBED.' THEN 1 ELSE null END) > 0'
                        )
                        ->groupBy('e.id, e.subject')
                        ->orderBy('unsubscribed', 'DESC');

                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->statRepository->getMostEmails($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-exclamation-triangle';
                    $graphData['link']      = 'mautic_email_action';
                    $event->setGraph($g, $graphData);
                    break;

                case 'mautic.email.table.most.emails.bounced':
                    $this->joinEmailsTableIfMissing($queryBuilder, $event);
                    $this->addDNCTableForEmailStats($queryBuilder);
                    $queryBuilder->select(
                        'e.id, e.subject as title, count(CASE WHEN dnc.id  and dnc.reason = '.DoNotContact::BOUNCED.' THEN 1 ELSE null END) as bounced'
                    )
                        ->having(
                            'count(CASE WHEN dnc.id and dnc.reason = '.DoNotContact::BOUNCED.' THEN 1 ELSE null END) > 0'
                        )
                        ->groupBy('e.id, e.subject')
                        ->orderBy('bounced', 'DESC');
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->statRepository->getMostEmails($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-exclamation-triangle';
                    $graphData['link']      = 'mautic_email_action';
                    $event->setGraph($g, $graphData);
                    break;

                case 'mautic.email.table.most.emails.read.percent':
                    $this->joinEmailsTableIfMissing($queryBuilder, $event);
                    $queryBuilder->select('e.id, e.subject as title, round(e.read_count / e.sent_count * 100) as ratio')
                        ->groupBy('e.id, e.subject')
                        ->orderBy('ratio', 'DESC');
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->statRepository->getMostEmails($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-tachometer';
                    $graphData['link']      = 'mautic_email_action';
                    $event->setGraph($g, $graphData);
                    break;
            }
            unset($queryBuilder);
        }
    }

    private function joinEmailsTableIfMissing(QueryBuilder $queryBuilder, ReportGraphEvent $event): void
    {
        if ($event->checkContext(self::CONTEXT_EMAIL_STATS) && !$this->isJoined($queryBuilder, MAUTIC_TABLE_PREFIX.'emails', self::EMAIL_STATS_PREFIX, self::EMAILS_PREFIX)) {
            $queryBuilder->leftJoin(self::EMAIL_STATS_PREFIX, MAUTIC_TABLE_PREFIX.'emails', self::EMAILS_PREFIX, 'e.id = es.email_id');
        }
    }

    /**
     * Add the Do Not Contact table to the query builder.
     */
    private function addDNCTableForEmails(QueryBuilder $qb): void
    {
        $table = MAUTIC_TABLE_PREFIX.'lead_donotcontact';

        if (!$this->isJoined($qb, $table, self::EMAILS_PREFIX, self::DNC_PREFIX)) {
            // DoNotContact stats with and without date range
            $subQuery = $this->db->createQueryBuilder();
            $subQuery->select(
                'SUM(IF(dnc.reason = '.DoNotContact::UNSUBSCRIBED.',1, 0)) as unsubscribed',
                'SUM(IF(dnc.reason = '.DoNotContact::BOUNCED.',1, 0)) as bounced',
                'dnc.channel_id as channel_id'
            )
                ->from(MAUTIC_TABLE_PREFIX.'lead_donotcontact', 'dnc')
                ->andWhere('dnc.channel=\'email\'')
                ->groupBy('dnc.channel_id');

            $subQuery->where(sprintf('%1$s BETWEEN :dateFrom AND :dateTo', 'dnc.date_added'));
            $qb->leftJoin('e', sprintf('(%s)', $subQuery->getSQL()), 'dnc', 'e.id = dnc.channel_id');
        }
    }

    /**
     * Add the Do Not Contact table to the query builder.
     */
    private function addDNCTableForEmailStats(QueryBuilder $qb)
    {
        $table = MAUTIC_TABLE_PREFIX.'lead_donotcontact';

        if (!$this->isJoined($qb, $table, self::EMAIL_STATS_PREFIX, self::DNC_PREFIX)) {
            $qb->leftJoin(
                self::EMAIL_STATS_PREFIX,
                $table,
                self::DNC_PREFIX,
                'es.email_id = dnc.channel_id AND dnc.channel=\'email\' AND es.lead_id = dnc.lead_id'
            );
        }
    }

    private function isJoined($query, $table, $fromAlias, $alias)
    {
        $joins = $query->getQueryParts()['join'];
        if (empty($joins) || (!empty($joins) && empty($joins[$fromAlias]))) {
            return false;
        }
        foreach ($joins[$fromAlias] as $join) {
            if (false !== strpos($join['joinTable'], $table) && $join['joinAlias'] == $alias) {
                return true;
            }
        }

        return false;
    }
}
