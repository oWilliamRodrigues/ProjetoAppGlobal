import React from 'react';
import { Link } from 'react-router-dom';
import { useShopcart } from './ShopcartContext';


export function EmptyShopcart() {
    return (
        <div className="text-center py-10">
            <h2 className="text-2xl font-semibold mb-4">Seu carrinho está vazio</h2>
        </div>
    );
}

export function Shopcart() {
    const { items } = useShopcart();

    if (items.length === 0) {
        return <EmptyShopcart />;
    }

    return (
        <div className="max-w-4xl mx-auto p-4">
            <h1 className="text-3xl font-bold mb-6">Seu Carrinho</h1>
        <ShopcartItems />
        <ShopcartSummary />
        </div>
    );
}

export function ShopcartItems() {
    const { items, updateItemQuantity, removeFromShopcart } = useShopcart();

    return (
        <div>
            <table className="w-full table-auto mb-6">
                <thead>
                    <tr>
                        <th className="border px-4 py-2">Produto</th>
                        <th className="border px-4 py-2">Preço</th>
                        <th className="border px-4 py-2">Quantidade</th>
                        <th className="border px-4 py-2">Total</th>
                        <th className="border px-4 py-2">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    {items.map((item) => (
                        <tr key={item.product_id}>
                            <td className="border px-4 py-2">{item.name}</td>
                            <td className="border px-4 py-2">R$ {item.price.toFixed(2)}</td>
                            <td className="border px-4 py-2">
                                <input
                                    type="number"
                                    value={item.quantity}
                                    min="1"
                                    className="w-16 border rounded px-2 py-1"
                                    onChange={(e) => updateItemQuantity(item, e.target.value)}
                                />
                            </td>
                            <td className="border px-4 py-2">R$ {(item.price * item.quantity).toFixed(2)}</td>
                            <td className="border px-4 py-2">
                                <button
                                    className="bg-red-500 text-white px-3 py-1 rounded"
                                    onClick={() => removeFromShopcart(item)}
                                >
                                    Remover
                                </button>
                            </td>   
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export function ShopcartSummary() {
    const { subtotal, totalQuantity } = useShopcart();

    return (
        <div className="bg-gray-100 p-4 rounded">
            <p className="mb-1">Quantidade Total: {totalQuantity}</p>
            <p className="mb-4">Subtotal: R$ {subtotal.toFixed(2)}</p>
            <Link to="/checkout" className="bg-green-500 text-white px-4 py-2 rounded">
                Finalizar Compra
            </Link>
        </div>
    );
}