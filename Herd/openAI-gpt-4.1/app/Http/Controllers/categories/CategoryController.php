<?php

namespace App\Http\Controllers\categories;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return Inertia::render('categories/index', [
            'categories' => $categories,
            'success' => session('success'),
        ]);
    }

    public function create()
    {
        return Inertia::render('categories/create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:25',
            'description' => 'required|string|max:1000'
        ]);
        // Store the category in the database
        Category::create($request->all());
        // Redirect to the categories index page
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    // handel millon records
    // public function getAll()
    // {
    //     $users = Cache::store('redis')->remember('users_all', 60, function () {
    //         return User::all(); // Fetch data from DB if not in cache
    //     });
    // }

    // public function clearCache()
    // {
    //     Cache::forget('users_all'); // Clear cached users data
    // }

    // public function storeUser()
    // {
    //     $this->clearCache();
    // }
}
