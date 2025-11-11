<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get(config('discussions.route_prefix', 'discussions'), \Wave\Plugins\Discussions\Components\Discussions::class)->name('discussions');
    Route::get(config('discussions.route_prefix', 'discussions') . '/category/{category}', \Wave\Plugins\Discussions\Components\Discussions::class)->name('discussions.category');
    Route::get(config('discussions.route_prefix_post', 'discussion') . '/{discussion_slug}', \Wave\Plugins\Discussions\Components\Discussion::class)->name('discussion');
});
