import '../css/app.css';

import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';

import Login from './Login';
import Products from './Products';

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
