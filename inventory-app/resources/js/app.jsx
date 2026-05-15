import '../css/app.css';

import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';

import Login from './Pages/Login';
import Products from './Pages/Products/Products';
import RequireAuth from './Auth/RequireAuth.jsx';

ReactDOM.createRoot(document.getElementById('app')).render(
    <BrowserRouter>
        <Routes>
            <Route path="/login" element={<Login />} />

            <Route
                path="/products"
                element={
                    <RequireAuth>
                        <Products />
                    </RequireAuth>
                }
            />
        </Routes>
    </BrowserRouter>
);