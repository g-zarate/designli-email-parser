<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\EmailParserService;
use App\Http\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class EmailParserController extends Controller
{
    public function parseEmail(Request $request, FileStorageService $fileStorageService, EmailParserService $emailParserService): JsonResponse
    {

        $rules = [
            'email_file' => 'required|file',
        ];
        if ($request->file('email_file')->getClientOriginalExtension() !== 'eml') {
            return response()->json(['errors' => ['email_file' => 'The email file must be an EML file.']], 422);
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $filePath = $fileStorageService->uploadFile($request->file('email_file'));
        $data = $emailParserService->parseEmail($filePath);

        return response()->json($data);

    }
}
