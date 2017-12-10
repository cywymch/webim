<?php
$server = new swoole_websocket_server("0.0.0.0", 9502);
$redis = new Redis();
$redis->connect('116.62.58.170', '6379');
$redis->auth('chen123456');
$server->on('open', function ($server, $req) use ($redis) {
    $redis->hSet("fd_" . $req->fd, 'fd', $req->fd);
    $redis->hSet("fd_" . $req->fd, 'member_id', $_SESSION['member_id']);
    $redis->hSet("fd_" . $req->fd, 'nickname', $_SESSION['nickname']);
    echo "connection open: {$req->fd}\n";
});

$server->on('message', function ($server, $frame) {
    echo "received message: {$frame->data}\n";
    $data = json_decode($frame->data, true);
    if ($data['fd']) {
        $server->push($data['fd'], $data['msg']);
    }
});

$server->on('close', function ($server, $fd) use ($redis) {
    echo "connection close: {$fd}\n";
    $redis->del("fd_" . $fd);
});

$server->start();