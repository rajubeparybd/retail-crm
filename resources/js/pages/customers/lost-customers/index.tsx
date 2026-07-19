import { Head, router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    Trophy,
    UserCheck,
    Users,
} from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    index as lostCustomersIndex,
    update as lostCustomersUpdate,
} from '@/routes/customers/lost-customers';
import type { BreadcrumbItem, Employee, PaginatedLostCustomers } from '@/types';

type Props = {
    customers: PaginatedLostCustomers;
    employees: Employee[];
    days: number;
};

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

    for (let p = start; p <= end; p += 1) {
        items.push(p);
    }

    if (end < last - 1) {
        items.push('ellipsis');
    }

    items.push(last);

    return items;
}

export default function AdminLostCustomers({
    customers,
    employees,
    days,
}: Props) {
    const pageHref = (page: number): string =>
        lostCustomersIndex({ query: { page } }).url;

    function handleAssign(customerId: number, value: string): void {
        const employeeId = value === 'none' ? null : Number(value);
        router.put(
            lostCustomersUpdate(customerId).url,
            { employee_id: employeeId },
            { preserveScroll: true },
        );
    }

    return (
        <>
            <Head title="Lost Customers — Admin" />

            <h1 className="sr-only">Lost Customer Assignment</h1>

            <div className="space-y-6 p-4 md:p-6">
                <Heading
                    title="Lost Customer Assignment"
                    description={`Customers with no purchase in the last ${days} days. Assign an employee to follow up.`}
                />

                {/* KPI scoreboard */}
                {employees.length > 0 && (
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        {employees.map((emp) => (
                            <Card
                                key={emp.id}
                                className="flex items-center gap-4 p-4"
                            >
                                <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                    <Trophy className="size-5 text-primary" />
                                </div>
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-medium">
                                        {emp.name}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        KPI score:{' '}
                                        <span className="font-semibold text-foreground">
                                            {emp.kpi_score}
                                        </span>
                                    </p>
                                </div>
                            </Card>
                        ))}
                    </div>
                )}

                <div className="overflow-hidden rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Customer</TableHead>
                                <TableHead>Last purchase</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="min-w-52">
                                    Assigned to
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {customers.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={4}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        <Users className="mx-auto mb-2 size-8 opacity-30" />
                                        No lost customers right now — great
                                        retention!
                                    </TableCell>
                                </TableRow>
                            ) : (
                                customers.data.map((customer) => (
                                    <TableRow key={customer.id}>
                                        <TableCell>
                                            <div className="font-medium">
                                                {customer.name}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {customer.email}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {customer.last_purchase_at ? (
                                                new Date(
                                                    customer.last_purchase_at,
                                                ).toLocaleDateString()
                                            ) : (
                                                <span className="text-muted-foreground">
                                                    Never
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {customer.assigned_employee ? (
                                                <Badge
                                                    variant="outline"
                                                    className="gap-1.5 text-xs"
                                                >
                                                    <UserCheck className="size-3" />
                                                    Assigned
                                                </Badge>
                                            ) : (
                                                <Badge
                                                    variant="secondary"
                                                    className="text-xs"
                                                >
                                                    Unassigned
                                                </Badge>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Select
                                                value={
                                                    customer.assigned_employee
                                                        ? String(
                                                              customer
                                                                  .assigned_employee
                                                                  .id,
                                                          )
                                                        : 'none'
                                                }
                                                onValueChange={(val) =>
                                                    handleAssign(
                                                        customer.id,
                                                        val,
                                                    )
                                                }
                                            >
                                                <SelectTrigger
                                                    id={`assign-employee-${customer.id}`}
                                                    className="w-full max-w-52"
                                                >
                                                    <SelectValue placeholder="Select employee…" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="none">
                                                        — Unassigned —
                                                    </SelectItem>
                                                    {employees.map((emp) => (
                                                        <SelectItem
                                                            key={emp.id}
                                                            value={String(
                                                                emp.id,
                                                            )}
                                                        >
                                                            {emp.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
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
                            {customers.total} lost customers
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
                                        <a
                                            href={pageHref(
                                                Math.max(
                                                    1,
                                                    customers.current_page - 1,
                                                ),
                                            )}
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
                                        </a>
                                    </PaginationLink>
                                </PaginationItem>

                                {pageItems(
                                    customers.current_page,
                                    customers.last_page,
                                ).map((item, idx) =>
                                    item === 'ellipsis' ? (
                                        <PaginationItem key={`e-${idx}`}>
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
                                                <a href={pageHref(item)}>
                                                    {item}
                                                </a>
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
                                        <a
                                            href={pageHref(
                                                Math.min(
                                                    customers.last_page,
                                                    customers.current_page + 1,
                                                ),
                                            )}
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
                                        </a>
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

AdminLostCustomers.layout = {
    breadcrumbs: [
        {
            title: 'Admin',
            href: '#',
        },
        {
            title: 'Lost Customers',
            href: lostCustomersIndex().url,
        },
    ] as BreadcrumbItem[],
};
