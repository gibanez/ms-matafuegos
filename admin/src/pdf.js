import logoUrl from './assets/ms-matafuegos-print.png';
import { formatDate, money } from './utils';

const BUSINESS = {
    name: 'MS Matafuegos',
    address: 'Av. Juan B. Justo 2665',
    city: 'San Miguel de Tucumán · Tucumán',
    phone: '381 597-7614',
    email: 'hola@msmatafuegos.com.ar',
};

const PAYMENT_LABELS = {
    cash: 'Efectivo',
    transfer: 'Transferencia',
    card: 'Tarjeta',
    other: 'Otro',
};

const PAGE = {
    width: 210,
    height: 297,
    margin: 16,
};

const CONTENT_RIGHT = PAGE.width - PAGE.margin;
const CONTENT_WIDTH = CONTENT_RIGHT - PAGE.margin;

const COL_DESCRIPTION = PAGE.margin + 3;
const COL_QUANTITY = 148;
const COL_UNIT_PRICE = 171;
const COL_AMOUNT = CONTENT_RIGHT - 1;

const TABLE_HEADER_HEIGHT = 8;
const ROW_MIN_HEIGHT = 8;
const BODY_BOTTOM = 258;

const COLOR_INK = [11, 15, 20];
const COLOR_MUTED = [110, 119, 130];
const COLOR_BRAND = [200, 25, 35];
const COLOR_LINE = [214, 219, 226];
const COLOR_PANEL = [245, 246, 248];
const COLOR_ZEBRA = [250, 250, 251];
const COLOR_WHITE = [255, 255, 255];

let logoCache;

function pdfMoney(value) {
    return money(value).replace(/\u00a0/g, ' ');
}

function clean(value, fallback = '') {
    const text = String(value === null || value === undefined ? '' : value)
        .replace(/\u00a0/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    return text || fallback;
}

function setFont(pdf, size, style = 'normal', color = COLOR_INK) {
    pdf.setFont('helvetica', style);
    pdf.setFontSize(size);
    pdf.setTextColor(color[0], color[1], color[2]);
}

async function loadLogo() {
    if (logoCache !== undefined) {
        return logoCache;
    }

    try {
        const response = await fetch(logoUrl);

        if (!response.ok) {
            throw new Error(`Logo ${response.status}`);
        }

        const blob = await response.blob();

        logoCache = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(String(reader.result));
            reader.onerror = () => reject(new Error('No se pudo leer el logo.'));
            reader.readAsDataURL(blob);
        });
    } catch {
        logoCache = null;
    }

    return logoCache;
}

function logoSize(width) {
    return { width, height: width * 181 / 387 };
}

function drawHeader(pdf, document, isQuote, logo) {
    if (logo) {
        const size = logoSize(44);
        pdf.addImage(logo, 'PNG', PAGE.margin, 14, size.width, size.height);
    }

    setFont(pdf, 20, 'bold', COLOR_BRAND);
    pdf.text(isQuote ? 'PRESUPUESTO' : 'FACTURA', CONTENT_RIGHT, 26, { align: 'right' });

    setFont(pdf, 11, 'bold', COLOR_INK);
    pdf.text(clean(document.number, 'Sin número'), CONTENT_RIGHT, 33, { align: 'right' });

    const meta = [`Fecha: ${formatDate(document.issueDate)}`];

    if (isQuote) {
        if (document.validUntil) {
            meta.push(`Válido hasta: ${formatDate(document.validUntil)}`);
        }
    } else {
        if (document.dueDate) {
            meta.push(`Vencimiento: ${formatDate(document.dueDate)}`);
        }

        const payment = PAYMENT_LABELS[document.paymentMethod];

        if (payment) {
            meta.push(`Forma de pago: ${payment}`);
        }
    }

    setFont(pdf, 8.5, 'normal', COLOR_MUTED);
    meta.forEach((line, index) => {
        pdf.text(line, CONTENT_RIGHT, 41 + index * 4.4, { align: 'right' });
    });

    if (document.status === 'cancelled') {
        setFont(pdf, 8.5, 'bold', COLOR_BRAND);
        pdf.text('ANULADO', CONTENT_RIGHT, 46 + meta.length * 4.4, { align: 'right' });
    }

    pdf.setDrawColor(COLOR_LINE[0], COLOR_LINE[1], COLOR_LINE[2]);
    pdf.setLineWidth(0.3);
    pdf.line(PAGE.margin, 50, CONTENT_RIGHT, 50);
}

function drawBusiness(pdf) {
    setFont(pdf, 10.5, 'bold', COLOR_INK);
    pdf.text(BUSINESS.name, PAGE.margin, 57);

    setFont(pdf, 8.5, 'normal', COLOR_MUTED);
    pdf.text(`${BUSINESS.address} · ${BUSINESS.city}`, PAGE.margin, 62.2);
    pdf.text(`${BUSINESS.phone} · ${BUSINESS.email}`, PAGE.margin, 66.6);
}

