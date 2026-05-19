import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

export default function UserProducts() {
    const [products, setProducts] = useState([]);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);
    const [loading, setLoading] = useState(false);
    const [quantities, setQuantities] = useState({});
    const [cart, setCart] = useState([]);

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
            setTotal(response.data.total);
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

    async function addToCart(product) {
        const quantity = quantities[product.id] || 1;
        
        try{
            await axios.post("/shopcart",{
                product_id: product.id,
                quantity,
            });
        }
        catch(error){
            console.error('Erro ao adicionar ao carrinho:', error);
        }
        
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
                    <button
                        onClick={handleLogout}
                        className="text-sm text-white/80 hover:text-white border border-white/30 hover:border-white px-3 py-1.5 rounded-lg transition"
                    >
                        Sair
                    </button>
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