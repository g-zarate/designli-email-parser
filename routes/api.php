<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmailParserController;

Route::post('/parse-email', [EmailParserController::class, 'parseEmail']);

Route::get('/parse-email', [EmailParserController::class, 'parseEmailFromPath']);
