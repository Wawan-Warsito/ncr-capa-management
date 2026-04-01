<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CAPARequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ncr_id' => 'required|exists:ncrs,id|unique:capas,ncr_id,' . $this->id, // One CAPA per NCR
            'root_cause_analysis' => 'required|string',
            'corrective_action_plan' => 'required|string',
            'preventive_action_plan' => 'required|string',
            'assigned_pic_id' => 'required|exists:users,id',
            'target_completion_date' => 'required|date|after:today',
            'resources_required' => 'nullable|string',
        ];
    }
}
