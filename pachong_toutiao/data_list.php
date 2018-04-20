<?php
set_time_limit(0);

require __DIR__ . '/../vendor/autoload.php';

use JonnyW\PhantomJs\Client;
use Beanbun\Lib\Db;

Db::$config = [
    'zhihu' => [
        'server'        => '127.0.0.1',
        'port'          => '3306',
        'username'      => 'root',
        'password'      => 'zhangya@4548',
        'database_name' => 'dahuzhi',
        'database_type' => 'mysql',
        'charset'       => 'utf8',
    ],
];

$table = 'pachong_toutiao';
$db    = Db::instance('zhihu');

$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 10;
$page = ($page - 1) * $limit;

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? 0;
$comments_count = $_GET['comments_count'] ?? 0;
$video_play_count = $_GET['video_play_count'] ?? 0;
$orderKey = 'id';

$order = $_GET['order'] ?? 'DESC';

$count = $db->count($table);

$data = $db->select($table, ['id',
    'group_id',
    'video_id',
    'status',
    'tag',
    'chinese_tag',
    'comments_count',
    'video_play_count',
    'video_duration_str',
    'title' ,
    'abstract',
    'middle_image',
    'source_url' ,
    'created_at' ,
], [
    "ORDER" => ['video_play_count' => 'DESC','status' => 'ASC',],
    "LIMIT" => [$page, $limit],
    'status'=>0
    ], [

]);
//var_dump( $db->log() );
//var_dump($count,$data);


$res = [
    'code'=>0,
    'msg'=>'ok',
    'count'=>$count,
    'data'=>$data,
];

exit(json_encode($res));
//
//$data = $db->get($table, [], [
//    "post.content",
//    "account.user_name",
//    "account.location"
//], [
//    "ORDER" => "post.rate"
//]);







