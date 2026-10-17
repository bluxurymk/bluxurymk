/**
 * Booking page: steps switch in place, free days and times come from the
 * JSON endpoints, and only the final form submit reloads the page.
 */

const pad = (number) => String(number).padStart(2, '0');

/** "2026-10-20" → { year, month (1-12), day }, without time zone surprises. */
const parseDate = (iso) => {
    const [year, month, day] = iso.split('-').map(Number);

    return { year, month, day };
};

const monthKey = ({ year, month }) => `${year}-${pad(month)}`;

export default (state) => ({
    services: state.services,
    service: null,
    stylist: null,
    stylistChosen: false,
    step: 'service',

    month: parseDate(state.firstDate),
    availableDates: [],
    loadingDates: false,
    date: null,
    slots: [],
    loadingSlots: false,
    time: null,
    loadFailed: false,
    requestId: 0,

    async init() {
        const initial = state.initial;

        if (!initial.service || !this.services[initial.service]) {
            return;
        }

        this.service = initial.service;
        const stylistId = Number.parseInt(initial.stylist, 10);

        if (this.stylists.some((stylist) => stylist.id === stylistId)) {
            this.stylist = stylistId;
            this.stylistChosen = true;
        } else if (initial.stylist === 'any' || initial.date) {
            this.stylistChosen = true;
        }

        if (!this.canOpen('datetime')) {
            this.step = this.needsStylistStep ? 'stylist' : 'service';

            return;
        }

        if (initial.date && /^\d{4}-\d{2}-\d{2}$/.test(initial.date)) {
            this.month = { ...parseDate(initial.date), day: 1 };
            this.date = initial.date;
        }

        this.step = 'datetime';
        await Promise.all([this.loadDates(), this.date ? this.loadSlots() : null]);

        if (initial.time && this.slots.includes(initial.time)) {
            this.time = initial.time;
            this.step = 'details';
        }
    },

    get chosenService() {
        return this.service ? this.services[this.service] : null;
    },

    get stylists() {
        return this.chosenService?.stylists ?? [];
    },

    get needsStylistStep() {
        return this.stylists.length > 1;
    },

    get steps() {
        return ['service', ...(this.needsStylistStep ? ['stylist'] : []), 'datetime', 'details'];
    },

    get stylistName() {
        return this.stylists.find((stylist) => stylist.id === this.stylist)?.name ?? null;
    },

    stepNumber(step) {
        return this.steps.indexOf(step) + 1;
    },

    isDone(step) {
        return {
            service: this.service !== null,
            stylist: this.stylistChosen,
            datetime: this.time !== null,
            details: false,
        }[step];
    },

    canOpen(step) {
        return {
            service: true,
            stylist: this.service !== null,
            datetime: this.service !== null && this.stylists.length > 0 && (this.stylistChosen || !this.needsStylistStep),
            details: this.time !== null,
        }[step];
    },

    open(step) {
        if (!this.canOpen(step)) {
            return;
        }

        this.step = step;

        if (step === 'datetime' && this.availableDates.length === 0) {
            this.loadDates();
        }

        this.$nextTick(() => {
            const panel = this.$refs.panel;

            if (panel && panel.getBoundingClientRect().top < 0) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    },

    back() {
        const previous = this.steps[this.steps.indexOf(this.step) - 1];

        if (previous) {
            this.open(previous);
        }
    },

    chooseService(slug) {
        if (slug !== this.service) {
            this.service = slug;
            this.stylist = null;
            this.stylistChosen = false;
            this.resetDateAndTime();
        }

        if (this.stylists.length > 0) {
            this.open(this.needsStylistStep ? 'stylist' : 'datetime');
        }
    },

    chooseStylist(id) {
        if (!this.stylistChosen || id !== this.stylist) {
            this.stylist = id;
            this.stylistChosen = true;
            this.resetDateAndTime();
        }

        this.open('datetime');
    },

    resetDateAndTime() {
        this.month = parseDate(state.firstDate);
        this.availableDates = [];
        this.date = null;
        this.slots = [];
        this.time = null;
    },

    chooseDate(iso) {
        if (iso === this.date) {
            return;
        }

        this.date = iso;
        this.time = null;
        this.loadSlots();
    },

    chooseTime(time) {
        this.time = time;
        this.open('details');
    },

    /* Calendar */

    get monthLabel() {
        return `${state.months[this.month.month - 1]} ${this.month.year}`;
    },

    get canGoToPreviousMonth() {
        return monthKey(this.month) > monthKey(parseDate(state.firstDate));
    },

    get canGoToNextMonth() {
        return monthKey(this.month) < monthKey(parseDate(state.lastDate));
    },

    shiftMonth(by) {
        const shifted = new Date(Date.UTC(this.month.year, this.month.month - 1 + by, 1));
        this.month = { year: shifted.getUTCFullYear(), month: shifted.getUTCMonth() + 1, day: 1 };
        this.loadDates();
    },

    /** Calendar cells for the month, Monday first; null cells pad the first week. */
    get calendarDays() {
        const { year, month } = this.month;
        const firstWeekday = (new Date(Date.UTC(year, month - 1, 1)).getUTCDay() + 6) % 7;
        const daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();
        const cells = Array.from({ length: firstWeekday }, () => null);

        for (let day = 1; day <= daysInMonth; day++) {
            const iso = `${year}-${pad(month)}-${pad(day)}`;
            cells.push({ iso, day, available: this.availableDates.includes(iso) });
        }

        return cells;
    },

    weekday(index) {
        return state.weekdays[index];
    },

    /** "вторник, 20 октомври" */
    formatDate(iso) {
        if (!iso) {
            return '';
        }

        const { year, month, day } = parseDate(iso);
        const weekday = (new Date(Date.UTC(year, month - 1, day)).getUTCDay() + 6) % 7;

        return `${state.weekdayNames[weekday]}, ${day} ${state.months[month - 1]}`;
    },

    /* Requests */

    query(extra) {
        return new URLSearchParams({
            service: this.service,
            ...(this.stylist ? { stylist: this.stylist } : {}),
            ...extra,
        });
    },

    async fetchJson(url, params) {
        const response = await fetch(`${url}?${params}`, { headers: { Accept: 'application/json' } });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    },

    async loadDates() {
        const requestId = ++this.requestId;
        this.loadingDates = true;
        this.loadFailed = false;

        try {
            const { dates } = await this.fetchJson(state.urls.dates, this.query({ month: monthKey(this.month) }));

            if (requestId === this.requestId) {
                this.availableDates = dates;
            }
        } catch {
            this.loadFailed = true;
        } finally {
            if (requestId === this.requestId) {
                this.loadingDates = false;
            }
        }
    },

    async loadSlots() {
        const date = this.date;
        this.loadingSlots = true;
        this.loadFailed = false;
        this.slots = [];

        try {
            const { slots } = await this.fetchJson(state.urls.slots, this.query({ date }));

            if (date === this.date) {
                this.slots = slots;
            }
        } catch {
            this.loadFailed = true;
        } finally {
            if (date === this.date) {
                this.loadingSlots = false;
            }
        }
    },
});
