<?php

namespace App\Http\Controllers;

use App\Exceptions\SlotUnavailableException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Number of days shown at once in the date step.
     */
    private const DAYS_PER_PAGE = 14;

    /**
     * The booking wizard. Each step is a plain link that adds a query
     * parameter (service → stylist → date → time), so it works without JS.
     */
    public function create(Request $request, AvailabilityService $availability): View
    {
        $services = Service::active()->ordered()->get();
        $service = $services->firstWhere('slug', $request->query('service'));

        $stylists = $service ? $availability->stylistsFor($service) : collect();
        $stylist = $stylists->firstWhere('id', $request->integer('stylist'));
        $hasChosenStylist = $service !== null
            && ($stylists->count() === 1 || $stylist !== null || $request->query('stylist') === 'any');

        if ($stylists->count() === 1) {
            $stylist = null;
        }

        $dateWindowStart = $this->parseDate($request->query('from')) ?? $availability->firstBookableDate();
        $dateWindowStart = $dateWindowStart->max($availability->firstBookableDate());
        $dateWindowEnd = $dateWindowStart->addDays(self::DAYS_PER_PAGE - 1);

        $dates = $hasChosenStylist
            ? $availability->availableDates($service, $stylist, $dateWindowStart, $dateWindowEnd)
            : collect();

        $date = $hasChosenStylist ? $this->parseDate($request->query('date')) : null;
        $slots = $date ? $availability->availableSlots($service, $date, $stylist) : collect();
        $time = $slots->has($request->query('time')) ? $request->query('time') : null;

        return view('booking.create', [
            'services' => $services,
            'service' => $service,
            'stylists' => $stylists,
            'stylist' => $stylist,
            'hasChosenStylist' => $hasChosenStylist,
            'stylistParam' => $stylist?->id ?? ($hasChosenStylist ? 'any' : null),
            'dates' => $dates,
            'previousWindow' => $dateWindowStart->greaterThan($availability->firstBookableDate())
                ? $dateWindowStart->subDays(self::DAYS_PER_PAGE)->max($availability->firstBookableDate())
                : null,
            'nextWindow' => $dateWindowEnd->lessThan($availability->lastBookableDate())
                ? $dateWindowEnd->addDay()
                : null,
            'date' => $date,
            'slots' => $slots,
            'time' => $time,
        ]);
    }

    public function store(StoreBookingRequest $request, BookingService $bookings): RedirectResponse
    {
        try {
            $booking = $bookings->create($request->service(), $request->stylist(), $request->startsAt(), $request->customer());
        } catch (SlotUnavailableException) {
            return redirect()
                ->route('booking.create', [
                    'service' => $request->validated('service'),
                    'stylist' => $request->validated('stylist') ?? 'any',
                    'date' => $request->validated('date'),
                ])
                ->withErrors(['time' => __('booking.slot_taken')]);
        }

        return redirect()->route('booking.success')->with('booking_token', $booking->manage_token);
    }

    public function success(Request $request): View|RedirectResponse
    {
        $booking = Booking::with(['service', 'stylist'])
            ->firstWhere('manage_token', $request->session()->get('booking_token'));

        if ($booking === null) {
            return redirect()->route('booking.create');
        }

        return view('booking.success', ['booking' => $booking]);
    }

    public function show(Booking $booking): View
    {
        return view('booking.manage', ['booking' => $booking->load(['service', 'stylist'])]);
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        if (! $booking->isCancellableByCustomer()) {
            return back()->withErrors(['booking' => __('booking.manage.not_cancellable', ['phone' => config('salon.phone')])]);
        }

        $booking->cancel(Booking::CANCELLED_BY_CUSTOMER);

        return redirect()->route('booking.show', $booking)->with('status', __('booking.manage.cancelled'));
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return CarbonImmutable::createFromFormat('!Y-m-d', $value) ?: null;
    }
}
