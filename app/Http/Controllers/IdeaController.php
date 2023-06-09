<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\Notification;
use Auth;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Session;
use ZipArchive;

class IdeaController extends Controller
{
    public function ideaIndex()
    {
        $ideas = Idea::all();
        $getCategory = Idea::value('categoryID');
        $categoryName = Category::where('categoryID', '=', $getCategory)->value('categoryName');
        $now = date("Y-m-d");
        $start = DB::table('academicyear')->value('open_date');
        $end = DB::table('academicyear')->value('close_date');
        if ($now <= $end && $now >= $start) {
            $passDate = 0;
        } else {
            $passDate = 1;
        }
        return view('ideas.index', compact('ideas', 'categoryName', 'passDate'));
    }
    public function getAddIdea()
    {
        $now = date("Y-m-d");
        $start = DB::table('academicyear')->value('open_date');
        $end = DB::table('academicyear')->value('close_date');
        if ($now <= $end && $now >= $start) {
            $categories = Category::all();
            $getAcaYears = DB::table('academicyear')->get();
            return view('ideas.add', compact('categories', 'getAcaYears'));
        } else {
            return redirect()->back();
        }

    }
    public function postAddIdea(Request $request)
    {
        $this->validate($request, [
            'ideaName' => 'required',
            'categoryID' => 'required',
            'ideaContent' => 'required'
        ]);
        $idea = new Idea;
        $idea->ideaName = $request->input('ideaName');
        $idea->categoryID = $request->input('categoryID');
        $idea->ideaContent = $request->input('ideaContent');
        $idea->uploader = Auth::user()->userID;
        if ($request->hasfile('document')) {
            $file = $request->file('document');
            $filename = 'doc_' . $idea->ideaName . '_' . time() . '.' . $file->extension();
            $file->move('documents', $filename);
            $idea->document = $filename;
        }
        $idea->save();
        $noti = new Notification;
        $noti->userID = Auth::user()->userID;
        $noti->notiContent = "Someone is added new idea";
        $noti->isRead = 0;
        $noti->notiFor = 'idea';
        $noti->save();
        return redirect('/');
    }
    public function getEditIdea($id_idea)
    {
        $now = date("Y-m-d");
        $start = DB::table('academicyear')->value('open_date');
        $end = DB::table('academicyear')->value('close_date');
        $idea = Idea::findOrFail($id_idea);
        $getAcaYears = DB::table('academicyear')->get();
        if ($idea->uploader == Auth::user()->userID && $now <= $end && $now >= $start) {
            $categories = Category::all();
            return view('ideas.edit', compact('idea', 'categories', 'getAcaYears'));
        }
        return redirect()->back();
    }
    public function postEditIdea(Request $request, $id_idea)
    {
        $idea = Idea::findOrFail($id_idea);
        $this->validate($request, [
            'ideaName' => 'required',
            'categoryID' => 'required',
            'ideaContent' => 'required'

        ]);
        $idea->ideaName = $request->input('ideaName');
        $idea->categoryID = $request->input('categoryID');
        $idea->ideaContent = $request->input('ideaContent');
        if ($request->hasfile('document')) {
            $des = 'documents/' . $idea->document;
            File::delete($des);
            $file = $request->file('document');
            $filename = 'doc_' . $idea->ideaName . '_' . time() . '.' . $file->extension();
            $file->move('documents', $filename);
            $idea->document = $filename;
        }
        $idea->update();
        return redirect('/');
    }
    public function deleteIdea($id_idea)
    {
        $idea = Idea::findOrFail($id_idea);
        $now = date("Y-m-d");
        $start = DB::table('academicyear')->value('open_date');
        $end = DB::table('academicyear')->value('close_date');
        if ($idea->uploader == Auth::user()->userID || $now <= $end && $now >= $start) {
            $des = 'documents/' . $idea->document;
            File::delete($des);
            $idea->delete();
            return redirect('/');
        }
        return redirect()->back();
    }
    public function viewIdea(Request $request, $id_idea)
    {
        $idea = Idea::findOrFail($id_idea);
        $request->session()->put('ideaID', $id_idea);
        $getCategory = Idea::value('categoryID');
        $document = Idea::where('ideaID', session()->get('ideaID'))->value('document');
        $viewIdea = 'idea_' . $id_idea;
        if (!Session::has($viewIdea)) {
            Idea::where('ideaID', $id_idea)->increment('view');
            Session::put($viewIdea, 1);
        }
        $now = date("Y-m-d");
        $start = DB::table('academicyear')->value('open_date');
        $end = DB::table('academicyear')->value('close_date');
        if ($now <= $end && $now >= $start) {
            $passDate = 0;
        } else {
            $passDate = 1;
        }
        $categoryName = Category::where('categoryID', '=', $getCategory)->value('categoryName');
        $comments = Comment::orderByDesc('created_at')->get();
        return view('ideas.view', compact('idea', 'categoryName', 'comments', 'document', 'passDate'));
    }
    public function likeIdea(Request $request, $id_idea)
    {
        $idea = Idea::findOrFail($id_idea);
        $idea->likeCount = $idea->likeCount + 1;
        $idea->update();
        return redirect()->route('viewIdea', ['id' => $request->session()->get('ideaID')]);
    }
    public function dislikeIdea(Request $request, $id_idea)
    {
        $idea = Idea::findOrFail($id_idea);
        $idea->likeCount = $idea->likeCount - 1;
        $idea->update();
        return redirect()->route('viewIdea', ['id' => $request->session()->get('ideaID')]);
    }
    public function delDoc($id_doc)
    {
        $idea = Idea::findOrFail($id_doc);
        $idea->document = null;
        $des = 'documents/' . $idea->document;
        File::delete($des);
        $idea->update();
        return redirect()->back();
    }
    public function downloadAllDoc(Request $request)
    {
        $countDoc = Idea::count('document');
        if ($countDoc > 0) {
            if ($request->session()->get('zipName')) {
                $des = 'temp/' . $request->session()->get('zipName');
                File::delete($des);
            }
            $zip = new ZipArchive();
            $fileName = 'ums_all_doc_' . Carbon::now() . '.' . 'zip';
            if ($zip->open(('temp/' . $fileName), ZipArchive::CREATE) == TRUE) {
                $files = File::files('documents');
                foreach ($files as $key => $value) {
                    $relativeName = basename($value);
                    $zip->addFile($value, $relativeName);
                }
                $zip->close();
            }
            $request->session()->put('zipName', $fileName);
            return response()->download('temp/' . $fileName);
        } else {
            return redirect()->back();
        }
    }
}
