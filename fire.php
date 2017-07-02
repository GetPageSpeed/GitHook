<?php

// Secret token to make sure that request comes from trusted resource
define('GIT_HOOK_SECRET', getenv('GIT_HOOK_SECRET'));

// Project name (will appear in hook emails)
define('GIT_HOOK_PROJECT_NAME', getenv('GIT_HOOK_PROJECT_NAME'));

// What branch pushes we should react upon
define('GIT_HOOK_BRANCH', getenv('GIT_HOOK_BRANCH'));

// Who will get notifications about pushes to this branch
define('GIT_HOOK_EMAILS', getenv('GIT_HOOK_EMAILS'));


define('REF_REGEX', '#^refs/heads/' . GIT_HOOK_BRANCH . '$#');


if (!isset($_GET['code']) && $_GET['code'] != $secret) {
    exit('404');
}

// go up one directory
chdir(dirname(__DIR__));

$result = `/usr/bin/git pull 2>&1`
        . `/usr/local/bin/n98-magerun cache:flush`
        . `/usr/local/bin/cachetool opcache:reset`
        . `/usr/local/bin/cachetool stat:clear`;
// . exec('../artisan migrate')
// . exec('cd ../ && composer install')
// . exec('curl -X PURGE -H "User-Agent: W3 Total Cache/.*" http://example.com/.*')
// . opcache_reset();

$payload = json_decode(file_get_contents('php://input'));

if ($payload && preg_match(REF_REGEX, $payload->ref)) {

    echo 'Deploy v.1.1.1<br>done';

    $message = '
        <style>
        table {
            border: 1px solid #eee;
            border-collapse: collapse;
        }

	table td {
            border: 1px solid #eee;
        }

	</style>

        Deploy status: <b>' . $result . '</b><br><br>
        <a href="' . $payload->compare . '">Compare changes</a><br><br>
        <table border=1>
            <tr>
                <th>Commit Date</th>
                <th>Commit Description</th>
                <th>Author</th>
            </tr>
    ';

    foreach ($payload->commits as $commit) {
        $message .= '
        <tr>
        <td>' . $commit->timestamp . '</td>
            <td>' . $commit->message . '</td>
            <td>' . $commit->author->name . '</td>
        </tr>';
    }

    $message .= '</table>' . $result;

    $emails = explode(',', GIT_HOOK_EMAILS);

    foreach ($emails as $email) {
        mail(
            $email,
            sprintf("%s deployed %s", GIT_HOOK_PROJECT_NAME, GIT_HOOK_BRANCH),
            $message,
            'Content-type: text/html; charset=iso-8859-1' . "\r\n"
        );
    }

    echo 'Success';
}

echo 'Done';
