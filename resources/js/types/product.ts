export type Product = {
    id: number;
    name: string;
    sku: string;
    price: string;
    stock_quantity: number;
    created_at: string;
    updated_at: string;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

export type PaginatedProducts = Paginated<Product>;

