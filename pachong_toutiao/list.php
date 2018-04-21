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

//获取详情页
$url  = 'http://www.365yg.com/api/pc/feed/?category=video&utm_source=toutiao&widen=1&max_behot_time=0&max_behot_time_tmp=' . time();

$curl = new \Curl\Curl();
// $curl->setUserAgent('MyUserAgent/0.0.1 (+https://www.example.com/bot.html)');
// $curl->setReferrer('https://www.example.com/url?url=https%3A%2F%2Fwww.example.com%2F');
// $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
// $curl->setCookie('key', 'value');


$curl->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8');
$curl->setHeader('Accept-Language', 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7');
$curl->setHeader('Connection', 'keep-alive');
$curl->setHeader('Host', 'www.365yg.com');
$curl->setHeader('Upgrade-Insecure-Requests', '1');
$curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');
$curl->get($url);
$body = '';
if ($curl->error) {
    die('没有返回列表数据');
} else {
    $body = $curl->response;
}
// die('1111');
// $body = file_get_contents($url);
// file_put_contents('123.php',$body);
// var_dump($body);
// $list = json_decode($body, true);
// var_dump($list);die;
// if (true === empty($list))
// {
//     die('没有返回列表数据');
// }

foreach ($body->data as $key => $value)
{
    $res = $db->count($table, ['video_id' => trim($value->video_id)]);
    if (!$res)
    {
        $db->insert($table, [
                'group_id'           => trim($value->group_id),
                'video_id'           => trim($value->video_id),
                'tag'                => trim($value->tag),
                'chinese_tag'        => trim($value->chinese_tag),
                'comments_count'     => $value->comments_count ?? 0,
                'video_play_count'   => trim($value->video_play_count),
                'video_duration_str' => trim($value->video_duration_str),
                'title'              => trim($value->title),
                'abstract'           => trim($value->abstract),
                'middle_image'       => trim($value->middle_image),
                'image_url'          => trim($value->image_url),
                'source_url'         => trim($value->source_url),
                'created_at'         => date('Y-m-d H:i:s', time()),
            ]
        );
    }
}

//获取详情页html
function getUtlHtml($url = 'http://www.365yg.com/group/6517499035203404295/')
{
    $client = Client::getInstance();
    $client->getEngine()->setPath('/usr/local/bin/phantomjs');
    //$client->getProcedureCompiler()->disableCache();//默认启用缓存,加大效率
    //$client->getProcedureCompiler()->enableCache();//禁用缓存
    //$client->isLazy(); //告诉客户在渲染前等待所有资源

    //设置请求并设置超时时间
    $request = $client->getMessageFactory()->createRequest($url, 'GET', 3000);
    //$request->setDelay(5);
    //$request->setTimeout(3000); //延迟3秒等待所有资源加载
    $request->addHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8');
    $request->addHeader('Accept-Language', 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7');
    $request->addHeader('Connection', 'keep-alive');
    $request->addHeader('Host', 'www.365yg.com');
    $request->addHeader('Upgrade-Insecure-Requests', '1');
    $request->addHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');

    $response = $client->getMessageFactory()->createResponse();
    $client->send($request, $response);

    $body = '';
    if ($response->getStatus() === 200)
    {
        $body = $response->getContent();
    }

    return $body;
}







