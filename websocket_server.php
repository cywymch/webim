<?php
$server = new swoole_websocket_server("0.0.0.0", 9502);
$redis = new Redis();
$redis->connect('116.62.58.170', '6379');
$redis->auth('*********');
$server->on('open', function ($server, $req) use ($redis) {
    $redis->hSet("fd_" . $req->fd, 'fd', $req->fd);
    echo "connection open: {$req->fd}\n";
});

$server->on('message', function ($server, $frame) use($redis) {
    echo "received message: {$frame->data}\n";
    $data = json_decode($frame->data, true);
    switch ($data['type']) {
        case 'connect':
            $redis->hSet("fd_" . $frame->fd, 'member_id', $data['member_id']);
            $redis->hSet("fd_" . $frame->fd, 'nickname', $data['nickname']);
            $server->push($frame->fd, json_encode(['fd'=>$frame->fd,'message'=>'success','type'=>'connect']));
            $_data = $redis->keys("fd_*");
            $user_list = [];
            foreach ($_data as $key=>$vo){
                $user_list[$key]['fd'] = $redis->hGet($vo,'fd');
                $user_list[$key]['member_id'] = $redis->hGet($vo,'member_id');
                $user_list[$key]['nickname'] = $redis->hGet($vo,'nickname');
            }
            //array_push($user_list, ['fd'=>$frame->fd,'member_id'=>$data['member_id'],'nickname'=>$data['nickname']]);;
            foreach($server->connections as $fd) {
                $server->push($fd, json_encode($user_list));
            }
            break;
        case 'chat':
            $data['nickname'] = $redis->hGet("fd_".$frame->fd,'nickname');
            $data['member_id'] = $redis->hGet("fd_".$frame->fd,'member_id');
            $data['time'] = date("Y-m-d H:i:s");
            $data['type'] = 'chat';
            $data['message'] = $data['message'];
            $server->push($data['fd'], json_encode($data));
            break;
    }
    
});

$server->on('close', function ($server, $fd) use ($redis) {
    echo "connection close: {$fd}\n";
    $redis->del("fd_" . $fd);
});

$server->start();