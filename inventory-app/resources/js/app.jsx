import '../css/app.css';
import './bootstrap';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import Login from './Login';
import UserProducts from './Pages/Products/UserProducts';
import AdminProducts from './Pages/Products/AdminProducts';
import AdminValidations from './Pages/Products/AdminValidations';
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
                    <UserProducts />                    
                }
            />
            <Route
               path="/admin"
               element={
                   <RequireAuth>
                       <AdminProducts />
                   </RequireAuth>
               }
            />
            <Route
               path="/validation"
               element={
                   <RequireAuth>
                       <AdminValidations />
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
