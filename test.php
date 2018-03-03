<?php
ini_set('memory_limit', '1024M');

include 'vendor/autoload.php';
include 'GoogleTokenGenerator.php';
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Jieba;
use Overtrue\Pinyin\Pinyin;

Jieba::init();
Finalseg::init();
$pinyin = new Pinyin();
// $seg_list = Jieba::cut("PHP是世界上最好的语言");
$seg_list = Jieba::cut($argv[1]);
$result_str = "";
foreach ($seg_list as $key => $value) {
	if ($key % 2 == 0) {
		if (is_word($value) == 1) {
			$result_str .= $value;
		} else {
			$result_str .= EnglishTranslate($value);
		}
	} else {
		foreach ($pinyin->convert($value) as $keys => $values) {
			$result_str .= $values;
		}
	}
}
echo $result_str;

function EnglishTranslate($chinese) {
	$headers = array(
		'origin' => 'https://translate.google.cn',
		'accept-encoding' => 'gzip, deflate, br',
		'accept-language' => 'zh-CN,zh;q=0.8,ja;q=0.6',
		'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
		'x-chrome-uma-enabled' => '1',
		'content-type' => 'application/x-www-form-urlencoded;charset=UTF-8',
		'accept' => '*/*',
		'referer' => 'https://translate.google.cn/',
		'authority' => 'translate.google.cn',
		'x-client-data' => 'CI22yQEIorbJAQj6nMoBCKmdygE=',
	);
	$data = array(
		'q' => $chinese,
	);
	$response = Requests::post('https://translate.google.cn/translate_a/single?client=t&sl=auto&tl=en&hl=zh&dt=at&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&ie=UTF-8&oe=UTF-8&pc=9&otf=2&ssel=0&tsel=0&kc=9&tk=' . GoogleTokenGenerator::TL($chinese), $headers, $data);
	$result = json_decode($response->body, true);
	return $result[0][0][0];
}
function is_word($str = '') {
	if (trim($str) == '') {
		return '';
	}
	$m = mb_strlen($str, 'utf-8');
	$s = strlen($str);
	if ($s == $m) {
		return 1;
	}
	if ($s % $m == 0 && $s % 3 == 0) {
		return 2;
	}
	return 3;
}