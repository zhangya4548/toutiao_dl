<?php
set_time_limit(0);

require __DIR__ . '/../vendor/autoload.php';

use JonnyW\PhantomJs\Client;

//获取详情页
$url = 'http://www.365yg.com/group/6545952575663899150/';
$body = getUtlHtml($url);

if ($body)
{
    //1打开http://toutiao.com/a6309254755004875010/，查看网页源代码获取videoid = 0425d8f0c2bb425d9361c0eb2eeb4f16
    //2拼接成如下字符串/video/urls/v/1/toutiao/mp4/{videoid}?r={randint}。其中:
    //videoid	通过上一个步骤获取 0425d8f0c2bb425d9361c0eb2eeb4f16
    //randint	任意16位长整形字符串 7937864853677161
    //3将第二步拼接的字符串进行crc32校验（php有crc32函数）,获取值为十六进制需转化成十进制
    //crc32("/video/urls/v/1/toutiao/mp4/0425d8f0c2bb425d9361c0eb2eeb4f16?r=7937864853677161") = 4040162423
    //4拼接Urlhttp://i.snssdk.com/video/urls/v/1/toutiao/mp4/{videoid}?r={randint}&s={checksum}
    //checksum	crc32校验值
    //5. 访问拼接Url http://i.snssdk.com/video/urls/v/1/toutiao/mp4/0425d8f0c2bb425d9361c0eb2eeb4f16?r=2330415823304158&s=4218775840
    //返回数据中main_url为视频地址（需要base64解码）。
    //6最终下载地址为 base64解码(main_url)

    //打开http://toutiao.com/a6309254755004875010/，查看网页源代码获取videoid = 0425d8f0c2bb425d9361c0eb2eeb4f16
    $pattern = "/videoid:'(.*?)',/";
    preg_match($pattern, $body, $match);
    if (true === empty($match[1]))
    {
        die('未找到videoid');
    }
    $videoid = $match[1];

    //生成randint 任意16位长整形字符串
    $randint = mt_rand(1000000000000000, 9999999999999999);

    //生成checksum	crc32校验值
    $checksum = crc32("/video/urls/v/1/toutiao/mp4/" . $videoid . "?r=" . $randint);

    //访问拼接Url
    $mainUrl = 'http://i.snssdk.com/video/urls/v/1/toutiao/mp4/' . $videoid . '?r=' . $randint . '&s=' . $checksum;

    //获取main_url
    $mainRes = file_get_contents($mainUrl);
    $mainRes = json_decode($mainRes, true);
    if (true === empty($mainRes))
    {
        die('没有main_url数据');
    }
    $mainUrl = $mainRes['data']['video_list']['video_1']['main_url'];
    $mainUrl = base64_decode($mainUrl);

    //下载
    download($mainUrl, $videoId.'.mp4');
}

//下载文件
function download($file_source, $file_target) {
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'w+b');
    if (!$rh || !$wh) {
        return false;
    }

    while (!feof($rh)) {
        if (fwrite($wh, fread($rh, 4096)) === FALSE) {
            return false;
        }
        echo ' ';
        flush();
    }

    fclose($rh);
    fclose($wh);

    return true;
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






