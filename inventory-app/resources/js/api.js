const API_BASE = './api';

export async function apiFetch(path, options = {}) {
    const token = localStorage.getItem('token');

    const headers = {
        'Accept': 'application/json',
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        ...options.headers,
    };

    const response = await fetch(`${API_BASE}${path}`, { ...options, headers });

    if (response.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.reload();
    }

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message || `Erro: ${response.status}`);
    }

    return data;
}