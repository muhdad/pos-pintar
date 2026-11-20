/**
 * Thermal Printer Driver (Web Bluetooth ESC/POS + Browser Print Fallback)
 * Standar kompatibel penuh untuk printer struk kasir 58mm & 80mm.
 * Mendukung merk populer di Indonesia: Panda, Xprinter, VSC, EPPOS, GOOJPRT, Iware, Sunmi, Epson, dll.
 */
export class ThermalPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.characteristic = null;
        this.isConnected = false;
        this.paperWidth = 58; // Default 58mm (32 karakter/baris), opsi 80mm (42-48 karakter/baris)
    }

    /**
     * Set ukuran kertas struk (58 atau 80)
     */
    setPaperWidth(width) {
        this.paperWidth = width === 80 ? 80 : 58;
        if (this.paperWidth === 80) {
            document.body.classList.add('paper-80mm');
        } else {
            document.body.classList.remove('paper-80mm');
        }
    }

    /**
     * Hubungkan ke printer via Web Bluetooth API (SPP / Serial Port over BLE)
     */
    async connectBluetooth() {
        if (!navigator.bluetooth) {
            alert('Browser ini belum mendukung Web Bluetooth. Gunakan Google Chrome di Android / PC, atau cetak langsung via USB / Driver.');
            return false;
        }

        try {
            this.device = await navigator.bluetooth.requestDevice({
                filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
            });

            this.device.addEventListener('gattserverdisconnected', () => {
                this.isConnected = false;
                this.characteristic = null;
                console.log('Bluetooth Printer terputus.');
            });

            this.server = await this.device.gatt.connect();
            const service = await this.server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
            this.characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
            this.isConnected = true;
            alert(`Printer Bluetooth terhubung: ${this.device.name || 'Thermal Printer'}`);
            return true;
        } catch (error) {
            console.error('Gagal menghubungkan Bluetooth Printer:', error);
            if (error.name !== 'NotFoundError') {
                alert('Gagal menghubungkan printer: ' + error.message);
            }
            return false;
        }
    }

    /**
     * Cetak Struk Transaksi
     * Jika Bluetooth terhubung, kirim byte ESC/POS mentah.
     * Jika tidak terhubung, otomatis panggil window.print() (Cetak via Browser / Driver USB / LAN).
     */
    async printReceipt(order) {
        if (!order) {
            console.warn('Tidak ada data pesanan untuk dicetak.');
            return false;
        }

        if (this.isConnected && this.characteristic) {
            return await this.printRawEscPos(order);
        } else {
            // Fallback cerdas: Cetak via print dialog browser (cocok untuk USB, WiFi, RawBT, atau driver thermal)
            return this.printBrowser(order);
        }
    }

    /**
     * Cetak via Browser Print Dialog (58mm / 80mm CSS @media print)
     */
    printBrowser(order) {
        // Berikan waktu sejenak agar DOM #printable-receipt ter-render dengan Alpine.js
        setTimeout(() => {
            window.print();
        }, 100);
        return true;
    }

    /**
     * Kirim Binary ESC/POS Langsung ke Bluetooth Thermal Printer
     */
    async printRawEscPos(order) {
        const encoder = new TextEncoder();
        const ESC = 0x1b;
        const GS = 0x1d;
        const maxCols = this.paperWidth === 80 ? 42 : 32; // 32 kolom untuk 58mm standar

        let buffer = [];

        // 1. Inisialisasi printer
        buffer.push(ESC, 0x40);

        // 2. Header Toko: SOTO LAMONGAN LESTARI (Tengah, Huruf Besar / Bold)
        buffer.push(ESC, 0x61, 0x01); // Center align
        buffer.push(ESC, 0x45, 0x01); // Bold on
        buffer.push(GS, 0x21, 0x11);  // Double width & double height
        buffer.push(...encoder.encode('SOTO LAMONGAN LESTARI\n'));
        buffer.push(GS, 0x21, 0x00);  // Normal size
        buffer.push(ESC, 0x45, 0x00); // Bold off

        buffer.push(...encoder.encode('Cabang Utama\n'));
        buffer.push(...encoder.encode('Telp: 0852-1325-4219\n'));
        buffer.push(...encoder.encode('='.repeat(maxCols) + '\n'));

        // 3. Metadata Transaksi (Rata Kiri)
        buffer.push(ESC, 0x61, 0x00); // Left align
        const now = order.created_at ? new Date(order.created_at) : new Date();
        const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        buffer.push(...encoder.encode(`No. Struk : ${order.order_number}\n`));
        buffer.push(...encoder.encode(`Waktu     : ${dateStr} ${timeStr}\n`));
        if (order.customer_name) {
            buffer.push(...encoder.encode(`Pelanggan : ${order.customer_name}\n`));
        }
        buffer.push(...encoder.encode('-'.repeat(maxCols) + '\n'));

        // 4. Daftar Item Menu
        const items = order.items || [];
        for (const item of items) {
            // Baris 1: Nama menu
            buffer.push(ESC, 0x45, 0x01); // Bold
            buffer.push(...encoder.encode(`${item.name}\n`));
            buffer.push(ESC, 0x45, 0x00); // Bold off

            // Baris 2: Qty x Harga di kiri, Subtotal di kanan
            const qtyPrice = `${item.quantity} x ${Number(item.unit_price).toLocaleString('id-ID')}`;
            const subtotal = `${Number(item.subtotal).toLocaleString('id-ID')}`;
            const spaces = Math.max(1, maxCols - qtyPrice.length - subtotal.length);
            buffer.push(...encoder.encode(`${qtyPrice}${' '.repeat(spaces)}${subtotal}\n`));

            // Baris 3: Catatan pesanan jika ada
            if (item.notes) {
                buffer.push(...encoder.encode(`  * ${item.notes}\n`));
            }
        }

        buffer.push(...encoder.encode('-'.repeat(maxCols) + '\n'));

        // 5. Rincian Pembayaran
        const formatRow = (label, valStr) => {
            const sp = Math.max(1, maxCols - label.length - valStr.length);
            return `${label}${' '.repeat(sp)}${valStr}\n`;
        };

        buffer.push(...encoder.encode(formatRow('Subtotal', Number(order.total_amount || 0).toLocaleString('id-ID'))));
        if (order.discount_amount > 0) {
            buffer.push(...encoder.encode(formatRow('Diskon', '-' + Number(order.discount_amount).toLocaleString('id-ID'))));
        }
        buffer.push(...encoder.encode(formatRow('PPN (11%)', Number(order.tax_amount || 0).toLocaleString('id-ID'))));

        buffer.push(ESC, 0x45, 0x01); // Bold on
        buffer.push(...encoder.encode(formatRow('TOTAL', 'Rp' + Number(order.final_amount || 0).toLocaleString('id-ID'))));
        buffer.push(ESC, 0x45, 0x00); // Bold off

        const paymentMethod = order.payment?.method || 'CASH';
        buffer.push(...encoder.encode(formatRow('Metode', paymentMethod)));

        if (paymentMethod === 'CASH') {
            const cashVal = order.cashGiven || order.final_amount || 0;
            const changeVal = order.changeDue || 0;
            buffer.push(...encoder.encode(formatRow('Bayar Tunai', Number(cashVal).toLocaleString('id-ID'))));
            buffer.push(...encoder.encode(formatRow('Kembalian', Number(changeVal).toLocaleString('id-ID'))));
        }

        // 6. Footer (Rata Tengah)
        buffer.push(ESC, 0x61, 0x01); // Center align
        buffer.push(...encoder.encode('='.repeat(maxCols) + '\n'));
        buffer.push(...encoder.encode('Rasa Mantap, Harga Bersahabat\n'));
        buffer.push(...encoder.encode('Terima Kasih Atas Kunjungan Anda!\n\n\n\n'));

        // 7. Cut Paper Command (GS V 65 0)
        buffer.push(GS, 0x56, 0x41, 0x10);

        try {
            // Kirim per chunk (512 bytes) untuk kestabilan buffer printer BLE
            const uint8 = new Uint8Array(buffer);
            const CHUNK_SIZE = 512;
            for (let i = 0; i < uint8.length; i += CHUNK_SIZE) {
                const chunk = uint8.slice(i, i + CHUNK_SIZE);
                await this.characteristic.writeValue(chunk);
            }
            return true;
        } catch (err) {
            console.error('Gagal menulis data ke Bluetooth Printer:', err);
            // Fallback ke browser print
            return this.printBrowser(order);
        }
    }
}

// Inisialisasi ke global window
window.thermalPrinter = new ThermalPrinter();
