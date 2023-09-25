<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class PagesController extends Controller
{
    public function index(Request $request) {
        $pages = Page::latest();

        if($request->keyword != ''){
            $pages = $pages->where('name','like','%'.$request->keyword.'%');
        }

        $pages = $pages->paginate(10);

        return view('admin.pages.list',[
            'pages' => $pages
        ]);
    }

    public function create() {
        return view('admin.pages.create');

    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'=>false,
                'errors' =>$validator->errors()
            ]);
        }

        $page = new Page;
        $page->name = $request->name;
        $page->slug = $request->slug;
        $page->content = $request->content;
        $page->save();

        $message = 'Page added successfuly';

        session()->flash('success',$message);

        return response()->json([
            'status'=>true,
            'message' => $message
        ]);

    }

    public function edit(Request $request, $id) {

        $page = Page::find($id);

        if ($page == null) {
            session()->flash('error','Halaman Tidak Ditemukan');
            return redirect()->route('pages.index');
        }

        return view('admin.pages.edit',[
            'page' => $page
        ]);
    }

    public function update (Request $request, $id) {

        $page = Page::find($id);

        if ($page == null) {
            session()->flash('error','Halaman Tidak Ditemukan');
            return response()->json([
                'status' => true,
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'=>false,
                'errors' =>$validator->errors()
            ]);
        }

        
        $page->name = $request->name;
        $page->slug = $request->slug;
        $page->content = $request->content;
        $page->save();

        $message = 'Page updated successfuly';

        session()->flash('success',$message);

        return response()->json([
            'status'=>true,
            'message' => $message
        ]);
    }

    public function destroy($id) {
        $page = Page::find($id);

        if ($page == null) {
            session()->flash('error','Halaman Tidak Ditemukan');
            return response()->json([
                'status' => true,
            ]);
        }

        $page->delete();
        
        $message = 'Page deleted successfuly';

        session()->flash('success',$message);

        return response()->json([
            'status'=>true,
            'message' => $message
        ]);
    }
}
