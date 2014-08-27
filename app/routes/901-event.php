<?php

\Route::group(['namespace' => 'Manage\Event', 'prefix' => 'event/{event}', 'before' => 's5_manage_event'], function() {
    \Route::controller('/venue', 'VenueController');

    \Route::get('/', 'IndexController@getIndex');
    \Route::post('/update-registration-status', 'IndexController@postUpdateRegistrationStatus');
});