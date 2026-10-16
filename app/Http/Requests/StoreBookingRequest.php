<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Models\Stylist;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Booking is public.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service' => ['required', 'string', Rule::exists('services', 'slug')->where('is_active', true)],
            'stylist' => ['nullable', 'integer', Rule::exists('stylists', 'id')->where('is_active', true)],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s\/-]{6,}$/'],
            'customer_email' => ['required', 'email', 'max:150'],
            'note' => ['nullable', 'string', 'max:500'],
            'consent' => ['accepted'],
            // Honeypot: hidden from people, filled in by spam bots.
            'website' => ['prohibited'],
        ];
    }

    public function service(): Service
    {
        return Service::where('slug', $this->validated('service'))->firstOrFail();
    }

    public function stylist(): ?Stylist
    {
        return $this->validated('stylist') === null ? null : Stylist::find($this->validated('stylist'));
    }

    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d H:i', $this->validated('date').' '.$this->validated('time'))->startOfMinute();
    }

    /**
     * @return array{customer_name: string, customer_phone: string, customer_email: string, note: string|null}
     */
    public function customer(): array
    {
        return [
            'customer_name' => $this->validated('customer_name'),
            'customer_phone' => $this->validated('customer_phone'),
            'customer_email' => $this->validated('customer_email'),
            'note' => $this->validated('note'),
        ];
    }
}
