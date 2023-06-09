<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Idea;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function categoryIndex()
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }
    public function getAddCategory()
    {
        return view('categories.add');
    }
    public function postAddCategory(Request $request)
    {
        $this->validate($request, [
            'categoryName' => 'required|unique:categories,categoryName',
            'categoryDesc' => 'required',
        ]);
        $category = new Category;
        $category->categoryName = $request->input('categoryName');
        $category->categoryDesc = $request->input('categoryDesc');
        $category->save();
        return redirect('/categories');
    }
    public function getEditCategory($id_category)
    {
        $category = Category::findOrFail($id_category);
        return view('categories.edit', compact('category'));
    }
    public function postEditCategory(Request $request, $id_category)
    {

        $category = Category::findOrFail($id_category);
        $this->validate($request, [
            'categoryName' => "required|unique:categories,categoryName",
            'categoryDesc' => 'required',
        ]);
        $category->categoryName = $request->input('categoryName');
        $category->categoryDesc = $request->input('categoryDesc');
        $category->update();
        return redirect('/categories');
    }
    public function deleteCategory($id_category)
    {
        $category = Category::findOrFail($id_category);
        if ($category->idea()->exists()) {
            return redirect('/categories')->with('notify', 'catcannotdelete');
        } else {
            $category->delete();
            return redirect('/categories');
        }
    }
}