function drawCustomer(pdf, customer) {
    const top = 73;
    const height = 32;

    pdf.setFillColor(COLOR_PANEL[0], COLOR_PANEL[1], COLOR_PANEL[2]);
    pdf.roundedRect(PAGE.margin, top, CONTENT_WIDTH, height, 2.5, 2.5, 'F');

    const left = PAGE.margin + 5;
    const name = clean(customer?.name, 'Consumidor final');

    setFont(pdf, 7.5, 'bold', COLOR_MUTED);
    pdf.text('CLIENTE', left, top + 6.4);

    setFont(pdf, 10.5, 'bold', COLOR_INK);
    pdf.text(name, left, top + 13);

    const identification = [
        customer?.taxId ? `CUIT / Tax ID: ${clean(customer.taxId)}` : null,
        customer?.phone ? `Tel: ${clean(customer.phone)}` : null,
    ].filter(Boolean).join(' · ');

    const location = [
        customer?.address ? clean(customer.address) : null,
        customer?.city ? clean(customer.city) : null,
    ].filter(Boolean).join(' · ');

    setFont(pdf, 8.5, 'normal', COLOR_MUTED);

    if (identification) {
        pdf.text(identification, left, top + 18.6);
    }

    if (location) {
        pdf.text(location, left, top + 23.4);
    }

    if (customer?.email) {
        pdf.text(clean(customer.email), left, top + 28.2);
    }

    return top + height;
}

function drawTableHeader(pdf, y) {
    pdf.setFillColor(COLOR_INK[0], COLOR_INK[1], COLOR_INK[2]);
    pdf.rect(PAGE.margin, y, CONTENT_WIDTH, TABLE_HEADER_HEIGHT, 'F');

    setFont(pdf, 8, 'bold', COLOR_WHITE);
    pdf.text('Descripción', COL_DESCRIPTION, y + 5.3);
    pdf.text('Cant.', COL_QUANTITY, y + 5.3, { align: 'right' });
    pdf.text('P. unit.', COL_UNIT_PRICE, y + 5.3, { align: 'right' });
    pdf.text('Importe', COL_AMOUNT, y + 5.3, { align: 'right' });

    return y + TABLE_HEADER_HEIGHT;
}

function drawContinuationHeader(pdf, document, isQuote, logo) {
    if (logo) {
        const size = logoSize(26);
        pdf.addImage(logo, 'PNG', PAGE.margin, 12, size.width, size.height);
    }

    setFont(pdf, 12, 'bold', COLOR_BRAND);
    pdf.text(`${isQuote ? 'PRESUPUESTO' : 'FACTURA'} ${clean(document.number)}`, CONTENT_RIGHT, 19, { align: 'right' });

    setFont(pdf, 8, 'normal', COLOR_MUTED);
    pdf.text('Continuación', CONTENT_RIGHT, 24, { align: 'right' });

    return drawTableHeader(pdf, 32);
}

function rowHeight(pdf, description) {
    const lines = pdf.splitTextToSize(description, 112);

    return {
        lines,
        height: Math.max(ROW_MIN_HEIGHT, lines.length * 4.4 + 3.6),
    };
}

function drawRow(pdf, y, height, descriptionLines, quantity, unitPrice, amount, options = {}) {
    if (options.zebra) {
        pdf.setFillColor(COLOR_ZEBRA[0], COLOR_ZEBRA[1], COLOR_ZEBRA[2]);
        pdf.rect(PAGE.margin, y, CONTENT_WIDTH, height, 'F');
    }

    const color = options.muted ? COLOR_MUTED : COLOR_INK;

    setFont(pdf, 8.5, options.muted ? 'italic' : 'normal', color);
    descriptionLines.forEach((line, index) => {
        pdf.text(line, COL_DESCRIPTION, y + 5.4 + index * 4.4);
    });

    if (quantity !== null) {
        pdf.text(String(quantity), COL_QUANTITY, y + 5.4, { align: 'right' });
    }

    if (unitPrice !== null) {
        pdf.text(pdfMoney(unitPrice), COL_UNIT_PRICE, y + 5.4, { align: 'right' });
    }

    if (amount !== null) {
        pdf.text(pdfMoney(amount), COL_AMOUNT, y + 5.4, { align: 'right' });
    }

    pdf.setDrawColor(COLOR_LINE[0], COLOR_LINE[1], COLOR_LINE[2]);
    pdf.setLineWidth(0.2);
    pdf.line(PAGE.margin, y + height, CONTENT_RIGHT, y + height);
}

function newPage(pdf, document, isQuote, logo) {
    pdf.addPage('a4', 'portrait');

    return drawContinuationHeader(pdf, document, isQuote, logo);
}

