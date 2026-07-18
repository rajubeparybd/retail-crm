import { Form, Head, Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
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
import { create, edit, index as productsIndex } from '@/routes/products';
import type { BreadcrumbItem, PaginatedProducts } from '@/types';

type Props = {
    products: PaginatedProducts;
};

const currency = new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
});

function pageItems(
    current: number,
    last: number,
): (number | 'ellipsis')[] {
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

export default function ProductsIndex({ products }: Props) {
    return (
        <>
            <Head title="Products" />

            <h1 className="sr-only">Products</h1>

            <div className="space-y-6 p-4 md:p-6">
                <Heading
                    title="Products"
                    description="Manage your product catalog"
                />

                <div className="flex justify-end">
                    <Button asChild>
                        <Link href={create()}>Create product</Link>
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>SKU</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Stock</TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {products.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="py-6 text-center text-muted-foreground"
                                    >
                                        No products yet. Create your first
                                        product.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                products.data.map((product) => (
                                    <TableRow key={product.id}>
                                        <TableCell className="font-medium">
                                            {product.name}
                                        </TableCell>
                                        <TableCell className="font-mono text-xs text-muted-foreground">
                                            {product.sku}
                                        </TableCell>
                                        <TableCell>
                                            {currency.format(
                                                Number(product.price),
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {product.stock_quantity}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link href={edit(product)}>
                                                        Edit
                                                    </Link>
                                                </Button>

                                                <Dialog>
                                                    <DialogTrigger asChild>
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                        >
                                                            Delete
                                                        </Button>
                                                    </DialogTrigger>
                                                    <DialogContent>
                                                        <DialogTitle>
                                                            Delete product?
                                                        </DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you
                                                            want to delete{' '}
                                                            <span className="font-medium text-foreground">
                                                                {product.name}
                                                            </span>
                                                            ? This action
                                                            cannot be undone.
                                                        </DialogDescription>

                                                        <Form
                                                            {...ProductController.destroy.form(
                                                                product,
                                                            )}
                                                        >
                                                            {({
                                                                processing,
                                                            }) => (
                                                                <DialogFooter className="gap-2">
                                                                    <DialogClose
                                                                        asChild
                                                                    >
                                                                        <Button variant="secondary">
                                                                            Cancel
                                                                        </Button>
                                                                    </DialogClose>
                                                                    <Button
                                                                        variant="destructive"
                                                                        type="submit"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                    >
                                                                        Delete
                                                                        product
                                                                    </Button>
                                                                </DialogFooter>
                                                            )}
                                                        </Form>
                                                    </DialogContent>
                                                </Dialog>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {products.total > 0 && (
                    <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <p className="text-sm text-muted-foreground">
                            Showing {products.from} to {products.to} of{' '}
                            {products.total} products
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
                                            href={productsIndex({
                                                query: {
                                                    page: Math.max(
                                                        1,
                                                        products.current_page - 1,
                                                    ),
                                                },
                                            }).url}
                                            preserveScroll
                                            preserveState
                                            className={
                                                products.current_page === 1
                                                    ? 'pointer-events-none opacity-50'
                                                    : ''
                                            }
                                            aria-disabled={
                                                products.current_page === 1
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
                                    products.current_page,
                                    products.last_page,
                                ).map((item, index) =>
                                    item === 'ellipsis' ? (
                                        <PaginationItem key={`ellipsis-${index}`}>
                                            <PaginationEllipsis />
                                        </PaginationItem>
                                    ) : (
                                        <PaginationItem key={item}>
                                            <PaginationLink
                                                asChild
                                                isActive={
                                                    item ===
                                                    products.current_page
                                                }
                                            >
                                                <Link
                                                    href={productsIndex({
                                                        query: { page: item },
                                                    }).url}
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
                                            href={productsIndex({
                                                query: {
                                                    page: Math.min(
                                                        products.last_page,
                                                        products.current_page + 1,
                                                    ),
                                                },
                                            }).url}
                                            preserveScroll
                                            preserveState
                                            className={
                                                products.current_page ===
                                                products.last_page
                                                    ? 'pointer-events-none opacity-50'
                                                    : ''
                                            }
                                            aria-disabled={
                                                products.current_page ===
                                                products.last_page
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

ProductsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Products',
            href: productsIndex().url,
        },
    ] as BreadcrumbItem[],
};
