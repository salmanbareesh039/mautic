<?php

/*
 * @copyright   2019 Mautic. All rights reserved
 * @author      Mautic.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MarketplaceBundle\Service;

use MauticPlugin\MarketplaceBundle\Api\Connection;
use MauticPlugin\MarketplaceBundle\Collection\PackageCollection;

class PluginCollector
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function collectPackages(): PackageCollection
    {
        $payload = $this->connection->getPlugins();

        return PackageCollection::fromArray($payload['results']);
    }
}
