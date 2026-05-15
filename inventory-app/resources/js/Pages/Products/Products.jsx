import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

export default function Products() {
    const [products, setProducts] = useState([]);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(false);
    const [stockEdit, setStockEdit] = useState({});

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
        } catch (error) {
            console.error('Erro ao carregar produtos:', error);
        } 
        
        setLoading(false);
    }

    useEffect(() => {
        fetchProducts(1);
    }, []);

    async function updateStock(productId, value) {
        setStockEdit((prev) => ({ 
            ...prev,
            [productId]: value, 
        }));
    }
    async function saveStock(productId) {
        try {
            await axios.patch(`/products/${productId}/stock`, {
                operation: 'set',
                quantity: Number(stockEdit[productId] || 0),
            });
            
            alert('Estoque atualizado com sucesso!');

            fetchProducts(page);
        } catch (error) {
            console.error('Erro ao salvar estoque:', error);
        }
    }

    async function syncProducts(){
        try {
            await axios.post("/products/sync");
            alert("Sincronizado!");

            fetchProducts(page);
        }catch (error) {
            console.error('Erro ao sincronizar produtos:', error);
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
                <div className="flex justify-between items-center mb-6">
                    <h2 className="font-sans text-2xl font-semibold text-navy">Produtos</h2>
                    <button
                        onClick={syncProducts}
                        className="bg-cta hover:bg-cta/90 text-white font-medium px-4 py-2 rounded-lg transition shadow-md"
                    >
                        Sincronizar com Fake Store
                    </button>
                </div>

                {loading ? (
                    <p className="text-gray-500">Carregando...</p>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {products.map((p) => (
                            <div key={p.id} className="bg-white rounded-xl shadow-sm hover:shadow-md transition p-4 flex gap-4">
                                {p.image_url && (
                                    <img src={p.image_url} alt={p.title} className="w-20 h-20 object-contain flex-shrink-0" />
                                )}
                                <div className="flex-1 min-w-0">
                                    <h3 className="font-medium text-navy truncate">{p.title}</h3>
                                    <p className="text-xs text-gray-500 capitalize">{p.category}</p>
                                    <p className="font-display text-lg text-royal mt-1">R$ {Number(p.price).toFixed(2)}</p>
                                    <div className="mt-2 flex items-center gap-2">
                                        <input
                                            type="number"
                                            value={stockEdit[p.id] ?? p.stock_quantity}
                                            onChange={(e) => updateStock(p.id, e.target.value)}
                                            className="w-20 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-royal"
                                        />
                                        <button
                                            onClick={() => saveStock(p.id)}
                                            className="text-sm bg-royal hover:bg-royal/90 text-white px-3 py-1 rounded transition"
                                        >
                                            Salvar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Paginação */}
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
                        onClick={() => fetchProducts(page + 1)}
                        className="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition"
                    >
                        Próxima
                    </button>
                </div>
            </main>
        </div>
    );
}