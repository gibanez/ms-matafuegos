const JSON_HEADERS = {
    Accept: 'application/json',
};

async function request(path, options = {}) {
    const headers = new Headers(options.headers || JSON_HEADERS);

    if (options.body && !(options.body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
    }

    let response;

    try {
        response = await fetch(path, {
            ...options,
            headers,
            body:
                options.body && !(options.body instanceof FormData)
                    ? JSON.stringify(options.body)
                    : options.body,
        });
    } catch {
        throw new Error('No pudimos conectarnos con el servidor. Intentá de nuevo en unos minutos.');
    }

    if (response.status === 204) {
        return null;
    }

    const text = await response.text();
    let data = null;

    if (text) {
        try {
            data = JSON.parse(text);
        } catch {
            data = { error: text };
        }
    }

    if (!response.ok) {
        const message =
            data && data.error ? data.error : 'No pudimos completar la operación. Intentá de nuevo.';
        const error = new Error(message);
        error.status = response.status;
        error.data = data;
        throw error;
    }

    return data;
}

export function getProducts(params = {}) {
    const query = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            query.set(key, String(value));
        }
    });

    const suffix = query.toString() ? `?${query.toString()}` : '';

    return request(`/api/public/products${suffix}`);
}

export function getProduct(slug) {
    return request(`/api/public/products/${encodeURIComponent(slug)}`);
}

export function getCategories() {
    return request('/api/public/categories');
}

export function createInquiry(payload) {
    return request('/api/inquiries', {
        method: 'POST',
        body: payload,
    });
}
