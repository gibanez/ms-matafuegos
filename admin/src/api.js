const TOKEN_KEY = 'cms_token';

export function getToken() {
    return localStorage.getItem(TOKEN_KEY) || '';
}

export function setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken() {
    localStorage.removeItem(TOKEN_KEY);
}

export async function api(path, options = {}) {
    const headers = new Headers(options.headers || {});
    const token = getToken();

    if (token) {
        headers.set('Authorization', `Bearer ${token}`);
    }

    let body = options.body;

    if (body !== undefined && body !== null && !(body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
        body = JSON.stringify(body);
    }

    const response = await fetch(path, {
        ...options,
        headers,
        body,
    });

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
        if (response.status === 401) {
            clearToken();
            window.dispatchEvent(new CustomEvent('cms:unauthorized'));
        }

        const error = new Error(data && data.error ? data.error : `Error ${response.status}`);
        error.status = response.status;
        error.data = data;

        throw error;
    }

    return data;
}
