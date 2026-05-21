import { useEffect, useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { useShopcart } from '../Shopcart/ShopcartContext';

export default function UserProducts() {
    const [products, setProducts] = useState([]);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(false);
    const [quantities, setQuantities] = useState({});
    const { addToShopcart, totalQuantity } = useShopcart();

    const navigate = useNavigate();
    
    const handleLogout = () => {
        localStorage.clear();
        navigate('/login', { replace: true });
    }

    async function fetchProducts(currentpage = 1) {
        setLoading(true);

        try {
            const response = await axios.get(`/products?page=${currentpage}`);
            setProducts(response.data.data);
            setPage(currentpage);
            setLastPage(response.data.last_page);
        } catch (error) {
            console.error('Erro ao carregar produtos:', error);
        } 
        
        setLoading(false);
    }

    useEffect(() => {
        fetchProducts(1);
    }, []);

    function handleQuantityChange(productId, value) {
        setQuantities(prev => ({
            ...prev,
            [productId]: Number(value)
        }));
    }

    function addToCart(product) {
        const quantity = quantities[product.id] || 1;
        addToShopcart({
            product_id: product.id,
            name: product.title,
            price: Number(product.price),
            quantity,
        });
    }

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Header */}
            <header className="bg-navy text-white px-6 py-4 shadow-md">
                <div className="max-w-7xl mx-auto flex justify-between items-center">
                    <div>
                        <h1 className="font-sans text-xl font-semibold">Gerente de Estoque</h1>
                        <p className="text-xs text-cyan/80">Por Adam e William</p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            to="/shopcart"
                            className="relative flex items-center gap-2 text-sm bg-royal hover:bg-royal/90 text-white px-3 py-1.5 rounded-lg transition shadow-sm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Carrinho
                            {totalQuantity > 0 && (
                                <span className="bg-cyan text-navy text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[1.25rem] text-center">
                                    {totalQuantity}
                                </span>
                            )}
                        </Link>
                        <button
                            onClick={handleLogout}
                            className="text-sm text-white/80 hover:text-white border border-white/30 hover:border-white px-3 py-1.5 rounded-lg transition"
                        >
                            Sair
                        </button>
                    </div>
                </div>
            </header>

            {/* Main */}
            <main className="max-w-7xl mx-auto px-6 py-8">
                {loading ? (
                    <p className="text-gray-500">Carregando...</p>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {products.map((p) => (
                            <div
                                key={p.id}
                                className="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden"
                            >
                                {p.image_url && (
                                    <div className="w-full h-48 bg-white flex items-center justify-center p-4">
                                        <img
                                            src={p.image_url}
                                            alt={p.title}
                                            className="max-h-full object-contain"
                                        />
                                    </div>
                                )}

                                <div className="p-4">
                                    <h3 className="font-medium text-navy truncate">
                                        {p.title}
                                    </h3>
                            
                                    <p className="text-xs text-gray-500 capitalize mt-1">
                                        {p.category}
                                    </p>

                                    {p.stock_quantity <= 0 ? (
                                        <p className="mt-4 text-sm text-red-500 font-medium">
                                            Produto esgotado
                                        </p>
                                    ) : (
                                        <>
                                            <div className="mt-4 flex items-center justify-between">
                                                <p className="font-display text-lg text-royal">
                                                    R$ {Number(p.price).toFixed(2)}
                                                </p>
                                            </div>
                                    
                                            <div className="flex items-center gap-2 mt-2">
                                                <input
                                                    type="number"
                                                    value={quantities[p.id] ?? 1}
                                                    onChange={(e) =>
                                                        handleQuantityChange(p.id, e.target.value)
                                                    }
                                                    className="w-16 border border-gray-300 rounded px-2 py-1 text-sm"
                                                    min={1}
                                                />

                                                <button
                                                    onClick={() => addToCart(p)}
                                                    className="text-sm bg-royal hover:bg-royal/90 text-white px-3 py-1 rounded transition"
                                                >
                                                    Adicionar ao Carrinho
                                                </button>
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
                
                <div className="mt-8 flex justify-center items-center gap-3">
                    <button
                        disabled={page <= 1}
                        onClick={() => fetchProducts(page - 1)}
                        className="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
                    >
                        Anterior
                    </button>
                    <span className="text-sm text-gray-600">Página <strong className="font-display text-navy">{page}</strong></span>
                    <button
                        disabled={page >= lastPage}
                        onClick={() => fetchProducts(page + 1)}
                        className="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
                    >
                        Próxima
                    </button>
                </div>
            </main>
        </div>
    );
}