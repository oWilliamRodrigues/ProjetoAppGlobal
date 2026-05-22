import { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { useShopcart } from '../Shopcart/ShopcartContext';

export default function Checkout() {
    const [email, setEmail] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const {items, subtotal, clearShopcart: clearShopcartContext} = useShopcart();

    if (items.length === 0) {
        return (
            <div className="max-w-4xl mx-auto p-4">
                <h2 className="text-3xl font-bold mb-6">Seu carrinho está vazio</h2>
                <button onClick={() => navigate('/products')}>Ir para Produtos</button>
            </div>
        );
    }

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try{
            const payload = {
                user_email: email,
                items: items.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                })),
            };

            const { data } = await axios.post('/checkout', payload);
            clearShopcartContext();
            window.location.href = data.init_point;
        }

        catch (err){
            setError(err.response?.data?.message || 'Checkout failed. Please try again.');
        }

        finally {
            setLoading(false);
        }
    }

    return (
        <div className="bg-gray-100 p-4 rounded shadow max-w-2xl mx-auto">
            <h2 className="text-3xl font-bold mb-6">Checkout</h2>
            <h3 className="text-2xl font-semibold mb-4">Subtotal: ${subtotal.toFixed(2)}</h3>
            {error && <p className="error">{error}</p>}
            <form onSubmit={handleSubmit} disabled={loading}>
                <input
                    type="email"
                    placeholder="Enter your email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                />
                <button type="submit" className="bg-green-500 text-white px-4 py-2 rounded" disabled={loading}>
                    {loading ? 'Processing...' : 'Confirmar Pedido'}
                </button>
            </form>
        </div>
    )
}