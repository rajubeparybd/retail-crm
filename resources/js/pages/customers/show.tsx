import { Head, Link } from '@inertiajs/react';
import {
    CalendarClock,
    ChevronLeft,
    ChevronRight,
    DollarSign,
    ShoppingCart,
    TrendingUp,
} from 'lucide-react';
import Heading from '@/components/heading';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
} from '@/components/ui/pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    index as customersIndex,
    show as customersShow,
} from '@/routes/customers';
import type {
    BreadcrumbItem,
    CustomerPurchaseStats,
    PaginatedSales,
} from '@/types';
import type { Customer } from '@/types';

type Props = {
    customer: Customer;
    stats: CustomerPurchaseStats;
    sales: PaginatedSales;
};

const currency = new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
});

function pageItems(current: number, last: number): (number | 'ellipsis')[] {
    if (last <= 7) {
        return Array.from({ length: last }, (_, i) => i + 1);
    }

    const items: (number | 'ellipsis')[] = [1];
    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    if (start > 2) {
        items.push('ellipsis');
    }

    for (let page = start; page <= end; page += 1) {
        items.push(page);
    }

    if (end < last - 1) {
        items.push('ellipsis');
    }

    items.push(last);

    return items;
}

export default function CustomerShow({ customer, stats, sales }: Props) {
    return (
        <>
            <Head title={customer.name} />

            <h1 className="sr-only">{customer.name}</h1>

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between gap-4">
                    <Heading
                        title="Customer"
                        description="Purchase history and lifetime stats"
                    />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-xl">
                            {customer.name}
                        </CardTitle>
                        <CardDescription>
                            {customer.email}
                            {customer.phone ? ` · ${customer.phone}` : ''}
                        </CardDescription>
                    </CardHeader>
                </Card>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total purchases
                            </CardTitle>
                            <ShoppingCart className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-semibold">
                                {stats.purchase_count}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total spent
                            </CardTitle>
                            <DollarSign className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-semibold">
                                {currency.format(Number(stats.total_spent))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Last purchase
                            </CardTitle>
                            <CalendarClock className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-semibold">
                                {stats.last_purchase_at
                                    ? new Date(
                                          stats.last_purchase_at,
                                      ).toLocaleDateString()
                                    : '—'}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Avg per month
                            </CardTitle>
                            <TrendingUp className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-semibold">
                                {stats.avg_per_month !== null
                                    ? `${stats.avg_per_month}/mo`
                                    : '—'}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="overflow-hidden rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Items</TableHead>
                                <TableHead>Cashier</TableHead>
                                <TableHead className="text-right">
                                    Total
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {sales.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={4}
                                        className="py-6 text-center text-muted-foreground"
                                    >
                                        No purchases yet.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                sales.data.map((sale) => (
                                    <TableRow key={sale.id}>
                                        <TableCell>
                                            {new Date(
                                                sale.created_at,
                                            ).toLocaleDateString()}
                                        </TableCell>
                                        <TableCell>
                                            {sale.items.length}
                                        </TableCell>
                                        <TableCell>
                                            {sale.user?.name ?? '—'}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {currency.format(
                                                Number(sale.total),
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {sales.total > 0 && (
                    <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <p className="text-sm text-muted-foreground">
                            Showing {sales.from} to {sales.to} of {sales.total}{' '}
                            purchases
                        </p>

                        <Pagination className="mx-0 sm:justify-end">
                            <PaginationContent>
                                <PaginationItem>
                                    <PaginationLink
                                        asChild
                                        size="default"
                                        className="gap-1 px-2.5"
                                        isActive={false}
                                    >
                                        <Link
                                            href={
                                                customersShow(customer, {
                                                    query: {
                                                        page: Math.max(
                                                            1,
                                                            sales.current_page -
                                                                1,
                                                        ),
                                                    },
                                                }).url
                                            }
                                            preserveScroll
                                            preserveState
                                            className={
                                                sales.current_page === 1
                                                    ? 'pointer-events-none opacity-50'
                                                    : ''
                                            }
                                            aria-disabled={
                                                sales.current_page === 1
                                            }
                                        >
                                            <ChevronLeft className="size-4" />
                                            <span className="hidden sm:inline">
                                                Previous
                                            </span>
                                        </Link>
                                    </PaginationLink>
                                </PaginationItem>

                                {pageItems(
                                    sales.current_page,
                                    sales.last_page,
                                ).map((item, index) =>
                                    item === 'ellipsis' ? (
                                        <PaginationItem
                                            key={`ellipsis-${index}`}
                                        >
                                            <PaginationEllipsis />
                                        </PaginationItem>
                                    ) : (
                                        <PaginationItem key={item}>
                                            <PaginationLink
                                                asChild
                                                isActive={
                                                    item === sales.current_page
                                                }
                                            >
                                                <Link
                                                    href={
                                                        customersShow(
                                                            customer,
                                                            {
                                                                query: {
                                                                    page: item,
                                                                },
                                                            },
                                                        ).url
                                                    }
                                                    preserveScroll
                                                    preserveState
                                                >
                                                    {item}
                                                </Link>
                                            </PaginationLink>
                                        </PaginationItem>
                                    ),
                                )}

                                <PaginationItem>
                                    <PaginationLink
                                        asChild
                                        size="default"
                                        className="gap-1 px-2.5"
                                        isActive={false}
                                    >
                                        <Link
                                            href={
                                                customersShow(customer, {
                                                    query: {
                                                        page: Math.min(
                                                            sales.last_page,
                                                            sales.current_page +
                                                                1,
                                                        ),
                                                    },
                                                }).url
                                            }
                                            preserveScroll
                                            preserveState
                                            className={
                                                sales.current_page ===
                                                sales.last_page
                                                    ? 'pointer-events-none opacity-50'
                                                    : ''
                                            }
                                            aria-disabled={
                                                sales.current_page ===
                                                sales.last_page
                                            }
                                        >
                                            <span className="hidden sm:inline">
                                                Next
                                            </span>
                                            <ChevronRight className="size-4" />
                                        </Link>
                                    </PaginationLink>
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    </div>
                )}
            </div>
        </>
    );
}

CustomerShow.layout = {
    breadcrumbs: [
        { title: 'Customers', href: customersIndex().url },
        { title: 'Customer detail', href: customersIndex().url },
    ] as BreadcrumbItem[],
};
