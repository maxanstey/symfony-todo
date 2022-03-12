<?php

namespace App\Tests;

use App\Controller\TodoListController;
use App\Entity\TodoListItem;
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

        parent::setUp();
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
