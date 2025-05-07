import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: '/categories',
    },
];


export default function Categories() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Categories" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <div className="flex justify-end">
                        <Button asChild>
                            <Link href={route('categories.create')}>Create Category</Link>
                        </Button>

                    </div>
                </div>
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border md:min-h-min">
                    <table className="w-full table-auto">
                        <thead>
                            <tr>
                                <th className="px-4 py-2 text-left">ID</th>
                                <th className="px-4 py-2 text-left">Name</th>
                                <th className="px-4 py-2 text-left">Description</th>
                                <th className="px-4 py-2 text-left">Created At</th>
                                <th className="px-4 py-2 text-left">Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td className="border px-4 py-2">1</td>
                                <td className="border px-4 py-2">Category 1</td>
                                <td className="border px-4 py-2">Description 1</td>
                                <td className="border px-4 py-2">2023-01-01</td>
                                <td className="border px-4 py-2">2023-01-01</td>
                            </tr>
                            <tr>
                                <td className="border px-4 py-2">2</td>
                                <td className="border px-4 py-2">Category 2</td>
                                <td className="border px-4 py-2">Description 2</td>
                                <td className="border px-4 py-2">2023-01-02</td>
                                <td className="border px-4 py-2">2023-01-02</td>
                            </tr>
                            <tr>
                                <td className="border px-4 py-2">3</td>
                                <td className="border px-4 py-2">Category 3</td>
                                <td className="border px-4 py-2">Description 3</td>
                                <td className="border px-4 py-2">2023-01-03</td>
                                <td className="border px-4 py-2">2023-01-03</td>
                            </tr>
                            <tr>
                                <td className="border px-4 py-2">4</td>
                                <td className="border px-4 py-2">Category 4</td>
                                <td className="border px-4 py-2">Description 4</td>
                                <td className="border px-4 py-2">2023-01-04</td>
                                <td className="border px-4 py-2">2023-01-04</td>
                            </tr>
                            <tr>
                                <td className="border px-4 py-2">5</td>
                                <td className="border px-4 py-2">Category 5</td>
                                <td className="border px-4 py-2">Description 5</td>
                                <td className="border px-4 py-2">2023-01-05</td>
                                <td className="border px-4 py-2">2023-01-05</td>
                            </tr>
                            <tr>
                                <td className="border px-4 py-2">6</td>
                                <td className="border px-4 py-2">Category 6</td>
                                <td className="border px-4 py-2">Description 6</td>
                                <td className="border px-4 py-2">2023-01-06</td>
                                <td className="border px-4 py-2">2023-01-06</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
