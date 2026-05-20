import { useEffect, useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';

export default function AdminValidations() {
    const [orders, setOrders] = useState([]);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(false);
    const [expanded, setExpanded] = useState({});
    const navigate = useNavigate();

    const handleLogout = () => {
        localStorage.clear();
        navigate('/login', { replace: true });
    }

    async function fetchOrders(currentpage = 1) {
        setLoading(true);

        try {
            const response = await axios.get(`/orders?page=${currentpage}`);
            setOrders(response.data.data);
            setPage(currentpage);
            setLastPage(response.data.last_page);
        } catch (error) {
            console.error('Erro ao carregar pedidos:', error);
        }

        setLoading(false);
    }

    useEffect(() => {
        fetchOrders(1);
    }, []);

    async function approveOrder(orderId) {
        try {
            await axios.post(`/orders/${orderId}/approve`);
            fetchOrders(page);
        } catch (error) {
            if (error.response?.status === 422) {
                alert(error.response.data?.message || 'Estoque insuficiente para aprovar este pedido.');
            } else {
                console.error('Erro ao aprovar pedido:', error);
            }
        }
    }

    async function discardOrder(orderId) {
        try {
            await axios.post(`/orders/${orderId}/discard`);
            fetchOrders(page);
        } catch (error) {
            console.error('Erro ao descartar pedido:', error);
        }
    }

    function toggleExpanded(orderId) {
        setExpanded((prev) => ({
            ...prev,
            [orderId]: !prev[orderId],
        }));
    }

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="bg-navy text-white px-6 py-4 shadow-md">
                <div className="max-w-7xl mx-auto flex justify-between items-center">
                    <div>
                        <h1 className="font-sans text-xl font-semibold">Gerente de Estoque</h1>
                        <p className="text-xs text-cyan/80">Por Adam e William</p>
                    </div>
                    <div className="flex gap-4">
                        <Link to="/admin" className="text-white/80 hover:text-white border border-white/30 hover:border-white px-3 py-1.5 rounded-lg transition text-sm">
                            Estoque
                        </Link>
                        <Link to="/validation" className="text-white/80 hover:text-white border border-white/30 hover:border-white px-3 py-1.5 rounded-lg transition text-sm">
                            Validação
                        </Link>
                    </div>
                    <button
                        onClick={handleLogout}
                        className="text-sm text-white/80 hover:text-white border border-white/30 hover:border-white px-3 py-1.5 rounded-lg transition"
                    >
                        Sair
                    </button>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-6 py-8">
                <h2 className="font-sans text-2xl font-semibold text-navy mb-6">Pedidos pendentes</h2>

                {loading ? (
                    <p className="text-gray-500">Carregando...</p>
                ) : orders.length === 0 ? (
                    <p className="text-gray-500">Nenhum pedido pendente.</p>
                ) : (
                    <div className="flex flex-col gap-4">
                        {orders.map((o) => {
                            const isOpen = expanded[o.id];
                            const itemsToShow = isOpen ? o.items : o.items.slice(0, 2);

                            return (
                                <div key={o.id} className="bg-white rounded-xl shadow-sm flex divide-x divide-gray-200">
                                    <div className="flex flex-col justify-center items-center px-6 py-4 w-40">
                                        <h3 className="font-display text-2xl text-navy">#{o.id}</h3>
                                        <p className="text-xs text-gray-500 mt-1 truncate max-w-full">{o.buyer_email}</p>
                                    </div>

                                    <div className="flex-1 px-6 py-4">
                                        {itemsToShow.map((item) => (
                                            <div key={item.id} className="flex items-center gap-4">
                                                <p>{item.product.title}</p>
                                                <p className="text-sm text-gray-500">x{item.quantity}</p>
                                            </div>
                                        ))}

                                        {o.items.length > 2 && (
                                            <button
                                                onClick={() => toggleExpanded(o.id)}
                                                className="text-sm text-cta hover:underline mt-2"
                                            >
                                                {isOpen ? 'Ver menos' : `Ver mais (${o.items.length - 2})`}
                                            </button>
                                        )}

                                        <p className="font-display text-lg text-navy mt-3">Total: R$ {Number(o.amount).toFixed(2)}</p>
                                    </div>

                                    <div className="flex flex-col justify-center gap-2 px-6 py-4 w-48">
                                        <button
                                            onClick={() => approveOrder(o.id)}
                                            className="text-sm bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition"
                                        >
                                            Aprovar
                                        </button>
                                        <button
                                            onClick={() => discardOrder(o.id)}
                                            className="text-sm bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition"
                                        >
                                            Descartar
                                        </button>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}

                <div className="mt-8 flex justify-center items-center gap-3">
                    <button
                        disabled={page <= 1}
                        onClick={() => fetchOrders(page - 1)}
                        className="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
                    >
                        Anterior
                    </button>
                    <span className="text-sm text-gray-600">Página <strong className="font-display text-navy">{page}</strong></span>
                    <button
                        disabled={page >= lastPage}
                        onClick={() => fetchOrders(page + 1)}
                        className="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
                    >
                        Próxima
                    </button>
                </div>
            </main>
        </div>
    );
}
