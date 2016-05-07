<?php

use Illuminate\Database\Seeder;
use App\Tweet;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;

class TweetTableSeeder extends Seeder
{
    const CSV_FILENAME = 'tweets.csv';
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('[Start] import data. ['.date('Y/m/d H:i:s').']');
        // 削除
        Tweet::truncate();

        $config = new LexerConfig();
        $config->setDelimiter(',');
        $lexer = new Lexer($config);
        $interpreter = new Interpreter();
        $interpreter->addObserver(function(array $row) {
            // tweet_id
            $tweetId = $row[0];
            // tweet_text
            $tweetText = $row[5];
            // expanded_urls
            $expectYoutubeUrls = $row[9];
            
            // expanded_urlsに値がセットされている場合
            if ($expectYoutubeUrls) {
                /**
                 * ツイート内に1件だけURLが存在する場合、expanded_urlsはそのURLがセットされている
                 * ,"https://youtu.be/fWffzdd2XMM"
                 * 
                 * ツイート内に複数のURLが存在する場合、expanded_urlsはカンマ区切りでURLがセットされている
                 * ,"https://youtu.be/-uwacLOnyKA,https://youtu.be/d6XsYyP1QXw,https://youtu.be/7rzD52hOChs"
                 */
                $expectYoutubeUrls = explode(',', $expectYoutubeUrls);
                foreach ($expectYoutubeUrls as $expectYoutubeUrl) {
                    if (strpos($expectYoutubeUrl, '//youtu.be/') !== false) {
                        // [youtu.be/xxxxxx]形式のURLを[www.youtube.com/watch?v=xxxxxx]形式に変換する
                        $originalUrl = $this->getOriginalUrl($expectYoutubeUrl);
                        if(isset($originalUrl)){
                            $this->saveTweet($tweetId, $tweetText, $originalUrl);
                        }
                    } elseif (strpos($expectYoutubeUrl, '//www.youtube.com/watch?v=') !== false) {
                        $this->saveTweet($tweetId, $tweetText, $expectYoutubeUrl);
                    }
                }
            } else {
                $pattern = '/https?:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+/';
                preg_match_all($pattern, $tweetText, $matches);
                foreach ($matches[0] as $url) {
                    // ツイート時に短縮URL形式に変換されている場合があるので元のURLに戻す
                    $originalUrl = $this->getOriginalUrl($url);
                    if(isset($originalUrl)){
                        if (strpos($originalUrl, '//www.youtube.com/watch?v=') !== false) {
                            $this->saveTweet($tweetId, $tweetText, $originalUrl);
                        }
                    }
                }
            }
        });
        $lexer->parse(base_path(self::CSV_FILENAME), $interpreter);

        $this->command->info('[End] import data. ['.date('Y/m/d H:i:s').']');
    }
    
    private function saveTweet ($tweetId, $tweetText, $expectYoutubeUrl)
    {
        // クエリーパラメータを抽出
        $queries = explode('&', parse_url($expectYoutubeUrl, PHP_URL_QUERY));
        foreach ($queries as $query) {
            $param = explode('=', $query);
            if ($param[0] === 'v') {
                $youtubeUrl = $expectYoutubeUrl;
                $youtubeId = rtrim($param[1], '_');
                Tweet::create([
                    'tweet_id' => $tweetId,
                    'tweet_text' => $tweetText,
                    'youtube_url' => $youtubeUrl,
                    'youtube_id' => $youtubeId,
                ]);
            }
            $param = null;
        }
    }
    
    private function getOriginalUrl ($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        $originalUrl = '';
        try {
            // get_headers()ではtimeout設定ができなかったためcurl_execを利用
            $headers = curl_exec($curl);
        } catch (Exception $e) {
            return $originalUrl;
        }
        $headers = explode("\n", $headers);
        foreach ($headers as $header) {
            // headerの[Location:]を取得することで短縮URLの場合にはオリジナルURLを辿ることができる
            if (strpos($header, 'Location: ') !== false) {
                // trimは文末の制御文字混入対策
                $originalUrl = trim(substr($header, (strpos($header, 'Location: ') + strlen('Location: '))));
            }
        }
        return $originalUrl;
    }
}
