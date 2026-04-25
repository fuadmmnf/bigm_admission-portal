<?php

namespace App\Http\Requests\Applicant;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicantDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $photoWidth = (int) config('applicant_uploads.photo.width');
        $photoHeight = (int) config('applicant_uploads.photo.height');
        $photoMaxKb = (int) config('applicant_uploads.photo.max_kb');

        $signatureWidth = (int) config('applicant_uploads.signature.width');
        $signatureHeight = (int) config('applicant_uploads.signature.height');
        $signatureMaxKb = (int) config('applicant_uploads.signature.max_kb');

        $marksheetMaxKb = (int) config('applicant_uploads.marksheet_pdf.max_kb');
        $marksheetMaxCount = (int) config('applicant_uploads.marksheet_pdf.max_count');

        return [
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:'.$photoMaxKb,
                'dimensions:width='.$photoWidth.',height='.$photoHeight,
            ],
            'signature' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:'.$signatureMaxKb,
                'dimensions:width='.$signatureWidth.',height='.$signatureHeight,
            ],
            'marksheets' => [
                'required',
                'array',
                'min:1',
                'max:'.$marksheetMaxCount,
            ],
            'marksheets.*' => [
                'required',
                'file',
                'mimes:pdf',
                'max:'.$marksheetMaxKb,
            ],
        ];
    }
}

