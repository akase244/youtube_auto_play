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
    </head>
    <body>
        <div class="container">
            <div class="content">
                <!-- 1. The <iframe> (and video player) will replace this <div> tag. -->
                <div id="player"></div>

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
    @foreach ($tweets as $key => $tweet)
                    videoIds.push('{{ $tweet->youtube_id }}');
                    tweetIds.push('{{ $tweet->tweet_id }}');
    @endforeach
@endif
                    function onYouTubeIframeAPIReady() {
                        if (videoIds.length > 0) {
                            // 1曲目を自動再生
                            playIndex = 0;
                            loadPlayer(videoIds[playIndex]);
                            $('table.play_lists tr:first').css('background-color', '#2D88B3');
                            addOriginalTweet();
                        }
                    }
                    function loadPlayer(videoId) {
                        player = new YT.Player('player', {
                            height: '240',
                            width: '400',
                            videoId: videoId,
                            events: {
                                'onReady': onPlayerReady,
                                'onStateChange': onPlayerStateChange
                            }
                        });
                    }

                    // 4. The API will call this function when the video player is ready.
                    function onPlayerReady(event) {
                        event.target.playVideo();
                    }

                    // 5. The API calls this function when the player's state changes.
                    //    The function indicates that when playing a video (state=1),
                    //    the player should play for six seconds and then stop.
                    function onPlayerStateChange(event) {
                        if (event.data == YT.PlayerState.PLAYING) {
                        }
                        if (event.data == YT.PlayerState.ENDED) {
                            if (playIndex == 9) {
                                playIndex = 0;
                            } else {
                                playIndex = playIndex + 1;
                            }
                            $('table.play_lists tr').css('background-color', '#FFFFFF');
                            $('table.play_lists tr').eq(playIndex).css('background-color', '#2D88B3');
                            player.loadVideoById(videoIds[playIndex]);
                            addOriginalTweet();
                        }
                    }

                    $(document).ready(function(){
                        $('table.play_lists tr').on('click', function() {
                            $(this).siblings().css('background-color', '#FFFFFF');
                            $(this).css('background-color', '#2D88B3');
                            playIndex = $('table.play_lists tr').index(this);
                            player.loadVideoById(videoIds[playIndex]);
                            addOriginalTweet();
                        });
                    });

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
                </script>
@if (count($tweets) > 0)
                <table class="play_lists" style="width: 500px;">
    @foreach ($tweets as $key => $tweet)
                <tr>
                    <th><img src="{{ $tweet->thumbnail }}" style="vertical-align: middle;"></th>
                    <td><span>{{ $key + 1 }}. {{ $tweet->title }}</span></td>
                </tr>
    @endforeach

                </table>
@endif
            <div class="original_tweet"></div>
            </div>
        </div>
    </body>
</html>
