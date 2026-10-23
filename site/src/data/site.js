export const business = {
    name: 'MS Matafuegos',
    tagline: 'Seguridad y tranquilidad, siempre a mano.',
    phone: '3815977614',
    phoneDisplay: '381 597-7614',
    phoneInternational: '+543815977614',
    whatsapp: '5493815977614',
    whatsappDisplay: '+54 9 381 597-7614',
    email: 'hola@msmatafuegos.com.ar',
    address: 'Av. Juan B. Justo 2665',
    city: 'San Miguel de Tucumán',
    province: 'Tucumán',
    hours: 'Lunes a sábados · horario comercial',
    delivery: 'Servicio a domicilio',
    mapEmbed:
        'https://www.google.com/maps?q=Av.+Juan+B.+Justo+2665,+San+Miguel+de+Tucum%C3%A1n,+Tucum%C3%A1n&output=embed',
    mapLink:
        'https://www.google.com/maps/search/?api=1&query=Av.+Juan+B.+Justo+2665,+San+Miguel+de+Tucum%C3%A1n,+Tucum%C3%A1n',
};

export function whatsappUrl(message = 'Hola MS Matafuegos, quiero hacer una consulta.') {
    return `https://wa.me/${business.whatsapp}?text=${encodeURIComponent(message)}`;
}

export const navItems = [
    { label: 'Inicio', to: '/' },
    { label: 'Productos', to: '/productos' },
    { label: 'Consultas', to: '/consultas' },
    { label: 'Contacto', to: '/contacto' },
];

export const services = [
    {
        icon: 'flame',
        title: 'Recarga y mantenimiento',
        description:
            'Recargamos y revisamos tu matafuego para que esté listo cuando realmente lo necesitás.',
    },
    {
        icon: 'extinguisher',
        title: 'Venta de matafuegos',
        description:
            'Equipos nuevos, accesorios y repuestos para hogares, vehículos, comercios e industrias.',
    },
    {
        icon: 'shield',
        title: 'Elementos de seguridad',
        description:
            'Todo lo necesario para habilitar y mantener tu espacio protegido y en regla.',
    },
    {
        icon: 'truck',
        title: 'Servicio a domicilio',
        description:
            'Coordinamos la visita y nos acercamos a tu negocio, consorcio o domicilio.',
    },
];

export const benefits = [
    'Atención personalizada y presupuesto sin cargo.',
    'Recarga, venta y mantenimiento en un solo lugar.',
    'Servicio para casas, vehículos, comercios, consorcios, galpones y escuelas.',
    'Coordinamos el servicio a domicilio según tu necesidad.',
];

export const steps = [
    {
        number: '01',
        title: 'Contanos qué necesitás',
        description:
            'Escribinos por WhatsApp o completá el formulario con la cantidad y el tipo de equipo.',
    },
    {
        number: '02',
        title: 'Te pasamos un presupuesto',
        description:
            'Revisamos tu caso y te respondemos sin cargo con la opción que mejor se adapta.',
    },
    {
        number: '03',
        title: 'Coordinamos el servicio',
        description:
            'Pasamos a buscar el equipo o coordinamos una visita a domicilio en el día y horario acordado.',
    },
];

export const faqs = [
    {
        question: '¿Cada cuánto se recarga un matafuego?',
        answer:
            'Depende del tipo de equipo y del uso. Como referencia general, la recarga suele hacerse una vez al año. Si tenés dudas, escribinos y te orientamos.',
    },
    {
        question: '¿Trabajan con servicios a domicilio?',
        answer:
            'Sí. Coordinamos la visita para comercios, consorcios, galpones, escuelas y domicilios. Contanos dónde estás y qué necesitás.',
    },
    {
        question: '¿Puedo comprar un matafuego nuevo?',
        answer:
            'Sí. Además de la recarga, ofrecemos matafuegos nuevos, accesorios y elementos de seguridad.',
    },
    {
        question: '¿Atienden vehículos y consorcios?',
        answer:
            'Sí. Trabajamos con hogares, vehículos, comercios, consorcios, galpones, escuelas y otros espacios que necesiten mantener su seguridad al día.',
    },
];

export const placeholderCategories = [
    'Todos',
    'Hogar',
    'Vehículos',
    'Comercios',
    'Industria',
];
