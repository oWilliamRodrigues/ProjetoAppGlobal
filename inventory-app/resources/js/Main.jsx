import { useState } from 'react';
import Login from './Login';

export default function App() {
    const [user, setUser] = useState(() => {
        const storedUser = localStorage.getItem('user');
        return storedUser ? JSON.parse(storedUser) : null;
    });

    const handleLogin = (token, userData) => {
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(userData));
        setUser(userData);
    };

    const handleLogout = () => {
        localStorage.clear();
        setUser(null);
    };

    if (!user) {
        return <Login onLogin={handleLogin} />;
    }

    return <ProductList user={user} onLogout={handleLogout} />;
}