function drawTotals(pdf, document, y) {
    const label = 148;
    let current = y + 9;

    setFont(pdf, 9, 'normal', COLOR_MUTED);
    pdf.text('Subtotal', label, current, { align: 'right' });
    setFont(pdf, 9, 'normal', COLOR_INK);
    pdf.text(pdfMoney(document.subtotal), COL_AMOUNT, current, { align: 'right' });

    current += 6;

    setFont(pdf, 9, 'normal', COLOR_MUTED);
    pdf.text('Ajustes', label, current, { align: 'right' });
    setFont(pdf, 9, 'normal', COLOR_INK);
    pdf.text(pdfMoney(document.adjustmentsTotal), COL_AMOUNT, current, { align: 'right' });

    current += 3.4;
    pdf.setDrawColor(COLOR_LINE[0], COLOR_LINE[1], COLOR_LINE[2]);
    pdf.setLineWidth(0.3);
    pdf.line(120, current, CONTENT_RIGHT, current);

    current += 7;
    setFont(pdf, 10, 'bold', COLOR_INK);
    pdf.text('TOTAL', label, current, { align: 'right' });
    setFont(pdf, 13.5, 'bold', COLOR_BRAND);
    pdf.text(pdfMoney(document.total), COL_AMOUNT, current + 0.4, { align: 'right' });

    return current + 6;
}

function drawNotes(pdf, notes, y) {
    setFont(pdf, 7.5, 'bold', COLOR_MUTED);
    pdf.text('NOTAS', PAGE.margin, y + 6);

    setFont(pdf, 8.5, 'normal', COLOR_INK);
    const lines = pdf.splitTextToSize(clean(notes), CONTENT_WIDTH);
    lines.forEach((line, index) => {
        pdf.text(line, PAGE.margin, y + 11.4 + index * 4.4);
    });

    return y + 11.4 + lines.length * 4.4;
}

function drawFooters(pdf, isQuote) {
    const pages = pdf.getNumberOfPages();

    for (let page = 1; page <= pages; page += 1) {
        pdf.setPage(page);

        pdf.setDrawColor(COLOR_LINE[0], COLOR_LINE[1], COLOR_LINE[2]);
        pdf.setLineWidth(0.3);
        pdf.line(PAGE.margin, 275, CONTENT_RIGHT, 275);

        setFont(pdf, 7.5, 'normal', COLOR_MUTED);
        pdf.text(`${BUSINESS.name} · ${BUSINESS.address} · ${BUSINESS.city}`, PAGE.margin, 279.5);
        pdf.text(`${BUSINESS.phone} · ${BUSINESS.email}`, PAGE.margin, 283.3);

        if (!isQuote) {
            pdf.text('Comprobante no fiscal', PAGE.margin, 287.1);
        } else {
            pdf.text('Presupuesto sin cargo. Precios en pesos argentinos.', PAGE.margin, 287.1);
        }

        pdf.text(`Página ${page} de ${pages}`, CONTENT_RIGHT, 279.5, { align: 'right' });
    }
}

/**
 * Genera y descarga el PDF de un presupuesto o una factura.
 *
 * @param {object} document Documento completo (con líneas, ajustes y cliente).
 * @param {'quotes'|'invoices'} moduleKey
 */
export async function downloadDocumentPdf(document, moduleKey) {
    const isQuote = moduleKey === 'quotes';
    const { jsPDF } = await import('jspdf');
    const logo = await loadLogo();
    const pdf = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });

    drawHeader(pdf, document, isQuote, logo);
    drawBusiness(pdf);
    const customerBottom = drawCustomer(pdf, document.customer);

    let y = drawTableHeader(pdf, customerBottom + 8);

    const lines = Array.isArray(document.lines) ? document.lines : [];
    lines.forEach((line, index) => {
        const description = clean(line.description, 'Ítem sin descripción');
        const measured = rowHeight(pdf, description);

        if (y + measured.height > BODY_BOTTOM) {
            y = newPage(pdf, document, isQuote, logo);
        }

        drawRow(
            pdf,
            y,
            measured.height,
            measured.lines,
            line.quantity ?? '',
            line.unitPrice ?? 0,
            line.lineTotal ?? 0,
            { zebra: index % 2 === 1 },
        );

        y += measured.height;
    });

    const adjustments = Array.isArray(document.adjustments) ? document.adjustments : [];
    adjustments.forEach((adjustment) => {
        const label = `${adjustment.kind === 'discount' ? 'Descuento' : 'Recargo'} · ${clean(adjustment.description, 'Ajuste')}`;
        const measured = rowHeight(pdf, label);

        if (y + measured.height > BODY_BOTTOM) {
            y = newPage(pdf, document, isQuote, logo);
        }

        drawRow(pdf, y, measured.height, measured.lines, null, null, adjustment.amount ?? 0, { muted: true });
        y += measured.height;
    });

    if (!lines.length && !adjustments.length) {
        drawRow(pdf, y, ROW_MIN_HEIGHT, ['Sin ítems'], null, null, null, { muted: true });
        y += ROW_MIN_HEIGHT;
    }

    if (y + 40 > 270) {
        y = newPage(pdf, document, isQuote, logo);
    }

    y = drawTotals(pdf, document, y);

    if (document.notes) {
        if (y + 30 > 272) {
            y = newPage(pdf, document, isQuote, logo);
        }

        y = drawNotes(pdf, document.notes, y);
    }

    drawFooters(pdf, isQuote);

    const prefix = isQuote ? 'Presupuesto' : 'Factura';
    const number = clean(document.number, 'sin-numero').replace(/[^A-Za-z0-9-_]/g, '-');

    pdf.save(`${prefix}-${number}.pdf`);
}
