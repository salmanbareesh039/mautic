<?php

declare(strict_types=1);

/*
 * @copyright   2020 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\IntegrationsBundle\Tests\Functional\Sync\Notification;

use Mautic\CoreBundle\Entity\Notification;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\IntegrationsBundle\Sync\Notification\BulkNotification;
use PHPUnit\Framework\Assert;

class BulkNotificationTest extends MauticMysqlTestCase
{
    /**
     * @var BulkNotification
     */
    private $bulkNotification;

    protected function setUp()
    {
        parent::setUp();

        $this->bulkNotification = $this->container->get('mautic.integrations.sync.notification.bulk_notification');
    }

    public function testNotifications(): void
    {
        $notificationRepository = $this->em->getRepository(Notification::class);

        $this->bulkNotification->addNotification('dup1', 'message 1', 'Integration name', 'Lead', 'lead', 0, 'link 1');
        $this->bulkNotification->addNotification('dup2', 'message 2', 'Integration name', 'Lead', 'lead', 0, 'link 2');
        $this->bulkNotification->addNotification('dup1', 'message 3', 'Integration name', 'Lead', 'lead', 0, 'link 3');

        Assert::assertCount(0, $notificationRepository->findAll());

        $this->bulkNotification->flush();

        $notifications = $notificationRepository->findAll();
        Assert::assertCount(2, $notifications);
        $this->assertNotification($notifications[0], 'dup1', 'message 1', 'link 1');
        $this->assertNotification($notifications[1], 'dup2', 'message 2', 'link 2');
    }

    private function assertNotification(Notification $notification, string $deduplicate, string $message, string $link): void
    {
        Assert::assertSame(md5($deduplicate), $notification->getDeduplicate());
        Assert::assertSame(sprintf('<a href="/s/contacts/view">%s</a> failed to sync with message, &quot;%s&quot;', $link, $message), $notification->getMessage());
    }
}
