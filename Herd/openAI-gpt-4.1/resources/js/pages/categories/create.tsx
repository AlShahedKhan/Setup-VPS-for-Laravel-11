import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { FormEvent, useState } from 'react';
import { router } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories Create',
        href: '/categories/create',
    },
];

export default function CategoriesCreate() {

    const [form, setForm] = useState({
        name: '',
        description: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        router.post(route('categories.store'), form);
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { id, value } = e.target;
        setForm(prev => ({
            ...prev,
            [id]: value
        }));
    };



    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Categories Create" />


            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border md:min-h-min">

                    <div className="relative z-40 p-4">
                        <div className="mb-6 text-center">
                            <h2 className="text-2xl font-bold">Create Category</h2>
                            <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">Fill in the form to create a new category.</p>
                        </div>

                        <form onSubmit={handleSubmit} className="flex flex-col gap-4 max-w-6xl mx-auto">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    placeholder="Category Name"
                                    value={form.name}
                                    onChange={handleChange}
                                    required
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="description">Description</Label>
                                <Input
                                    id="description"
                                    type="text"
                                    placeholder="Category Description"
                                    value={form.description}
                                    onChange={handleChange}
                                />
                            </div>
                            <Button type="submit" className="mt-2">Create Category</Button>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
