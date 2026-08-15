<?php

namespace Lareon\Modules\Ticketing\App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return userCan('admin.ticket.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => 'required|string|in:approve,reject,review',
        ];
    }
}
