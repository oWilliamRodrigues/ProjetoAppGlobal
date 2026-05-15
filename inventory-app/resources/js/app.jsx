import '../css/app.css';
import './bootstrap';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import Login from './Login';
import Products from './Pages/Products/Products';
import RequireAuth from './RequireAuth';

ReactDOM.createRoot(document.getElementById('app')).render(
    <BrowserRouter>
        <Routes>
            <Route 
                path="/" 
                element={
                    <Navigate to="/login" replace />
                } 
            />

            <Route 
                path="/login" 
                element={
                    <Login />
                } 
            />

            <Route
                path="/products"
                element={
                    <RequireAuth>
                        <Products />
                    </RequireAuth>
                }
            />
            <Route 
                path="*" 
                element={<Navigate to="/" replace />} 
            />
        </Routes>
    </BrowserRouter>
);
