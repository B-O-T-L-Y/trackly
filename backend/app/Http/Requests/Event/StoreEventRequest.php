<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->header('Authorization') === 'Bearer ' . config('tracking.token');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'idempotency_key' => $this->header('X-Idempotency-Key'),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:page_view,cta_click,form_submit'],
            'ts' => ['required', 'date'],
            'session_id' => ['required', 'string'],
            'idempotency_key' => ['required', 'string', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Invalid event type.',
            'idempotency_key.required' => 'Missing idempotency key.',
            'idempotency_key.uuid' => 'Invalid idempotency key format.',
        ];
    }
}
