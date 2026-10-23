export const MODULES = {
    dashboard: { key: 'dashboard', title: 'Panel', eyebrow: 'General', icon: 'pi pi-th-large' },
    products: { key: 'products', title: 'Productos', eyebrow: 'Catálogo', icon: 'pi pi-box' },
    categories: { key: 'categories', title: 'Categorías', eyebrow: 'Catálogo', icon: 'pi pi-tags' },
    providers: { key: 'providers', title: 'Proveedores', eyebrow: 'Catálogo', icon: 'pi pi-truck' },
    customers: { key: 'customers', title: 'Clientes', eyebrow: 'Personas', icon: 'pi pi-users' },
    quotes: { key: 'quotes', title: 'Presupuestos', eyebrow: 'Ventas', icon: 'pi pi-file-edit' },
    invoices: { key: 'invoices', title: 'Facturas', eyebrow: 'Ventas', icon: 'pi pi-file' },
    maintenance: { key: 'maintenance', title: 'Mantenimientos', eyebrow: 'Ventas', icon: 'pi pi-calendar' },
    inquiries: { key: 'inquiries', title: 'Consultas', eyebrow: 'Contenido', icon: 'pi pi-inbox' },
    migrations: { key: 'migrations', title: 'Migraciones', eyebrow: 'Sistema', icon: 'pi pi-database' },
};

export const NAV_SECTIONS = [
    {
        label: 'General',
        items: ['dashboard'],
    },
    {
        label: 'Catálogo',
        items: ['products', 'categories', 'providers'],
    },
    {
        label: 'Ventas',
        items: ['quotes', 'invoices', 'maintenance', 'customers'],
    },
    {
        label: 'Contenido',
        items: ['inquiries'],
    },
    {
        label: 'Sistema',
        items: ['migrations'],
    },
];

export const STATUS_OPTIONS = [
    { label: 'Activo', value: 'active' },
    { label: 'Inactivo', value: 'inactive' },
];

export const QUOTE_STATUS_OPTIONS = [
    { label: 'Borrador', value: 'draft' },
    { label: 'Enviado', value: 'sent' },
    { label: 'Aceptado', value: 'accepted' },
    { label: 'Rechazado', value: 'rejected' },
    { label: 'Cancelado', value: 'cancelled' },
];

export const INVOICE_STATUS_OPTIONS = [
    { label: 'Borrador', value: 'draft' },
    { label: 'Emitida', value: 'issued' },
    { label: 'Pagada', value: 'paid' },
    { label: 'Cancelada', value: 'cancelled' },
];

export const PAYMENT_METHOD_OPTIONS = [
    { label: 'Efectivo', value: 'cash' },
    { label: 'Transferencia', value: 'transfer' },
    { label: 'Tarjeta', value: 'card' },
    { label: 'Otro', value: 'other' },
];

export const LOOKUPS = {
    categories: { path: '/api/categories', key: 'categories', label: 'name' },
    providers: { path: '/api/providers', key: 'providers', label: 'name' },
};

