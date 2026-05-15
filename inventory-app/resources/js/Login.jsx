import { useState } from 'react';
import { apiFetch } from './api';

export default function Login({ onLogin }) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);
        try {
            const data = await apiFetch('/login', {
                method: 'POST',
                // headers: {
                //     'Content-Type': 'application/json'
                // },
                body: JSON.stringify({ email, password })
            });
            onLogin(data.token, data.user);
        } catch (err) {
            setError('Invalid email or password');
        } finally {
            setLoading(false);
        }
    }

    return (
        <form onSubmit={handleSubmit}>
            <input
                type="email"
                placeholder="Email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
            />
            <input
                type="password"
                placeholder="Password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
            />
            {error && <p>{error}</p>}
            <button type="submit" disabled={loading}>
                {loading ? 'Acessando...' : 'Login'}
            </button>
        </form>
    );
}