import { formatDate } from './utils';

export const WEEKDAYS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

const monthFormatter = new Intl.DateTimeFormat('es-AR', {
    month: 'long',
    year: 'numeric',
});

const longDateFormatter = new Intl.DateTimeFormat('es-AR', {
    weekday: 'long',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
});

export function parseYmd(value) {
    if (!value) {
        return null;
    }

    const [year, month, day] = String(value).split('-').map(Number);

    if (!year || !month || !day) {
        return null;
    }

    return new Date(year, month - 1, day);
}

export function formatYmd(date) {
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${month}-${day}`;
}

export function monthLabel(date) {
    const label = monthFormatter.format(date);

    return label.charAt(0).toUpperCase() + label.slice(1);
}

export function formatLongDate(ymd) {
    const date = parseYmd(ymd);

    if (!date) {
        return '—';
    }

    return longDateFormatter.format(date);
}

export function buildMonthGrid(year, month) {
    const first = new Date(year, month, 1);
    const offset = (first.getDay() + 6) % 7;
    const start = new Date(year, month, 1 - offset);

    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(start);
        date.setDate(start.getDate() + index);

        return {
            date: formatYmd(date),
            day: date.getDate(),
            inMonth: date.getMonth() === month,
        };
    });
}

export function buildWeek(fromYmd) {
    const start = parseYmd(fromYmd);

    if (!start) {
        return [];
    }

    return Array.from({ length: 7 }, (_, index) => {
        const date = new Date(start);
        date.setDate(start.getDate() + index);

        return {
            date: formatYmd(date),
            day: date.getDate(),
            weekday: WEEKDAYS[index],
        };
    });
}

export function maintenanceMessage(item) {
    const name = item.customerName || 'cliente';
    const product = item.productName || 'el servicio';
    const purchased = formatDate(item.purchaseDate);

    return `Hola ${name}, le escribimos de MS Matafuegos. Registramos que el ${purchased} compró o realizó un service de ${product}. Ya es tiempo de un nuevo mantenimiento: ¿querés que coordinemos una visita?`;
}

export function whatsappHref(phone, text = '') {
    let digits = String(phone || '').replace(/\D+/g, '');

    if (!digits) {
        return '';
    }

    digits = digits.replace(/^0+/, '');

    if (!digits.startsWith('549')) {
        if (digits.startsWith('54')) {
            digits = `549${digits.slice(2).replace(/^0+/, '')}`;
        } else {
            digits = `549${digits}`;
        }
    }

    const query = text ? `?text=${encodeURIComponent(text)}` : '';

    return `https://wa.me/${digits}${query}`;
}

export function maintenanceStatusLabel(status) {
    const labels = {
        pending: 'Pendiente',
        overdue: 'Atrasado',
        notified: 'Avisado',
    };

    return labels[status] || status || '—';
}

export function maintenanceStatusSeverity(status) {
    const severities = {
        pending: 'warn',
        overdue: 'danger',
        notified: 'success',
    };

    return severities[status] || 'secondary';
}
