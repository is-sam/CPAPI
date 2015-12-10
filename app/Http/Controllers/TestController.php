<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Link;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class TestController extends Controller
{
    /**
     *
     */
    public function list_all() {
        $links = DB::select('SELECT * FROM links');
        return view('links.list', compact('links'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create() {
        return view('links.create');
    }

    /**
     * @return string
     */
    public function store() {
        $url = Input::get('url');
        $link = Link::firstOrCreate(['url' => $url]);
        return view('links.success', compact('link'));
    }

    public function show($id) {
        $link = Link::findOrFail($id);
        return redirect($link->url, 301);
    }
}
