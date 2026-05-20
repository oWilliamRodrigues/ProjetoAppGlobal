import '../css/app.css';
import './bootstrap';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import Login from './Login';
import UserProducts from './Pages/Products/UserProducts';
import AdminProducts from './Pages/Products/AdminProducts';
import RequireAuth from './RequireAuth';
import { ShopcartProvider } from './Pages/Shopcart/ShopcartContext';
import { Shopcart } from './Pages/Shopcart/Shopcart';
import Checkout from './Pages/Checkout/Checkout';

ReactDOM.createRoot(document.getElementById('app')).render(
    <ShopcartProvider>
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
                    path="/shopcart"
                    element={
                        <RequireAuth>
                            <Shopcart />
                        </RequireAuth>
                    }
                />
                <Route
                    path="/checkout"
                    element={
                        <Checkout />
                    }
                />
                <Route 
                    path="*" 
                    element={<Navigate to="/" replace />} 
                />
            </Routes>
        </BrowserRouter>
    </ShopcartProvider>
);
