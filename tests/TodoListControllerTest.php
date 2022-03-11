<?php

namespace App\Tests;

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

        $this->assertTrue($flashNotice->innerText() === 'Task created successfully.');

        $mostRecentTaskTitle = $crawler->filter('input:not(#todo_list_item_form_0_title)')->last();

        $this->assertTrue($mostRecentTaskTitle->attr('value') === $randomTitle);

        $deleteButtonId = str_replace('_title', '_delete', $mostRecentTaskTitle->attr('id'));

        $this->client->submit($crawler->filter('#' . $deleteButtonId)->form());

        $crawler = $this->client->followRedirect();

        $flashNotice = $crawler->filter('.flash-notice')->first();

        $this->assertTrue(str_contains($flashNotice->innerText(), 'deleted'));
    }

    public function testFormWithInvalidTitlesDoNotSubmit(): void
    {
        $randomTitle = substr($this->randomLongString, 0, TodoListItem::MAX_TITLE_LENGTH + 1);

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
     * @return void
     * @throws Exception
     */
    protected function setUp(): void {
        $this->client = $this->createClient();

        $this->randomLongString =  bin2hex(random_bytes(32));

        parent::setUp();
    }

    private function createNewTaskWithTitle(string $taskTitle): Crawler
    {
        $crawler = $this->client->request('GET', '/');

        $formSaveButton = $crawler->filter('#todo_list_item_form_0_save');

        $form = $formSaveButton->form();

        $form->get('todo_list_item_form_0[title]')->setValue($taskTitle);

        $this->client->submit($form);

        return $this->client->followRedirect();
    }
}
