<?php

namespace Tests\Functional;

class SearchTest extends BaseTestCase {

    /**
     * Test that empty search query shows message
     */
    public function testEmptySearch() {
        $response = $this->runApp('GET', '/search/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Please enter search query', (string)$response->getBody());
    }

    /**
     * Test that ACC 2000 returns a JSON string that contains course names and description
     */
    public function testSearch() {
        $response = $this->runApp('GET', '/search/ACC 2000');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('{"CourseNum":"ACC 2000","CourseName":"Accounting Principles I","Description":"Introduction to basic principles, concepts, and theoretical framework of financial accounting with the emphasis on its use by economically rational decision makers. Topics include the decision-making environment and the accounting cycles, processes, and statements."', (string)$response->getBody());
    }

    /**
     * Test that a search with no results shows a message
     */
    public function testNoResultsSearch() {
        $response = $this->runApp('GET', '/search/no result');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('returned no Results', (string)$response->getBody());
    }

}