<?php

namespace Tests;

class MainPageTest extends BaseTestCase
{
    public $project;

    /**
     * Get required env variables for tests.
     */
    protected function setUp(): void
    {
        $this->project = getenv('JIRA_PROJECT');
        parent::setUp();
    }

    /**
     * Test that the index route returns a rendered response.
     */
    public function testGetMainPage()
    {
        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString($this->project . ' Timesheet', (string) $response->getBody());
    }

    /**
     * Test that the index route with optional query parameters returns a rendered response and sets form data.
     */
    public function testGetMainPageWithQuery()
    {
        $response = $this->runApp('GET', '/?createdAfter=2019-04-01&createdBefore=2019-04-13&status=In+Progress&maxResults=50');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString($this->project . ' Timesheet', (string) $response->getBody());
        $this->assertStringContainsString('<input type="date" class="form-control" id="createdAfter" name="createdAfter" value="2019-04-01">', (string) $response->getBody());
        $this->assertStringContainsString('<input type="date" class="form-control" id="createdBefore" name="createdBefore" value="2019-04-13">', (string) $response->getBody());
        $this->assertStringContainsString('<option value="In Progress" selected>In Progress</option>', (string) $response->getBody());
        $this->assertStringContainsString('<option value="50" selected>50</option>', (string) $response->getBody());
    }
}
