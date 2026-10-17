<?php

namespace App\Http\Controllers;

use App\Exceptions\SlotUnavailableException;
use App\Http\Requests\BookingAvailabilityRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * The booking page. Steps switch in the browser (Alpine); free days and
     * times are loaded from the JSON endpoints below. A service can be
     * preselected with ?service=slug, and after a failed submit the old
     * input reopens the page at the right step.
     */
    public function create(Request $request, AvailabilityService $availability): View
    {
        $services = Service::active()
            ->ordered()
            ->with(['stylists' => fn ($query) => $query->active()->orderBy('stylists.id')])
            ->get();

        $locale = app()->getLocale();
        $firstMonday = CarbonImmutable::parse('2024-01-01')->locale($locale);

        return view('booking.create', [
            'services' => $services,
            'state' => [
                'services' => $services->mapWithKeys(fn (Service $service): array => [$service->slug => [
                    'name' => $service->name,
                    'duration' => $service->duration_min,
                    'stylists' => $service->stylists->map(fn (Stylist $stylist): array => ['id' => $stylist->id, 'name' => $stylist->name])->values(),
                ]]),
                'initial' => [
                    'service' => old('service', $request->query('service')),
                    'stylist' => old('stylist', $request->query('stylist')),
                    'date' => old('date', $request->query('date')),
                    'time' => old('time'),
                ],
                'firstDate' => $availability->firstBookableDate()->toDateString(),
                'lastDate' => $availability->lastBookableDate()->toDateString(),
                'weekdays' => collect(range(0, 6))->map(fn (int $day): string => $firstMonday->addDays($day)->isoFormat('dd')),
                'weekdayNames' => collect(range(0, 6))->map(fn (int $day): string => $firstMonday->addDays($day)->isoFormat('dddd')),
                'months' => collect(range(0, 11))->map(fn (int $month): string => $firstMonday->addMonths($month)->isoFormat('MMMM')),
                'urls' => [
                    'dates' => route('booking.dates'),
                    'slots' => route('booking.slots'),
                ],
            ],
        ]);
    }

    /**
     * Days in the requested month that have at least one free time.
     */
    public function dates(BookingAvailabilityRequest $request, AvailabilityService $availability): JsonResponse
    {
        $month = $request->selectedMonth();

        return response()->json([
            'dates' => $availability
                ->availableDates($request->service(), $request->stylist(), $month->startOfMonth(), $month->endOfMonth())
                ->map(fn (CarbonImmutable $date): string => $date->toDateString()),
        ]);
    }

    /**
     * Free start times ("H:i") on the requested date.
     */
    public function slots(BookingAvailabilityRequest $request, AvailabilityService $availability): JsonResponse
    {
        return response()->json([
            'slots' => $availability
                ->availableSlots($request->service(), $request->selectedDate(), $request->stylist())
                ->keys(),
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
}
