<?php

namespace Tests\Functional;

class ApiTest extends BaseTestCase
{
    /**
     * Test that the issues API route returns a rendered response.
     */
    public function testApiIssuesEndpointLoadsDataFromJira()
    {
        $response = $this->runApp('GET', '/api/issues');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('{"data":[{"key":"', (string) $response->getBody());
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
