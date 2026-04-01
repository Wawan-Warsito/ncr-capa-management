<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NCRRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth handled by middleware/controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'process_name' => 'required|string|max:255',
            'finder_dept_id' => 'required|exists:departments,id',
            'receiver_dept_id' => 'required|exists:departments,id',
            'defect_category_id' => 'required|exists:defect_categories,id',
            'severity_level_id' => 'required|exists:severity_levels,id',
            'defect_description' => 'required|string',
            'reference_document' => 'nullable|string|max:255',
            'target_completion_date' => 'nullable|date|after:today',
            // Attachments can be handled separately or here if sent as array
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Adjust rules for update if necessary
            // e.g. some fields might be immutable or optional on update
        }

        return $rules;
    }
}
