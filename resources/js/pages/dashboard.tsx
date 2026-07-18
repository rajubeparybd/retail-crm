import { Head, Link } from '@inertiajs/react';
import { AlertTriangle, DollarSign, ShoppingCart, Users } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { index as lostCustomersIndex } from '@/routes/customers/lost-customers';
import { index as salesIndex } from '@/routes/sales';
import type { DashboardStats, Sale } from '@/types';

type Props = {
    stats: DashboardStats;
    recentSales: Sale[];
};

const currency = new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
});

export default function Dashboard({ stats, recentSales }: Props) {
    return (
        <>
            <Head title="Dashboard" />

            <div className="space-y-6 p-4 md:p-6">
                <Heading
                    title="Dashboard"
                    description="Store overview at a glance"
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Today&apos;s revenue
                            </CardTitle>
                            <DollarSign className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-semibold">
                                {currency.format(Number(stats.today_revenue))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total sales
                            </CardTitle>
                            <ShoppingCart className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-semibold">
                                {stats.total_sales}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Low stock products
                            </CardTitle>
                            <AlertTriangle className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-semibold">
                                {stats.low_stock_count}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Lost Customers
                            </CardTitle>
                            <Users className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <Button variant="link" className="h-auto p-0 text-base font-semibold" asChild>
                                <Link href={lostCustomersIndex().url}>Assign employees &rarr;</Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Recent sales</CardTitle>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={salesIndex().url}>View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Items</TableHead>
                                    <TableHead className="text-right">
                                        Total
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {recentSales.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            No sales yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    recentSales.map((sale) => (
                                        <TableRow key={sale.id}>
                                            <TableCell>
                                                {new Date(
                                                    sale.created_at,
                                                ).toLocaleDateString()}
                                            </TableCell>
                                            <TableCell>
                                                {sale.customer?.name ??
                                                    'Walk-in'}
                                            </TableCell>
                                            <TableCell>
                                                {sale.items.length}
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
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
