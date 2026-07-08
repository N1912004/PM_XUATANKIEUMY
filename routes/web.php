<?php

use App\Exports\RecipeExport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/admin/recipes-export', function () {
    return Excel::download(
        new RecipeExport,
        'ngan-hang-thuc-don-'.now()->format('Ymd-His').'.xlsx',
    );
})->middleware('auth')->name('recipes.export-file');

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'vi'])) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('lang.switch');
