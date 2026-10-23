const moneyFormatter = new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const dateFormatter = new Intl.DateTimeFormat('es-AR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
});

const dateTimeFormatter = new Intl.DateTimeFormat('es-AR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
});

export function money(value) {
    return moneyFormatter.format(Number(value) || 0);
}

export function formatDate(value) {
    if (!value) {
        return '—';
    }

    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        const [year, month, day] = value.split('-');
        return `${day}/${month}/${year}`;
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? value : dateFormatter.format(date);
}

export function formatDateTime(value) {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? value : dateTimeFormatter.format(date);
}

export function timeAgo(value) {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    const seconds = Math.max(0, Math.round((Date.now() - date.getTime()) / 1000));

    if (seconds < 60) {
        return 'Ahora';
    }

    const minutes = Math.round(seconds / 60);

    if (minutes < 60) {
        return `Hace ${minutes} min`;
    }

    const hours = Math.round(minutes / 60);

    if (hours < 24) {
        return `Hace ${hours} h`;
    }

    const days = Math.round(hours / 24);

    if (days < 7) {
        return `Hace ${days} d`;
    }

    return formatDate(value);
}

export function todayYmd() {
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    return `${now.getFullYear()}-${month}-${day}`;
}

export function ymdToDate(value) {
    if (!value) {
        return null;
    }

    const date = new Date(`${value}T00:00:00`);

    return Number.isNaN(date.getTime()) ? null : date;
}

export function dateToYmd(value) {
    if (!value) {
        return null;
    }

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${month}-${day}`;
}

export function nested(object, path) {
    return path.split('.').reduce((value, key) => {
        if (value === null || value === undefined) {
            return null;
        }

        return value[key];
    }, object);
}

export function excerpt(value, length = 90) {
    const text = String(value || '').replace(/\s+/g, ' ').trim();

    if (text.length <= length) {
        return text || '—';
    }

    return `${text.slice(0, length)}…`;
}

export function initials(name, email) {
    const source = String(name || email || 'A').trim();

    if (!source) {
        return 'A';
    }

    const parts = source.split(/\s+/).filter(Boolean);

    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
}

export function statusLabel(status) {
    const labels = {
        active: 'Activo',
        inactive: 'Inactivo',
        draft: 'Borrador',
        sent: 'Enviado',
        accepted: 'Aceptado',
        rejected: 'Rechazado',
        cancelled: 'Cancelado',
        issued: 'Emitida',
        paid: 'Pagada',
    };

    return labels[status] || status || '—';
}

export function statusSeverity(status) {
    const severities = {
        active: 'success',
        inactive: 'secondary',
        draft: 'info',
        sent: 'warn',
        accepted: 'success',
        rejected: 'danger',
        cancelled: 'secondary',
        issued: 'info',
        paid: 'success',
    };

    return severities[status] || 'secondary';
}

export function roundMoney(value) {
    return Math.round((Number(value) + Number.EPSILON) * 100) / 100;
}
