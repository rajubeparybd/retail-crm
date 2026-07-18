import type { Paginated } from './product';

export type Customer = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    assigned_employee_id: number | null;
};

export type CustomerPurchaseStats = {
    purchase_count: number;
    total_spent: string;
    first_purchase_at: string | null;
    last_purchase_at: string | null;
    avg_per_month: number | null;
};

export type CustomerWithStats = Customer & {
    purchase_count: number;
    total_spent: string | null;
    last_purchase_at: string | null;
};

export type LostCustomerWithAssignment = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    last_purchase_at: string | null;
    assigned_employee: { id: number; name: string } | null;
};

export type PaginatedCustomers = Paginated<CustomerWithStats>;
export type PaginatedLostCustomers = Paginated<LostCustomerWithAssignment>;

