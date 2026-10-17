<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Models\Stylist;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query for the booking page's free days (by month) or free times (by date).
 */
class BookingAvailabilityRequest extends FormRequest
{
    /**
     * Availability is public.
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
            'month' => ['required_without:date', 'date_format:Y-m'],
            'date' => ['required_without:month', 'date_format:Y-m-d'],
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

    public function selectedMonth(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m', $this->validated('month'));
    }

    public function selectedDate(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $this->validated('date'));
    }
}
