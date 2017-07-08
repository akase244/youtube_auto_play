# youtube auto play
youtube auto play from own tweets.

## これは何？
シードを実行すると、Twitterの全ツイート履歴を読み込み、ツイートの中からYouTubeのリンクが記載されているものをDBに保存します。
YouTube Auto PlayのURLにアクセスすると、YouTubeのリンクを含むツイートをランダムで10ツイート抽出し、自動的にYouTubeを再生します。


## セットアップ方法

### データベースを作成する（MySQLの例）
```
$ mysql -uroot -p
mysql> CREATE DATABASE youtube_auto_play CHARACTER SET utf8mb4;
mysql> exit
```

### .envファイルを準備する
```
$ cp .env.example .env
$ php artisan key:generate
$ vim .env

APP_URL=http://localhost <- 環境に合わせて変更する

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=youtube_auto_play <- ↑の手順で作成したものを指定
DB_USERNAME=homestead <- 環境に合わせて変更する
DB_PASSWORD=homestead <- 環境に合わせて変更する

YOUTUBE_API_KEY=null <- https://developers.google.com/youtube/registering_an_application?hl=ja を参照
TWITTER_ACCOUNT=null <- 自分のTwitterアカウントを指定する
```

### Twitterの全ツイート履歴をダウンロード
![Twitterの全ツイート履歴](https://github.com/akase244/youtube_auto_play/blob/master/app/resources/assets/images/tweets_history.png)をダウンロードする

### マイグレーション実行
```
$ php artisan migrate
```

### storage/appディレクトリ直下に全ツイート履歴のCSV(tweets.csv)を置く
```
$ ls -l /PATH_TO/storage/app/tweets.csv
-rwxr-xr-x. 1 vagrant vagrant 18143657  7月  8 14:52 2017 /PATH_TO/storage/app/tweets.csv
```

### シードを実行
```
$ php artisan db:seed
[Start] import data. [2017/07/08 14:53:22]
[End] import data. [2017/07/08 15:07:58]
Seeded: TweetTableSeeder
```

### URLにアクセスする
https://youtube_auto_play/youtube