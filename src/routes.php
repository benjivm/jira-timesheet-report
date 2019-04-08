<?php

use Slim\Http\Request;
use Slim\Http\Response;

// The base route
$app->get('/', function (Request $request, Response $response) {
    // Flash filters
    foreach ($request->getQueryParams() as $key => $value) {
        $this->flash->addMessageNow($key, $value);
    }

    // Get project assignees for the desired role
    $usersInRoleHttpResponse = $this->httpClient->request('GET', 'project/' . getenv('JIRA_PROJECT') . '/role/' . getenv('JIRA_ASSIGNEE_ROLE_ID'));

    // Build the assignees array
    $assignees = array_map(function ($assignee) {
        return [
            'shortName' => $assignee['name'],
            'fullName'  => $assignee['displayName'],
        ];
    }, $usersInRoleHttpResponse->toArray()['actors']);

    // Return our home view with the data
    return $this->view->render($response, 'home.php', [
        'projectName' => getenv('JIRA_PROJECT'),
        'assignees'   => $assignees,
        'flash'       => $this->flash,
    ]);
});

$app->group('/api', function () use ($app) {
    // Get the issues API
    $app->get('/issues', function (Request $request, Response $response) {
        // Duration
        $duration = new Khill\Duration\Duration();

        // Our query parameters for JIRA's search API
        $query = [
            'jql'    => 'project = "' . getenv('JIRA_PROJECT') . '" AND timespent > 0',
            'fields' => [
                'key',
                'issuetype',
                'summary',
                'customfield_10202', // customer
                'customfield_11405', // location
                'customfield_11401', // name
                'customfield_11403', // phone
                'created',
                'assignee',
                'reporter',
                'timespent',
            ],
        ];

        // Append JQL statements based on query filters
        if (! empty($request->getParam('createdAfter'))) {
            $query['jql'] .= sprintf(' AND created >= "%s 00:00"', date('Y/m/d', strtotime($request->getParam('createdAfter'))));
        }

        if (! empty($request->getParam('createdBefore'))) {
            $query['jql'] .= sprintf(' AND created <= "%s 00:00"', date('Y/m/d', strtotime($request->getParam('createdBefore'))));
        }

        if (! empty($request->getParam('status')) && $request->getParam('status') !== 'Any') {
            $query['jql'] .= sprintf(' AND statusCategory = "%s"', $request->getParam('status'));
        }

        if (! empty($request->getParam('assignee'))) {
            $query['jql'] .= ' AND assignee = ' . $request->getParam('assignee');
        }

        if (! empty($request->getParam('reporter'))) {
            $query['jql'] .= ' AND reporter = ' . $request->getParam('reporter');
        }

        if (! empty($request->getParam('maxResults'))) {
            $query['maxResults'] = $request->getParam('maxResults');
        }

        // Request issues from the JIRA API
        $httpResponse = $this->httpClient->request('POST', 'search', ['json' => $query]);

        // Map the results
        $issues = array_map(function ($issue) use ($duration) {
            return [
                'key'               => $issue['key'],
                'issueLink'         => getenv('JIRA_URL') . '/browse/' . $issue['key'],
                'type'              => $issue['fields']['issuetype'],
                'summary'           => $issue['fields']['summary'],
                'customer'          => $issue['fields']['customfield_10202'] . ', ' . $issue['fields']['customfield_11405'],
                'contactName'       => $issue['fields']['customfield_11401'],
                'contactPhone'      => $issue['fields']['customfield_11403'],
                'timeSpent'         => $duration->humanize($issue['fields']['timespent']),
                'timeSpentInHours'  => number_format($issue['fields']['timespent'] / 3600, 2) . ' hours',
                'created'           => $issue['fields']['created'],
                'assignee'          => $issue['fields']['assignee']['displayName'],
                'assigneeAvatarUrl' => $issue['fields']['assignee']['avatarUrls']['16x16'],
            ];
        }, $httpResponse->toArray()['issues']);

        return $response->withJson([
            'data' => $issues,
        ]);
    });
});
