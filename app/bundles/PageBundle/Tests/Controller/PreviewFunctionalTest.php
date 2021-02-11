<?php

declare(strict_types=1);

/*
 * @copyright   2021 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PageBundle\Tests\Controller;

use DateTime;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewFunctionalTest extends MauticMysqlTestCase
{
    public function testPreviewAdmin(): void
    {
        $page = $this->createPage();
        $this->em->flush();

        $this->client->request(Request::METHOD_GET, "/page/preview/{$page->getId()}");

        // Anonymous visitor is not allowed to access preview
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->loginUser('admin');
        $this->client->request(Request::METHOD_GET, "/page/preview/{$page->getId()}");

        // Admin user is allowed to access preview
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    private function createPage(): Page
    {
        $page = new Page();
        $page->setIsPublished(true);
        $page->setDateAdded(new DateTime());
        $page->setTitle('Preview settings test - main page');
        $page->setAlias('page-main');
        $page->setTemplate('Blank');
        $page->setCustomHtml('Test Html');
        $page->setLanguage('en');
        $this->em->persist($page);

        return $page;
    }
}
