<?php


namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        // $page = Markdown::convertToHtml(view('markdown.about'));
        return view('pages.about');
    }

}