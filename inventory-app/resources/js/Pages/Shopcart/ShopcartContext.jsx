import { createContext, useContext, useEffect, useState } from 'react';

const ShopcartContext = createContext(null);

const STORAGE_KEY = 'shopcart';

export const ShopcartProvider = ({ children }) => {
    const [items, setItems] = useState(() => {
        const storedShopcart = localStorage.getItem(STORAGE_KEY);
        return storedShopcart ? JSON.parse(storedShopcart) : [];
    });

    useEffect(() => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    }, [items]);

    const addToShopcart = (product) => {
        setItems((prevItems) => {
            const existingItem = prevItems.find((i) => i.product_id === product.product_id);
            if (existingItem) {
                return prevItems.map((i) =>
                    i.product_id === product.product_id ? { ...i, quantity: i.quantity + 1 } : i
                );
            }
            return [...prevItems, product];
        });
    };

    const removeFromShopcart = (product) => {
        setItems((prevItems) => prevItems.filter((i) => i.product_id !== product.product_id));
    };

    const updateItemQuantity = (product, newQuantity) => {
        const quantity = Number(newQuantity);
        if (quantity <= 0) {
            return removeFromShopcart(product);
        }
        setItems((prevItems) =>
            prevItems.map((i) =>
                i.product_id === product.product_id ? { ...i, quantity: quantity } : i
            )
        );
    };

    const clearShopcart = () => {
        setItems([]);
    };

    const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const totalQuantity = items.reduce((sum, item) => sum + item.quantity, 0);

    return (
        <ShopcartContext.Provider
            value={{
                items,
                subtotal,
                totalQuantity,
                addToShopcart,
                removeFromShopcart,
                updateItemQuantity,
                clearShopcart
            }}
        >
            {children}
        </ShopcartContext.Provider>
    );
};

export function useShopcart() {
    const context = useContext(ShopcartContext);
    if (!context) {
        throw new Error('useShopcart must be used within a ShopcartProvider');
    }
    return context;
};