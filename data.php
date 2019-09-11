<?php

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest' ) {
    header('Location: index.php');
    exit;
}

session_start();

if (empty($_SESSION['email'])) {
    echo json_encode(['error' => true]);
    exit;
}

require_once 'settings.php';

$data = file_exists($json_file) ? file_get_contents($json_file) : '';

$users = json_decode(file_get_contents($users_file), true);

$workers = [];
foreach ($users as $email => $user)
    $workers[$email] = $user[0];
    
if (!empty($data)) {
    $json = json_decode($data, true);
    
    if (time() < $json['last_updated'] + 3600) { //still fresh data!
        echo $data;
        exit;
    }
}

function get($a, $b, $c) {
    $y   = explode($b, $a);
    $len = sizeof($y);
    $arr = [];
    for ($i = 1; $i < $len; $i++)
        $arr[] = explode($c, $y[$i])[0];
    
    return $arr;
}

function getAssigned($url, $workers) {
    $data = file_get_contents($url . 'feed');

    $xml = new SimpleXMLElement($data);
    $ns = $xml->getNamespaces(true);

    $users = [];
    foreach ($xml->channel->item as $item)
        $users[] = strtolower($item->children($ns['dc'])->creator);
        
    foreach (array_reverse($users) as $user)
        if (in_array($user, $workers))
            return $user;
        
    return '';
}

$prev = file_exists($json_file) ? file_get_contents($json_file) : '';
if (empty($prev))
    $prev = [
        'data' => []
    ];
else
    $prev = json_decode($prev, true);

$posts = [];

for ($page = 1; $page <= 10; $page++) {
    $contents = file_get_contents('https://wordpress.org/support/plugin/wordfence/page/' . $page);
    $root = get($contents, '<ul id="bbp-topic-', '</ul>');

    foreach ($root as $thread) {
        $title = get($thread, '<li class="bbp-topic-title">', '</a>')[0];
        $title = explode('/">', $title)[1];
        
        $url = get($thread, '<a class="bbp-topic-permalink" href="', '">')[0];

        if (substr($title, 0, 79) == '<span class="resolved" aria-label="Resolved" title="Topic is resolved."></span>')
            continue; //ignore any resolved threads
        
        $closed = get($thread, '" class="', '">')[0];
        $closed = strpos($closed, 'status-closed') === false ? false : true;
        
        if ($closed)
            continue; //ignore any closed threads
        
        $author  = get($thread, 'class="bbp-author-name">', '</a></span>')[0];
        $voices  = get($thread, '<li class="bbp-topic-voice-count">', '</li>')[0];
        $replies = get($thread, '<li class="bbp-topic-reply-count">', '</li>')[0];
        
        $last_active = get($thread, '<li class="bbp-topic-freshness">', '<p class="bbp-topic-meta">')[0];
        $last_active = get($last_active, '">', '</a>')[0];
        
        $last_user = get($thread, '<span class="bbp-topic-freshness-author">', '</a></span>')[0];
        $last_user = get($last_user, 'class="bbp-author-name">', '</a></span>');
        if (empty($last_user))
            $last_user = $author;
        else
            $last_user = $last_user[0];
        
        //the last comment is a worker, so assign the ticket to them as pending
        if (in_array(strtolower($last_user), $workers)) {
            $status = 'pending';
            $assigned = strtolower($last_user);
        
        //only the poster has posted, or there are no replies
        } elseif ($voices == 1 || $replies == 0) {
            $status = 'unassigned';
            $assigned = '';
        
        //at least two distinct users, and at least 2 replies (so worker might be the second post)
        } elseif ($voices > 1 && $replies > 1) {
            $dirty = true;
            
            foreach ($prev['data'] as $prev_thread) {
                if ($prev_thread[1] == '<a target="_blank" href="' . htmlspecialchars($url) . '">' . strip_tags($title) . '</a>' && $prev_thread[3] == $replies) {
                    $assigned = $prev_thread[6];
                    $status   = $prev_thread[7];
                    
                    $dirty = false;
                    
                    break;
                }
            }
            
            if ($dirty) {
                $assigned = getAssigned($url, $workers);
                $status = 'assigned';
                if ($assigned == '')
                    $status = 'unassigned';
            }
        } else {
            $status = 'unassigned';
            $assigned = '';
        }
        
        $posts[] = [
            0,
            '<a target="_blank" href="' . htmlspecialchars($url) . '">' . strip_tags($title) . '</a>',
            $author,
            $replies,
            $last_user,
            $last_active,
            $assigned,
            $status
        ];
    }
}

usort($posts, function($a, $b) {
    return strtotime($a[5]) < strtotime($b[5]);
});

foreach ($posts as $index => &$post) {
    $post[0] = $index + 1;
}

$header = [[
    'name'      => 'id',
    'title'     => '#',
    'format'    => 'int',
    'sort-dir'  => 'asc',
    'sortable'  => true
], [
    'name'      => 'title',
    'title'     => 'Title',
], [
    'name'      => 'author',
    'title'     => 'Author',
], [
    'name'      => 'replies',
    'title'     => 'Replies',
    'format'    => 'int'
], [
    'name'      => 'last_reply',
    'title'     => 'Last Reply'
], [
    'name'      => 'last_active',
    'title'     => 'Last Active'
], [
    'name'      => 'assigned_to',
    'title'     => 'Assigned To',
    'show'      => false
], [
    'name'      => 'status',
    'title'     => 'Status',
    'show'      => false

]];

$output = [
    'header'       => $header,
    'data'         => array_reverse($posts),
    'last_updated' => time()
];

$json = json_encode($output);

file_put_contents($json_file, $json);

echo $json;
