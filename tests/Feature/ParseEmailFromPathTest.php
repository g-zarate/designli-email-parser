<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ParseEmailFromPathTest extends TestCase
{

    public function testJsonOnlyInAttachment()
    {
        $filePath = storage_path('app/test_emails/json_only_in_attachment.eml');

        $this->assertFileExists($filePath, "The test email file does not exist at: $filePath");

        $response = $this->getJson('/api/parse-email?file_path=' . urlencode($filePath));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'attachment',
            'text_body',
            'urls',
        ]);
        $responseData = $response->json();

        $this->assertIsArray($responseData['attachment'],  "Attachment does not contain valid JSON");
        $this->assertIsString($responseData['text_body'], "Text body should be a string and not a JSON");
        $this->assertIsString($responseData['urls'], "URLs should be a string and not a JSON");
    }

    public function testJsonOnlyInBody()
    {
        $filePath = storage_path('app/test_emails/json_only_in_body.eml');

        $this->assertFileExists($filePath, "The test email file does not exist at: $filePath");

        $response = $this->getJson('/api/parse-email?file_path=' . urlencode($filePath));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'attachment',
            'text_body',
            'urls',
        ]);
        $responseData = $response->json();

        $this->assertIsString($responseData['attachment'],  "Attachment should be a string and not a JSON");
        $this->assertIsArray($responseData['text_body'], "Text body does not contain valid JSON");
        $this->assertIsString($responseData['urls'], "URLs should be a string and not a JSON");
    }

    public function testJsonInAttachmentAndInBody()
    {
        $filePath = storage_path('app/test_emails/json_in_attachment_and_body.eml');

        $this->assertFileExists($filePath, "The test email file does not exist at: $filePath");

        $response = $this->getJson('/api/parse-email?file_path=' . urlencode($filePath));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'attachment',
            'text_body',
            'urls',
        ]);
        $responseData = $response->json();

        $this->assertIsArray($responseData['attachment'],  "Attachment does not contain valid JSON");
        $this->assertIsArray($responseData['text_body'], "Text body does not contain valid JSON");
        $this->assertIsString($responseData['urls'], "URLs should be a string and not a JSON");
    }

    public function testOnlyJsonInUrls()
    {
        $filePath = storage_path('app/test_emails/two_urls_with_json.eml');

        $this->assertFileExists($filePath, "The test email file does not exist at: $filePath");

        $response = $this->getJson('/api/parse-email?file_path=' . urlencode($filePath));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'attachment',
            'text_body',
            'urls',
        ]);
        $responseData = $response->json();

        $this->assertIsString($responseData['attachment'],  "Attachment should be a string and not a JSON");
        $this->assertIsString($responseData['text_body'], "Text body should be a string and not a JSON");
        $this->assertIsArray($responseData['urls'], "URLs does not contain valid JSON");
    }

    public function testJsonInAttachmentInUrlAndInUrlInPage()
    {
        $filePath = storage_path('app/test_emails/json_in_attachment_url_and_in_page.eml');

        $this->assertFileExists($filePath, "The test email file does not exist at: $filePath");

        $response = $this->getJson('/api/parse-email?file_path=' . urlencode($filePath));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'attachment',
            'text_body',
            'urls',
        ]);
        $responseData = $response->json();

        $this->assertIsArray($responseData['attachment'],  "Attachment does not contain valid JSON");
        $this->assertIsString($responseData['text_body'], "Text body should be a string and not a JSON");
        $this->assertIsArray($responseData['urls'], "URLs does not contain valid JSON");
    }

    public function testInvalidFile()
    {
        $filePath = storage_path('app/test_emails/does_not_exists.eml');

        $this->assertFileDoesNotExist($filePath, "The test email file should not exist at: $filePath");
    }


    public function testEntityNotProcessed()
    {
        $filePath = storage_path('app/test_emails/test_file.txt');

        $this->assertFileExists($filePath, "The test email file does not exist at: $filePath");

        $response = $this->getJson('/api/parse-email?file_path=' . urlencode($filePath));
        $response->assertStatus(422);
    }
}
