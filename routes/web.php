<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $panelUrl = route('panel.index');
    $adminUrl = route('admin.index');
    return <<<blade
<a class="" href="$panelUrl">panel</a>
<a class="" href="$adminUrl">admin</a>


blade;
});
