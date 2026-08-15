<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $panelUrl = route('panel.dashboard');
    $adminUrl = route('admin.dashboard');
    return <<<blade
<a  href="$panelUrl">panel</a>
<a  href="$adminUrl">admin</a>

blade;
});
