<?php

use App\Http\Controllers\Test\TestUserController;
use App\Mail\NewsletterMail;
use App\Models\User;
use App\Models\Category;
use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DemoNotificanion;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Lara\Notifications\LaraNotificanion;

// Panel CustomInputs components
// Panel CustomInputs components
Route::middleware(['web', 'locales', 'auth', 'role:admin|superadmin'])
    ->prefix('test')->name('test.')
    ->group(function () {
        Route::resource('users', TestUserController::class);
        Route::get('users/populate', [TestUserController::class, 'populate'])->name('users.populate');
        Route::post('users/bulk-delete', [TestUserController::class, 'bulkDelete'])->name('users.bulk-delete');
    });

Route::get('/db-replace', function () {
    DB::table('articles')->update(['content' => DB::raw("REPLACE(content, 'https://img.icons8.com/bubbles/100/google-logo.jpg', 'https://raw.githubusercontent.com/atomjoy/icons/refs/heads/main/laptop/mac3.webp')")]);
});

Route::get('/seed', function () {
    set_time_limit(600);
    // DB::delete('DELETE from subscribers where id > 150');
    // DB::delete('DELETE from newsletters where newsletters.id > 15');
    // Subscriber::factory(55)->create(['status' => 'confirmed']);
    return 'Ok';
});

Route::get('/gallery', function () {
    return view('gallery');
});

Route::get('/notify', function () {
    // Make user
    $user = User::factory()->make([
        // 'email' => 'atomjoy.official@gmail.com'
        // 'email' => 'mluk123@proton.me'
    ]);

    // Send email
    // Notification::sendNow($user, new LaraNotificanion());

    // Display email
    return (new LaraNotificanion())->toMail(User::first());
    // return (new DemoNotificanion())->toMail(User::first());
});

Route::get('/mail', function () {
    $n = Newsletter::latest('id')->first();
    return new NewsletterMail($n, Subscriber::first());
});

Route::get('/count', function () {
    return Category::withCount(['articles' => function ($q) {
        $q->whereIn('status', ['published', 'archived']);
    }])->with(['articles' => function ($query) {
        $query->select('articles.id', 'category_id', 'user_id', 'title', 'slug', 'excerpt', 'image', 'published_at')
            ->with(['user' => function ($q) {
                $q->select('id', 'name', 'avatar', DB::raw('concat(\'/image/\', avatar) as avatar_src'));
            }])->whereIn('status', ['published', 'archived'])->latest('published_at');
    }])->where('id', 1)->get();

    return Category::withCount('subcategories')
        ->with(['subcategories'])->orderBy('name')->get();

    return Category::withCount('articles')->with('articles')
        ->where('status', 'visible')
        ->whereNull('category_id')->get();

    // Count users with status
    // return Subscriber::select('status as name', DB::raw('count(status) as status_count'))->distinct()->groupBy('status')->get();

    // Count users with role
    // return Role::withCount(['users' => function ($query) {
    //     $query->distinct();
    // }])->get();
});
