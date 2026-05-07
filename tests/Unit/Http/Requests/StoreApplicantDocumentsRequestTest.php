<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Applicant\StoreApplicantDocumentsRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreApplicantDocumentsRequestTest extends TestCase
{
    public function test_validation_passes_for_valid_photo_signature_and_marksheets(): void
    {
        $request = new StoreApplicantDocumentsRequest;

        $validator = Validator::make([
            'photo' => UploadedFile::fake()->image('photo.jpg', 300, 300),
            'signature' => UploadedFile::fake()->image('signature.png', 300, 80),
            'marksheets' => [
                UploadedFile::fake()->create('marksheet.pdf', 300, 'application/pdf'),
            ],
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_for_invalid_photo_dimensions(): void
    {
        $request = new StoreApplicantDocumentsRequest;

        $validator = Validator::make([
            'photo' => UploadedFile::fake()->image('photo.jpg', 250, 300),
            'signature' => UploadedFile::fake()->image('signature.png', 300, 80),
            'marksheets' => [
                UploadedFile::fake()->create('marksheet.pdf', 300, 'application/pdf'),
            ],
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('photo', $validator->errors()->toArray());
    }

    public function test_validation_fails_for_non_pdf_marksheet_file(): void
    {
        $request = new StoreApplicantDocumentsRequest;

        $validator = Validator::make([
            'photo' => UploadedFile::fake()->image('photo.jpg', 300, 300),
            'signature' => UploadedFile::fake()->image('signature.png', 300, 80),
            'marksheets' => [
                UploadedFile::fake()->image('marksheet.jpg', 1000, 1000),
            ],
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('marksheets.0', $validator->errors()->toArray());
    }
}

