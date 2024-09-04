<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\EmailParserService;
use App\Http\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
        $data = $emailParserService->parseEmail($filePath, true);
        return response()->json($data);
    }

    public function parseEmailFromPath(Request $request, EmailParserService $emailParserService): JsonResponse
    {
        $rules = [
            'file_path' => 'required|string',
        ];
        $validator = Validator::make($request->query(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        $filePath = $request->query('file_path');
        if (!is_file($filePath)) {
            return response()->json(['errors' => ['file_path' => 'The specified file path must be an absolute path and point to an existing file.']], 404);
        }
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'eml') {
            return response()->json(['errors' => ['file_path' => 'The file must be an EML file.']], 422);
        }
        $data = $emailParserService->parseEmail($filePath, false);
        return response()->json($data);
    }
}
