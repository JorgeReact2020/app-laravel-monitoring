<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UptimeKumaWebhookRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'heartbeat' => 'required|array',
            'heartbeat.status' => 'required|integer',
            'heartbeat.msg' => 'nullable|string',
            'heartbeat.time' => 'required|string',
            'monitor' => 'required|array',
            'monitor.name' => 'required|string',
            'monitor.url' => 'required|string',
        ];
    }
}