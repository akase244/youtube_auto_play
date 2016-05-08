<!DOCTYPE html>
<html>
    <head>
        <title>YouTube Auto Player</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <script src="https://code.jquery.com/jquery-1.12.3.min.js"></script>

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
        </style>
        <script>
            // 2. This code loads the IFrame Player API code asynchronously.
            var tag = document.createElement('script');

            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            // 3. This function creates an <iframe> (and YouTube player)
            //    after the API code downloads.
            var player;
            var playIndex;
            var videoIds = [];
            var tweetIds = [];
@if (count($tweets) > 0)
    @foreach ($tweets as $tweet)
            videoIds.push('{{ $tweet->youtube_id }}');
            tweetIds.push('{{ $tweet->tweet_id }}');
    @endforeach
@endif
            function onYouTubeIframeAPIReady() {
                if (videoIds.length > 0) {
                    // 1曲目を自動再生
                    playIndex = 0;
                    changeVideo();
                }
            }
            function loadPlayer(playIndex) {
                if (!player) {
                    player = new YT.Player('player', {
                        height: '240',
                        width: '400',
                        videoId: videoIds[playIndex],
                        events: {
                            'onReady': onPlayerReady,
                            'onStateChange': onPlayerStateChange
                        }
                    });
                } else {
                    player.loadVideoById(videoIds[playIndex]);
                }
            }

            // 4. The API will call this function when the video player is ready.
            function onPlayerReady(event) {
                event.target.playVideo();
            }

            // 5. The API calls this function when the player's state changes.
            //    The function indicates that when playing a video (state=1),
            //    the player should play for six seconds and then stop.
            function onPlayerStateChange(event) {
                // 再生中
                if (event.data == YT.PlayerState.PLAYING) {
                }
                // 停止
                if (event.data == YT.PlayerState.ENDED) {
                    if (playIndex == (videoIds.length - 1)) {
                        playIndex = 0;
                    } else {
                        playIndex = playIndex + 1;
                    }
                    changeVideo();
                }
            }

            $(document).ready(function(){
                // 曲を直接選択
                $('table.play_lists tr').on('click', function() {
                    playIndex = $('table.play_lists tr').index(this);
                    changeVideo();
                });

                // 前へボタン
                $('span.prev').on('click', function() {
                    if (playIndex == 0) {
                        playIndex = videoIds.length - 1;
                    } else {
                        playIndex = playIndex - 1;
                    }
                    changeVideo();
                });

                // 次へボタン
                $('span.next').on('click', function() {
                    if (playIndex == (videoIds.length - 1)) {
                        playIndex = 0;
                    } else {
                        playIndex = playIndex + 1;
                    }
                    changeVideo();
                });
            });

            // 元ツイートを表示
            function addOriginalTweet() {
                $('div.original_tweet').children().remove();
                $blockquote = $('<blockquote>')
                        .addClass('twitter-tweet')
                        .attr('data-lang', 'ja')
                        .append(
                            $('<a>').attr('href', 'https://twitter.com/{{ env('TWITTER_ACCOUNT') }}/status/' + tweetIds[playIndex])
                        );
                $('div.original_tweet').append($blockquote);
                $script = $('<script>')
                        .attr('async', '')
                        .attr('src', '//platform.twitter.com/widgets.js')
                        .attr('charset', 'utf-8');
                $('div.original_tweet').append($script);
            }

            // 動画を変更
            function changeVideo() {
                // 選択した曲の背景色を変更
                $('table.play_lists tr').css('background-color', '#FFFFFF');
                $('table.play_lists tr').eq(playIndex).css('background-color', '#2D88B3');

                // 選択した曲の文字色を変更
                $('table.play_lists tr').css('color', '#000000');
                $('table.play_lists tr').eq(playIndex).css('color', '#FFFFFF');

                // 動画を読み込み
                //player.loadVideoById(videoIds[playIndex]);
                loadPlayer(playIndex);

                // 元ツイートを表示
                addOriginalTweet();

                // タイトルを表示
                $('span.title').text($('table.play_lists tr td span').eq(playIndex).text());
            }
        </script>
    </head>
    <body>
        <div class="container">
            <div class="content" style="width: 600px;">
                <!-- 1. The <iframe> (and video player) will replace this <div> tag. -->
                <div>
                    <span class="prev" style="font-size: xx-large; cursor: pointer;">◁</span>
                    <div id="player" style="vertical-align: middle;"></div>
                    <span class="next" style="font-size: xx-large; cursor: pointer;">▷</span>
                    <div><span class="title" style="font-size: large; color: #2D88B3;"></span></div>
                </div>
@if (count($tweets) > 0)
                <table class="play_lists" style="width: 500px; margin: 30px auto;">
    @foreach ($tweets as $key => $tweet)
                <tr>
                    <th><img src="{{ $tweet->thumbnail }}" style="vertical-align: middle;"></th>
                    <td style="text-align: left;"><span>{{ $key + 1 }}. {{ $tweet->title }}</span></td>
                </tr>
    @endforeach
                </table>
@endif
                <div class="original_tweet" style="width: 500px; margin: 10px auto;"></div>
            </div>
        </div>
    </body>
</html>
