import { Head, useForm } from '@inertiajs/react';
import { Plus, Search, Trash2, UserSearch } from 'lucide-react';
import { useMemo, useState  } from 'react';
import type {FormEvent} from 'react';
import { toast } from 'sonner';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    create as posRoute,
    customer as findCustomerRoute,
    store as storeSale,
} from '@/routes/sales';
import type { BreadcrumbItem, Customer, Product } from '@/types';

type Props = {
    products: Product[];
};

type CartLine = {
    product_id: number;
    name: string;
    price: string;
    stock: number;
    quantity: number;
};

const currency = new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
});

export default function PointOfSale({ products }: Props) {
    const [query, setQuery] = useState('');
    const [cart, setCart] = useState<CartLine[]>([]);
    const [customer, setCustomer] = useState<Customer | null>(null);
    const [looking, setLooking] = useState(false);

    const form = useForm({
        customer_email: '',
        customer_name: '',
        customer_phone: '',
        items: [] as { product_id: number; quantity: number }[],
    });

    const matches = useMemo(() => {
        const needle = query.trim().toLowerCase();

        if (needle === '') {
            return products;
        }

        return products.filter(
            (product) =>
                product.name.toLowerCase().includes(needle) ||
                product.sku.toLowerCase().includes(needle),
        );
    }, [query, products]);

    const addToCart = (product: Product): void => {
        setCart((prev) => {
            const existing = prev.find(
                (line) => line.product_id === product.id,
            );

            if (existing) {
                return prev.map((line) =>
                    line.product_id === product.id
                        ? { ...line, quantity: line.quantity + 1 }
                        : line,
                );
            }

            return [
                ...prev,
                {
                    product_id: product.id,
                    name: product.name,
                    price: product.price,
                    stock: product.stock_quantity,
                    quantity: 1,
                },
            ];
        });
    };

    const setLineQty = (productId: number, value: string): void => {
        setCart((prev) =>
            prev.map((line) =>
                line.product_id === productId
                    ? { ...line, quantity: Math.max(1, Number(value) || 1) }
                    : line,
            ),
        );
    };

    const removeLine = (productId: number): void => {
        setCart((prev) => prev.filter((line) => line.product_id !== productId));
    };

    const total = cart.reduce(
        (sum, line) => sum + Number(line.price) * line.quantity,
        0,
    );

    const lookupCustomer = async (): Promise<void> => {
        const email = form.data.customer_email.trim();

        if (email === '') {
            toast.error('Enter a customer email first.');

            return;
        }

        setLooking(true);

        try {
            const response = await fetch(
                findCustomerRoute({ query: { email } }).url,
                { headers: { Accept: 'application/json' } },
            );
            const json = (await response.json()) as {
                customer: Customer | null;
            };
            const found = json.customer;

            if (found) {
                setCustomer(found);
                form.setData('customer_name', found.name);
                form.setData('customer_phone', found.phone ?? '');
                toast.success(`Loaded customer: ${found.name}`);
            } else {
                setCustomer(null);
                toast.info('No customer found — enter details to create.');
            }
        } catch {
            toast.error('Could not look up customer.');
        } finally {
            setLooking(false);
        }
    };

    const submit = (event: FormEvent): void => {
        event.preventDefault();

        form.transform(() => ({
            ...form.data,
            items: cart.map((line) => ({
                product_id: line.product_id,
                quantity: line.quantity,
            })),
        }));

        form.post(storeSale().url, { preserveScroll: true });
    };

    return (
        <>
            <Head title="POS" />

            <h1 className="sr-only">Point of sale</h1>

            <div className="space-y-6 p-4 md:p-6">
                <Heading
                    title="Point of Sale"
                    description="Search products, attach a customer, complete the sale"
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Customer</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-3">
                        <div className="grid gap-2">
                            <Label htmlFor="customer_email">Email</Label>
                            <div className="flex gap-2">
                                <Input
                                    id="customer_email"
                                    type="email"
                                    value={form.data.customer_email}
                                    onChange={(event) => {
                                        form.setData(
                                            'customer_email',
                                            event.target.value,
                                        );
                                        setCustomer(null);
                                    }}
                                    placeholder="customer@example.com"
                                />
                                <Button
                                    type="button"
                                    variant="secondary"
                                    onClick={lookupCustomer}
                                    disabled={looking}
                                >
                                    <UserSearch className="size-4" /> Lookup
                                </Button>
                            </div>
                            <InputError message={form.errors.customer_email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="customer_name">Name</Label>
                            <Input
                                id="customer_name"
                                value={form.data.customer_name}
                                onChange={(event) =>
                                    form.setData(
                                        'customer_name',
                                        event.target.value,
                                    )
                                }
                                placeholder="Customer name"
                            />
                            <InputError message={form.errors.customer_name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="customer_phone">Phone</Label>
                            <Input
                                id="customer_phone"
                                value={form.data.customer_phone}
                                onChange={(event) =>
                                    form.setData(
                                        'customer_phone',
                                        event.target.value,
                                    )
                                }
                                placeholder="Optional"
                            />
                        </div>

                        {customer && (
                            <p className="text-sm text-muted-foreground md:col-span-3">
                                Existing customer · ID #{customer.id}
                            </p>
                        )}
                    </CardContent>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Products</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="relative">
                                <Search className="absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                                <Input
                                    value={query}
                                    onChange={(event) =>
                                        setQuery(event.target.value)
                                    }
                                    placeholder="Search by name or SKU…"
                                    className="pl-8"
                                />
                            </div>

                            <div className="max-h-112 divide-y overflow-y-auto rounded-lg border">
                                {matches.length === 0 ? (
                                    <p className="p-4 text-center text-sm text-muted-foreground">
                                        No products match &ldquo;{query}&rdquo;.
                                    </p>
                                ) : (
                                    matches.map((product) => (
                                        <div
                                            key={product.id}
                                            className="flex items-center justify-between gap-2 p-3"
                                        >
                                            <div className="min-w-0">
                                                <p className="truncate font-medium">
                                                    {product.name}
                                                </p>
                                                <p className="font-mono text-xs text-muted-foreground">
                                                    {product.sku} ·{' '}
                                                    {product.stock_quantity} in
                                                    stock
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span className="text-sm font-semibold">
                                                    {currency.format(
                                                        Number(product.price),
                                                    )}
                                                </span>
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="outline"
                                                    disabled={
                                                        product.stock_quantity <
                                                        1
                                                    }
                                                    onClick={() =>
                                                        addToCart(product)
                                                    }
                                                >
                                                    <Plus className="size-4" />{' '}
                                                    Add
                                                </Button>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Cart</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-4">
                                {cart.length === 0 ? (
                                    <p className="py-6 text-center text-muted-foreground">
                                        Cart is empty. Add products to begin.
                                    </p>
                                ) : (
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Item</TableHead>
                                                <TableHead>Stock</TableHead>
                                                <TableHead>Qty</TableHead>
                                                <TableHead className="text-right">
                                                    Subtotal
                                                </TableHead>
                                                <TableHead />
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {cart.map((line) => {
                                                const oversold =
                                                    line.quantity > line.stock;

                                                return (
                                                    <TableRow
                                                        key={line.product_id}
                                                    >
                                                        <TableCell className="font-medium">
                                                            {line.name}
                                                        </TableCell>
                                                        <TableCell
                                                            className={
                                                                oversold
                                                                    ? 'font-semibold text-destructive'
                                                                    : ''
                                                            }
                                                        >
                                                            {line.stock}
                                                        </TableCell>
                                                        <TableCell>
                                                            <Input
                                                                type="number"
                                                                min={1}
                                                                value={
                                                                    line.quantity
                                                                }
                                                                onChange={(
                                                                    event,
                                                                ) =>
                                                                    setLineQty(
                                                                        line.product_id,
                                                                        event
                                                                            .target
                                                                            .value,
                                                                    )
                                                                }
                                                                className="w-16"
                                                            />
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            {currency.format(
                                                                Number(
                                                                    line.price,
                                                                ) *
                                                                    line.quantity,
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() =>
                                                                    removeLine(
                                                                        line.product_id,
                                                                    )
                                                                }
                                                            >
                                                                <Trash2 className="size-4" />
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })}
                                        </TableBody>
                                    </Table>
                                )}

                                <div className="flex items-center justify-between border-t pt-4 font-semibold">
                                    <span>Total</span>
                                    <span>{currency.format(total)}</span>
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={
                                        form.processing || cart.length === 0
                                    }
                                >
                                    Complete sale
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

PointOfSale.layout = {
    breadcrumbs: [
        {
            title: 'POS',
            href: posRoute().url,
        },
    ] as BreadcrumbItem[],
};
