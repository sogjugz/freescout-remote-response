<?php

use App\Misc\Helper;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'web', 'prefix' => Helper::getSubdirectory(), 'namespace' => 'Modules\SsRemoteResponse\Http\Controllers'], function()
{
    Route::post('/ss-remote-response/generate', 'SsRemoteResponseController@generate');

    Route::get('/ss-remote-response/is_enabled', 'SsRemoteResponseController@checkIsEnabled');

    Route::get('/mailbox/{mailbox_id}/ss-remote-response-settings', [
        'uses' => 'SsRemoteResponseController@settings', 
        'middleware' => ['auth', 'roles'], 
        'roles' => ['admin']
    ])->name('ss-remote-response.settings');

    Route::post('/mailbox/{mailbox_id}/ss-remote-response-settings', [
        'uses' => 'SsRemoteResponseController@saveSettings', 
        'middleware' => ['auth', 'roles'], 
        'roles' => ['admin']
    ]);
});
