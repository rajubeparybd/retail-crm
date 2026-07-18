import { Head, Link, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import type { BreadcrumbItem, PaginatedCustomers } from '@/types';

type Props = {
    customers: PaginatedCustomers;
    search?: string;
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

export default function CustomersIndex({ customers, search = '' }: Props) {
    const [query, setQuery] = useState(search);

    const [prevSearch, setPrevSearch] = useState(search);

    if (search !== prevSearch) {
        setPrevSearch(search);
        setQuery(search);
    }

    useEffect(() => {
        const term = query.trim();

        if (term === search) {
            return;
        }

        const handle = setTimeout(() => {
            router.get(
                customersIndex({ query: { search: term, page: 1 } }).url,
                {},
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                },
            );
        }, 300);

        return () => clearTimeout(handle);
    }, [query, search]);

    const pageHref = (page: number): string =>
        customersIndex({ query: { search: search.trim(), page } }).url;

    return (
        <>
            <Head title="Customers" />

            <h1 className="sr-only">Customers</h1>

            <div className="space-y-6 p-4 md:p-6">
                <Heading
                    title="Customers"
                    description="Purchase history and stats per customer"
                />

                <div className="relative max-w-sm">
                    <Search className="absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                    <Input
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder="Search by name, email, or phone…"
                        className="pl-8"
                    />
                </div>

                <div className="overflow-hidden rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Customer</TableHead>
                                <TableHead>Purchases</TableHead>
                                <TableHead>Total spent</TableHead>
                                <TableHead>Last purchase</TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {customers.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="py-6 text-center text-muted-foreground"
                                    >
                                        {search.trim()
                                            ? `No customers match “${search.trim()}”.`
                                            : 'No customers yet.'}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                customers.data.map((customer) => (
                                    <TableRow key={customer.id}>
                                        <TableCell>
                                            <Link
                                                href={
                                                    customersShow(customer).url
                                                }
                                                className="font-medium hover:underline"
                                            >
                                                {customer.name}
                                            </Link>
                                            <div className="text-xs text-muted-foreground">
                                                {customer.email}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {customer.purchase_count}
                                        </TableCell>
                                        <TableCell>
                                            {customer.total_spent
                                                ? currency.format(
                                                      Number(
                                                          customer.total_spent,
                                                      ),
                                                  )
                                                : '—'}
                                        </TableCell>
                                        <TableCell>
                                            {customer.last_purchase_at
                                                ? new Date(
                                                      customer.last_purchase_at,
                                                  ).toLocaleDateString()
                                                : '—'}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link
                                                    href={
                                                        customersShow(customer)
                                                            .url
                                                    }
                                                >
                                                    View
                                                </Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {customers.total > 0 && (
                    <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <p className="text-sm text-muted-foreground">
                            Showing {customers.from} to {customers.to} of{' '}
                            {customers.total} customers
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
                                            href={pageHref(
                                                Math.max(
                                                    1,
                                                    customers.current_page - 1,
                                                ),
                                            )}
                                            preserveScroll
                                            preserveState
                                            className={
                                                customers.current_page === 1
                                                    ? 'pointer-events-none opacity-50'
                                                    : ''
                                            }
                                            aria-disabled={
                                                customers.current_page === 1
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
                                    customers.current_page,
                                    customers.last_page,
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
                                                    item ===
                                                    customers.current_page
                                                }
                                            >
                                                <Link
                                                    href={pageHref(item)}
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
                                            href={pageHref(
                                                Math.min(
                                                    customers.last_page,
                                                    customers.current_page + 1,
                                                ),
                                            )}
                                            preserveScroll
                                            preserveState
                                            className={
                                                customers.current_page ===
                                                customers.last_page
                                                    ? 'pointer-events-none opacity-50'
                                                    : ''
                                            }
                                            aria-disabled={
                                                customers.current_page ===
                                                customers.last_page
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

CustomersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Customers',
            href: customersIndex().url,
        },
    ] as BreadcrumbItem[],
};
