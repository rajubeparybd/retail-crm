import type { Customer } from './customer';
import type { Paginated, Product } from './product';

export type SaleItem = {
    id: number;
    quantity: number;
    unit_price: string;
    subtotal: string;
    product: Product;
};

export type Sale = {
    id: number;
    total: string;
    created_at: string;
    user: { id: number; name: string } | null;
    customer: Customer | null;
    items: SaleItem[];
};

export type DashboardStats = {
    today_revenue: string;
    total_sales: number;
    low_stock_count: number;
};

export type PaginatedSales = Paginated<Sale>;
