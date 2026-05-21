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
            <div className="empty-shopcart">
                <h2>Seu carrinho está vazio</h2>
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

            const { data } = await axios.post('/checkout', payload)
            clearShopcartContext();
            navigate(`/order-confirmed/${data.order.id}`, {replace: true});
        }

        catch (err){
            setError(err.response?.data?.message || 'Checkout failed. Please try again.');
        }

        finally {
            setLoading(false);
        }
    }

    return (
        <div className="checkout-container">
            <h2>Checkout</h2>
            <p>Subtotal: ${subtotal.toFixed(2)}</p>
            {error && <p className="error">{error}</p>}
            <form onSubmit={handleSubmit} disabled={loading}>
                <input
                    type="email"
                    placeholder="Enter your email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                />
                <button type="submit" disabled={loading}>
                    {loading ? 'Processing...' : 'Confirmar Pedido'}
                </button>
            </form>
        </div>
    )
}