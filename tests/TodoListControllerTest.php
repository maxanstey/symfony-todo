<?php

namespace App\Tests;

use App\Controller\TodoListController;
use App\Entity\TodoListItem;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class TodoListControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $randomLongString;

    public function testValidFormSubmitsAndCanBeDeleted(): void
    {
        $randomTitle = substr($this->randomLongString, 0, TodoListItem::MIN_TITLE_LENGTH);

        $crawler = $this->createNewTaskWithTitle($randomTitle);

        $flashNotice = $crawler->filter('.flash-notice')->first();

        $this->assertTrue(TodoListController::CREATE_MESSAGE === $flashNotice->innerText());

        $mostRecentTaskTitle = $crawler->filter('input:not(#todo_list_item_form_0_title)')->last();

        $this->assertTrue($mostRecentTaskTitle->attr('value') === $randomTitle);

        $deleteButtonId = str_replace('_title', '_delete', $mostRecentTaskTitle->attr('id'));

        $this->client->submit($crawler->filter('#'.$deleteButtonId)->form());

        $crawler = $this->client->followRedirect();

        $flashNotice = $crawler->filter('.flash-notice')->first();

        $this->assertTrue(TodoListController::DELETE_MESSAGE === $flashNotice->innerText());
    }

    public function testFormWithInvalidTitlesDoNotSubmit(): void
    {
        $randomTitle = substr($this->randomLongString, 0, TodoListItem::MAX_TITLE_LENGTH + 1);

        if (strlen($randomTitle) < TodoListItem::MAX_TITLE_LENGTH + 1) {
            $this->fail(
                sprintf(
                    'The random title "%s" is too short for testing; try increasing the length of random_bytes().',
                    $randomTitle
                )
            );
        }

        $crawler = $this->createNewTaskWithTitle($randomTitle);

        $flashNotice = $crawler->filter('.flash-notice')->first();

        $this->assertTrue(str_contains($flashNotice->innerText(), 'too long'));

        if (TodoListItem::MIN_TITLE_LENGTH <= 1) {
            return;
        }

        $randomTitle = substr($this->randomLongString, 0, TodoListItem::MIN_TITLE_LENGTH - 1);

        $crawler = $this->createNewTaskWithTitle($randomTitle);

        $flashNotice = $crawler->filter('.flash-notice')->first();

        $this->assertTrue(str_contains($flashNotice->innerText(), 'too short'));
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->client = $this->createClient();

        $this->randomLongString = bin2hex(random_bytes(32));

        $this->truncateEntities();

        parent::setUp();
    }

    /**
     * @return void
     * @throws DbalException
     * @see https://symfonycasts.com/screencast/phpunit/control-database
     */
    private function truncateEntities(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $connection = $entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();

        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        }

        foreach ($entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames() as $entity) {
            $query = $databasePlatform->getTruncateTableSQL(
                $entityManager->getClassMetadata($entity)->getTableName()
            );

            $connection->executeStatement($query);
        }

        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function createNewTaskWithTitle(string $taskTitle): Crawler
    {
        $crawler = $this->client->request('GET', '/');

        $form = $crawler->filter('#todo_list_item_form_0_save')->form();

        $form->get('todo_list_item_form_0[title]')->setValue($taskTitle);

        $this->client->submit($form);

        return $this->client->followRedirect();
    }
}