export const RESOURCES = {
    products: {
        endpoint: '/api/products',
        listKey: 'products',
        title: 'Productos',
        singular: 'producto',
        addLabel: 'Nuevo producto',
        searchPlaceholder: 'Buscar por nombre, SKU o categoría…',
        displayName: (row) => row.name,
        search: (row) => [row.name, row.sku, row.slug, row.category?.name, row.provider?.name]
            .filter(Boolean)
            .join(' ')
            .toLowerCase(),
        columns: [
            { field: 'name', header: 'Producto', type: 'product', sortable: true, style: 'min-width: 260px' },
            { field: 'sku', header: 'SKU', type: 'mono', sortable: true },
            { field: 'category.name', header: 'Categoría', type: 'nested' },
            { field: 'price', header: 'Precio', type: 'money', align: 'right', sortable: true },
            { field: 'stock', header: 'Stock', type: 'stock', align: 'center', sortable: true },
            { field: 'requiresMaintenance', header: 'Mantenimiento', type: 'maintenance', align: 'center' },
            { field: 'status', header: 'Estado', type: 'status', align: 'center' },
        ],
        actions: ['media', 'edit', 'delete'],
        fields: [
            { name: 'name', label: 'Nombre', type: 'text', required: true, placeholder: 'Matafuego ABC 10 kg' },
            { name: 'slug', label: 'Slug', type: 'text', nullable: true, placeholder: 'matafuego-abc-10kg' },
            { name: 'sku', label: 'SKU', type: 'text', nullable: true, placeholder: 'MAT-ABC-10' },
            { name: 'categoryId', label: 'Categoría', type: 'select', source: 'categories', emptyLabel: 'Sin categoría', nullable: true, filter: true },
            { name: 'providerId', label: 'Proveedor', type: 'select', source: 'providers', emptyLabel: 'Sin proveedor', nullable: true, filter: true },
            { name: 'price', label: 'Precio', type: 'number', min: 0, step: 0.01, minFractionDigits: 2, maxFractionDigits: 2, default: 0 },
            { name: 'stock', label: 'Stock', type: 'number', min: 0, step: 1, maxFractionDigits: 0, default: 0 },
            { name: 'minStock', label: 'Stock mínimo', type: 'number', min: 0, step: 1, maxFractionDigits: 0, default: 0 },
            { name: 'showPrice', label: 'Mostrar precio en el sitio', type: 'toggle', default: true },
            { name: 'featured', label: 'Destacado en el catálogo', type: 'toggle', default: false },
            { name: 'requiresMaintenance', label: 'Requiere mantenimiento periódico', type: 'toggle', default: false },
            {
                name: 'maintenanceDays',
                label: 'Días hasta el próximo mantenimiento',
                type: 'number',
                min: 1,
                max: 3650,
                step: 1,
                maxFractionDigits: 0,
                default: 365,
                showWhen: { field: 'requiresMaintenance', value: true },
            },
            { name: 'status', label: 'Estado', type: 'select', options: STATUS_OPTIONS, default: 'active' },
            { name: 'description', label: 'Descripción', type: 'textarea', rows: 4, full: true, nullable: true },
        ],
    },
    categories: {
        endpoint: '/api/categories',
        listKey: 'categories',
        title: 'Categorías',
        singular: 'categoría',
        addLabel: 'Nueva categoría',
        searchPlaceholder: 'Buscar por nombre, slug o descripción…',
        displayName: (row) => row.name,
        search: (row) => [row.name, row.slug, row.description].filter(Boolean).join(' ').toLowerCase(),
        columns: [
            { field: 'name', header: 'Nombre', type: 'strong', sortable: true },
            { field: 'slug', header: 'Slug', type: 'mono', sortable: true },
            { field: 'description', header: 'Descripción', type: 'excerpt' },
            { field: 'status', header: 'Estado', type: 'status', align: 'center' },
        ],
        actions: ['edit', 'delete'],
        fields: [
            { name: 'name', label: 'Nombre', type: 'text', required: true, placeholder: 'Extintores' },
            { name: 'slug', label: 'Slug', type: 'text', nullable: true, placeholder: 'extintores' },
            { name: 'status', label: 'Estado', type: 'select', options: STATUS_OPTIONS, default: 'active' },
            { name: 'description', label: 'Descripción', type: 'textarea', rows: 3, full: true, nullable: true },
        ],
    },
    providers: {
        endpoint: '/api/providers',
        listKey: 'providers',
        title: 'Proveedores',
        singular: 'proveedor',
        addLabel: 'Nuevo proveedor',
        searchPlaceholder: 'Buscar por nombre, contacto, email o teléfono…',
        displayName: (row) => row.name,
        search: (row) => [row.name, row.taxId, row.contactName, row.email, row.phone, row.address]
            .filter(Boolean)
            .join(' ')
            .toLowerCase(),
        columns: [
            { field: 'name', header: 'Proveedor', type: 'strong', sortable: true },
            { field: 'contactName', header: 'Contacto', type: 'nested' },
            { field: 'email', header: 'Email', type: 'nested' },
            { field: 'phone', header: 'Teléfono', type: 'nested' },
            { field: 'status', header: 'Estado', type: 'status', align: 'center' },
        ],
        actions: ['edit', 'delete'],
        fields: [
            { name: 'name', label: 'Nombre', type: 'text', required: true, placeholder: 'Proveedor SA' },
            { name: 'taxId', label: 'CUIT / Tax ID', type: 'text', nullable: true },
            { name: 'contactName', label: 'Contacto', type: 'text', nullable: true },
            { name: 'email', label: 'Email', type: 'text', nullable: true, placeholder: 'ventas@proveedor.com' },
            { name: 'phone', label: 'Teléfono', type: 'text', nullable: true },
            { name: 'status', label: 'Estado', type: 'select', options: STATUS_OPTIONS, default: 'active' },
            { name: 'address', label: 'Dirección', type: 'text', nullable: true, full: true },
            { name: 'notes', label: 'Notas', type: 'textarea', rows: 3, nullable: true, full: true },
        ],
    },
    customers: {
        endpoint: '/api/customers',
        listKey: 'customers',
        title: 'Clientes',
        singular: 'cliente',
        addLabel: 'Nuevo cliente',
        searchPlaceholder: 'Buscar por nombre, CUIT, email o ciudad…',
        displayName: (row) => row.name,
        search: (row) => [row.name, row.taxId, row.email, row.phone, row.city, row.address]
            .filter(Boolean)
            .join(' ')
            .toLowerCase(),
        columns: [
            { field: 'name', header: 'Cliente', type: 'strong', sortable: true },
            { field: 'taxId', header: 'CUIT / Tax ID', type: 'mono' },
            { field: 'email', header: 'Email', type: 'nested' },
            { field: 'phone', header: 'Teléfono', type: 'nested' },
            { field: 'city', header: 'Ciudad', type: 'nested' },
            { field: 'status', header: 'Estado', type: 'status', align: 'center' },
        ],
        actions: ['edit', 'delete'],
        fields: [
            { name: 'name', label: 'Nombre', type: 'text', required: true, placeholder: 'Consorcio Rivadavia' },
            { name: 'taxId', label: 'CUIT / Tax ID', type: 'text', nullable: true },
            { name: 'email', label: 'Email', type: 'text', nullable: true },
            { name: 'phone', label: 'Teléfono', type: 'text', nullable: true },
            { name: 'city', label: 'Ciudad', type: 'text', nullable: true },
            { name: 'status', label: 'Estado', type: 'select', options: STATUS_OPTIONS, default: 'active' },
            { name: 'address', label: 'Dirección', type: 'text', nullable: true, full: true },
            { name: 'notes', label: 'Notas', type: 'textarea', rows: 3, nullable: true, full: true },
        ],
    },
    inquiries: {
        endpoint: '/api/inquiries',
        listKey: 'inquiries',
        title: 'Consultas',
        singular: 'consulta',
        addLabel: null,
        readOnly: true,
        searchPlaceholder: 'Buscar por nombre, email, WhatsApp o mensaje…',
        displayName: (row) => row.name,
        search: (row) => [row.name, row.email, row.phone, row.message].filter(Boolean).join(' ').toLowerCase(),
        columns: [
            { field: 'createdAt', header: 'Fecha', type: 'datetime', sortable: true, style: 'min-width: 175px' },
            { field: 'name', header: 'Nombre', type: 'strong', sortable: true },
            { field: 'email', header: 'Email', type: 'nested' },
            { field: 'phone', header: 'WhatsApp', type: 'nested' },
            { field: 'message', header: 'Mensaje', type: 'excerpt' },
        ],
        actions: ['view'],
        fields: [],
    },
};

