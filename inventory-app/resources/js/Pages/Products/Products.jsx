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
        <div style={{ padding: 20 }}>
            <h1>Products</h1>

            <button onClick={handleLogout}>
                Sair
            </button>

            <button onClick={syncProducts}>
                Sincronizar com Fake Store API
            </button>

            {loading && <p>Carregando...</p>}

            <table border="1" cellPadding="8" style={{ marginTop: 20 }}>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Editar estoque</th>
                        <th>Ação</th>
                    </tr>
                </thead>

                <tbody>
                    {products.map((p) => (
                        <tr key={p.id}>
                            <td>{p.id}</td>
                            <td>{p.title}</td>
                            <td>{p.price}</td>
                            <td>{p.stock_quantity}</td>

                            <td>
                                <input
                                    type="number"
                                    value={stockEdit[p.id] ?? p.stock_quantity}
                                    onChange={(e) =>
                                        updateStock(p.id, e.target.value)
                                    }
                                />
                            </td>

                            <td>
                                <button onClick={() => saveStock(p.id)}>
                                    Salvar
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <div style={{ marginTop: 20 }}>
                <button
                    disabled={page <= 1}
                    onClick={() => fetchProducts(page - 1)}
                >
                    Anterior
                </button>

                <span style={{ margin: "0 10px" }}>Página {page}</span>

                <button onClick={() => fetchProducts(page + 1)}>
                    Próxima
                </button>
            </div>
        </div>
    );
}