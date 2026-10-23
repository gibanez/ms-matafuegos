import { reactive } from 'vue';
import { clearToken, getToken, setToken } from './api';

const ADMIN_KEY = 'cms_admin';
const NOTIFICATIONS_KEY = 'cms_notifications';
const PREFS_KEY = 'cms_prefs';

function readJson(key, fallback) {
    try {
        const raw = localStorage.getItem(key);

        return raw ? JSON.parse(raw) : fallback;
    } catch {
        return fallback;
    }
}

function writeJson(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

export const store = reactive({
    token: getToken(),
    admin: readJson(ADMIN_KEY, null),
    view: 'dashboard',
    sidebarOpen: false,
    createRequest: null,
    notifications: readJson(NOTIFICATIONS_KEY, []),
    prefs: {
        compact: false,
        saveToasts: true,
        ...readJson(PREFS_KEY, {}),
    },
});

export function setSession(token, admin) {
    setToken(token);
    store.token = token;
    store.admin = admin || null;
    writeJson(ADMIN_KEY, store.admin);
}

export function clearSession() {
    clearToken();
    store.token = '';
    store.admin = null;
    localStorage.removeItem(ADMIN_KEY);
}

export function saveNotifications() {
    writeJson(NOTIFICATIONS_KEY, store.notifications.slice(0, 40));
}

export function pushNotification({ title, detail = '', type = 'success' }) {
    store.notifications.unshift({
        id: typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : String(Date.now()),
        title,
        detail,
        type,
        at: new Date().toISOString(),
        read: false,
    });
    store.notifications = store.notifications.slice(0, 40);
    saveNotifications();
}

export function markNotificationsRead() {
    store.notifications = store.notifications.map((notification) => ({
        ...notification,
        read: true,
    }));
    saveNotifications();
}

export function clearNotifications() {
    store.notifications = [];
    saveNotifications();
}

export function applyPrefs() {
    document.body.classList.toggle('compact', Boolean(store.prefs.compact));
}

export function updatePrefs(patch) {
    store.prefs = {
        ...store.prefs,
        ...patch,
    };
    writeJson(PREFS_KEY, store.prefs);
    applyPrefs();
}

export function resetPrefs() {
    store.prefs = {
        compact: false,
        saveToasts: true,
    };
    writeJson(PREFS_KEY, store.prefs);
    applyPrefs();
}
