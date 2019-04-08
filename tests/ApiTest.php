<?php

namespace Tests;

class ApiTest extends BaseTestCase
{
    /**
     * Test that the issues API route returns a JSON response.
     */
    public function testApiIssuesEndpointLoadsDataFromJira()
    {
        $response = $this->runApp('GET', '/api/issues');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json;charset=utf-8', $response->getHeader('content-type')[0]);
    }

    /**
     * Test that the issues API route returns a JSON response with our query.
     */
    public function testApiIssuesEndpointLoadsDataFromJiraWithQuery()
    {
        $response = $this->runApp('GET', '/api/issues?createdAfter=2019-04-01&createdBefore=2019-04-13&status=In+Progress&maxResults=50');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json;charset=utf-8', $response->getHeader('content-type')[0]);
    }

    /**
     * Test that the issues API route won't accept a post request.
     */
    public function testPostApiIssuesEndpointNotAllowed()
    {
        $response = $this->runApp('POST', '/api/issues', ['test']);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertStringContainsString('Method not allowed', (string) $response->getBody());
    }
}
