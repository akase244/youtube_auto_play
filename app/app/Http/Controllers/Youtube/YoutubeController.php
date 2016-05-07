<?php

namespace App\Http\Controllers\Youtube;

use Illuminate\Http\Request;

use App\Tweet;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class YoutubeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $count = Tweet::all()->count();
        $tweets = [];
        $client = new \GuzzleHttp\Client();
        if ($count > 0) {
            $max = ($count > 10) ? 10 : $count;
            for ($i = 0; $i < $max; $i++) {
                while (true) {
                    $offset = mt_rand(1, $count) - 1;
                    $limit = 1;
                    $tweet = DB::table('tweets')->skip($offset)->take($limit)->get();
                    if ($tweet) {
                        $url = 'https://www.googleapis.com/youtube/v3/videos';
                        $res = $client->get($url,[
                            'query'=>[
                                'key' => env('YOUTUBE_API_KEY'),
                                'id' => $tweet[0]->youtube_id,
                                'part' => 'snippet,contentDetails',
                            ],
                        ]);
                        $body = json_decode($res->getBody());
                        if ($body->items) {
                            $index = count($tweets);
                            $tweets[$index] = $tweet[0];
                            $tweets[$index]->title = $body->items[0]->snippet->title;
                            $tweets[$index]->thumbnail = $body->items[0]->snippet->thumbnails->default->url;
                        }
                        break;
                    }
                }
            }
        }
        return view('youtube.index')
            ->with('tweets', $tweets);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
