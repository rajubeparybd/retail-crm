import { Form, Head, Link } from '@inertiajs/react';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as productsIndex } from '@/routes/products';
import type { BreadcrumbItem, Product } from '@/types';

type Props = {
    product: Product;
};

export default function ProductEdit({ product }: Props) {
    return (
        <>
            <Head title={`Edit ${product.name}`} />

            <h1 className="sr-only">Edit product</h1>

            <div className="space-y-6 p-4 md:p-6">
                <Heading
                    title="Edit product"
                    description="Update product details"
                />

                <Card className="max-w-2xl">
                    <CardContent>
                        <Form
                            {...ProductController.update.form(product)}
                            options={{ preserveScroll: true }}
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">
                                            Product name
                                        </Label>
                                        <Input
                                            id="name"
                                            className="mt-1 block w-full"
                                            defaultValue={product.name}
                                            name="name"
                                            required
                                            autoFocus
                                            placeholder="Product name"
                                        />
                                        <InputError
                                            className="mt-2"
                                            message={errors.name}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="sku">SKU</Label>
                                        <Input
                                            id="sku"
                                            className="mt-1 block w-full"
                                            defaultValue={product.sku}
                                            name="sku"
                                            required
                                            placeholder="Stock keeping unit"
                                        />
                                        <InputError
                                            className="mt-2"
                                            message={errors.sku}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="price">
                                            Price (USD)
                                        </Label>
                                        <Input
                                            id="price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            className="mt-1 block w-full"
                                            defaultValue={product.price}
                                            name="price"
                                            required
                                            placeholder="0.00"
                                        />
                                        <InputError
                                            className="mt-2"
                                            message={errors.price}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="stock_quantity">
                                            Stock quantity
                                        </Label>
                                        <Input
                                            id="stock_quantity"
                                            type="number"
                                            min="0"
                                            className="mt-1 block w-full"
                                            defaultValue={product.stock_quantity}
                                            name="stock_quantity"
                                            required
                                            placeholder="0"
                                        />
                                        <InputError
                                            className="mt-2"
                                            message={errors.stock_quantity}
                                        />
                                    </div>

                                    <div className="flex items-center gap-4">
                                        <Button disabled={processing}>
                                            Save changes
                                        </Button>
                                        <Button variant="ghost" asChild>
                                            <Link href={productsIndex()}>
                                                Cancel
                                            </Link>
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

ProductEdit.layout = {
    breadcrumbs: [
        {
            title: 'Products',
            href: productsIndex().url,
        },
        {
            title: 'Edit',
            href: productsIndex().url,
        },
    ] as BreadcrumbItem[],
};
