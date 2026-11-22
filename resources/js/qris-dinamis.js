import QRCode from 'qrcode';

/**
 * Kalkulasi Checksum CRC16-CCITT (0x1021) Standar EMVCo / ASPI QRIS Indonesia.
 */
export function crc16(str) {
    let crc = 0xFFFF;
    for (let c = 0; c < str.length; c++) {
        crc ^= str.charCodeAt(c) << 8;
        for (let i = 0; i < 8; i++) {
            if (crc & 0x8000) {
                crc = (crc << 1) ^ 0x1021;
            } else {
                crc = crc << 1;
            }
        }
    }
    return (crc & 0xFFFF).toString(16).toUpperCase().padStart(4, '0');
}

/**
 * Template default QRIS Statis Soto Lamongan Lestari (SLL)
 * Bisa diganti dengan string QRIS toko fisik (BCA, Mandiri, BRI, ShopeePay, Nobu, Midtrans).
 */
export const DEFAULT_STATIC_QRIS = '00020101021126580014ID.LINKAJA.WWW01189360091100223405100208123456785204581253033605802ID5922SOTO LAMONGAN LESTARI6007JAKARTA6105123456304ABCD';

/**
 * Ambil payload QRIS dasar (dari database outlet via POS_CONFIG, atau localStorage jika diset kustom, atau default template).
 */
export function getBaseQrisPayload() {
    if (window.POS_CONFIG && window.POS_CONFIG.outletQrisPayload && window.POS_CONFIG.outletQrisPayload.trim() !== '') {
        return window.POS_CONFIG.outletQrisPayload.trim();
    }
    return localStorage.getItem('pos_custom_qris_payload') || DEFAULT_STATIC_QRIS;
}

/**
 * Simpan string QRIS kustom toko ke localStorage.
 */
export function setCustomQrisPayload(payload) {
    if (payload && payload.trim()) {
        localStorage.setItem('pos_custom_qris_payload', payload.trim());
    } else {
        localStorage.removeItem('pos_custom_qris_payload');
    }
}

/**
 * Konversi String QRIS Statis menjadi QRIS Dinamis Otomatis Nominal
 * sesuai standar Bank Indonesia (EMVCo MPM Dynamic).
 *
 * @param {string} staticQris String QRIS statis
 * @param {number} amount Nominal transaksi
 * @returns {string} String QRIS dinamis lengkap dengan Tag 54 dan CRC16 baru
 */
export function convertToDynamicQris(staticQris, amount) {
    let clean = (staticQris || DEFAULT_STATIC_QRIS).trim();

    // 1. Potong checksum lama (Tag 63)
    const crcIdx = clean.indexOf('6304');
    let base = crcIdx !== -1 ? clean.substring(0, crcIdx) : clean;

    // 2. Ubah Tag 01 (Point of Initiation Method): '11' (Statis) -> '12' (Dinamis)
    base = base.replace('010211', '010212');

    // 3. Format Tag 54 (Transaction Amount): Tag 54 + 2-digit length + nominal integer
    const amtInt = Math.max(1, Math.round(Number(amount) || 0));
    const amtStr = amtInt.toString();
    const amtTag = '54' + amtStr.length.toString().padStart(2, '0') + amtStr;

    // Cek jika Tag 54 sudah ada sebelumnya
    const tag54Regex = /54(\d{2})(\d+)/;
    if (tag54Regex.test(base)) {
        base = base.replace(tag54Regex, amtTag);
    } else {
        // Sisipkan setelah Tag 53 (5303360 = IDR Currency)
        if (base.includes('5303360')) {
            base = base.replace('5303360', '5303360' + amtTag);
        } else {
            base = base + amtTag;
        }
    }

    // 4. Tambahkan header Tag 63 (6304) dan hitung ulang CRC16
    base = base + '6304';
    const checksum = crc16(base);
    return base + checksum;
}

/**
 * Render QR Code ke elemen HTML Canvas.
 */
export async function renderQrisCanvas(canvasElement, rawQrisPayload, customWidth = 240) {
    if (!canvasElement) return;

    try {
        await QRCode.toCanvas(canvasElement, rawQrisPayload, {
            width: customWidth,
            margin: 2,
            color: {
                dark: '#121212',
                light: '#FFFFFF'
            },
            errorCorrectionLevel: 'M'
        });
    } catch (err) {
        console.error('Gagal render QR code QRIS:', err);
    }
}

/**
 * Broadcast sync ke Layar Pelanggan (Customer Display) via BroadcastChannel.
 */
let customerChannel = null;
try {
    customerChannel = new BroadcastChannel('sll_pos_customer_display');
} catch (e) {
    console.warn('BroadcastChannel tidak didukung di browser ini.');
}

export function broadcastToCustomerDisplay(payload) {
    if (customerChannel) {
        customerChannel.postMessage(payload);
    }
    // Fallback via localStorage event untuk browser lawas
    try {
        localStorage.setItem('sll_pos_customer_display_event', JSON.stringify({
            ...payload,
            _timestamp: Date.now()
        }));
    } catch (e) {}
}

window.QRIS_HELPER = {
    crc16,
    DEFAULT_STATIC_QRIS,
    getBaseQrisPayload,
    setCustomQrisPayload,
    convertToDynamicQris,
    renderQrisCanvas,
    broadcastToCustomerDisplay
};