export const DOCUMENTS = {
    quotes: {
        endpoint: '/api/quotes',
        listKey: 'quotes',
        title: 'Presupuestos',
        singular: 'presupuesto',
        addLabel: 'Nuevo presupuesto',
        searchPlaceholder: 'Buscar por número, cliente o estado…',
        displayName: (row) => row.number,
        search: (row) => [row.number, row.customer?.name, row.status, row.notes]
            .filter(Boolean)
            .join(' ')
            .toLowerCase(),
        columns: [
            { field: 'number', header: 'Número', type: 'strong', sortable: true },
            { field: 'customer.name', header: 'Cliente', type: 'nested', style: 'min-width: 200px' },
            { field: 'issueDate', header: 'Emitido', type: 'date', sortable: true },
            { field: 'validUntil', header: 'Vence', type: 'date' },
            { field: 'total', header: 'Total', type: 'money', align: 'right', sortable: true },
            { field: 'status', header: 'Estado', type: 'status', align: 'center' },
        ],
    },
    invoices: {
        endpoint: '/api/invoices',
        listKey: 'invoices',
        title: 'Facturas',
        singular: 'factura',
        addLabel: 'Nueva factura',
        searchPlaceholder: 'Buscar por número, cliente o estado…',
        displayName: (row) => row.number,
        search: (row) => [row.number, row.customer?.name, row.status, row.paymentMethod, row.notes]
            .filter(Boolean)
            .join(' ')
            .toLowerCase(),
        columns: [
            { field: 'number', header: 'Número', type: 'strong', sortable: true },
            { field: 'customer.name', header: 'Cliente', type: 'nested', style: 'min-width: 200px' },
            { field: 'issueDate', header: 'Emitido', type: 'date', sortable: true },
            { field: 'dueDate', header: 'Vence', type: 'date' },
            { field: 'total', header: 'Total', type: 'money', align: 'right', sortable: true },
            { field: 'status', header: 'Estado', type: 'status', align: 'center' },
        ],
    },
};
