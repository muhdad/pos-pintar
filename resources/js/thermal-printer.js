/**
 * Thermal Printer Driver (Web Bluetooth & WebUSB ESC/POS)
 * Kompatibel dengan printer thermal 58mm & 80mm standar (Epson, Panda, Xprinter, Vmax, dll).
 */
export class ThermalPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.characteristic = null;
        this.isConnected = false;
    }

    /**
     * Hubungkan ke printer via Web Bluetooth API.
     */
    async connectBluetooth() {
        if (!navigator.bluetooth) {
            alert('Browser ini tidak mendukung Web Bluetooth API. Gunakan Chrome di Android/PC.');
            return false;
        }

        try {
            this.device = await navigator.bluetooth.requestDevice({
                filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
            });

            this.server = await this.device.gatt.connect();
            const service = await this.server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
            this.characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
            this.isConnected = true;
            return true;
        } catch (error) {
            console.error('Gagal menghubungkan Bluetooth Printer:', error);
            return false;
        }
    }

    /**
     * Cetak Struk Transaksi Standar ESC/POS
     */
    async printReceipt(order) {
        if (!this.isConnected || !this.characteristic) {
            console.warn('Printer belum terhubung.');
            return false;
        }

        const encoder = new TextEncoder();
        const ESC = 0x1b;
        const GS = 0x1d;

        let buffer = [];

        // Reset / Inisialisasi printer
        buffer.push(ESC, 0x40);

        // Header: Nama Toko & Rata Tengah
        buffer.push(ESC, 0x61, 0x01); // Center
        buffer.push(...encoder.encode('POS PINTAR\n'));
        buffer.push(...encoder.encode('================================\n'));

        // Metadata Order (Rata Kiri)
        buffer.push(ESC, 0x61, 0x00); // Left
        buffer.push(...encoder.encode(`No    : ${order.order_number}\n`));
        buffer.push(...encoder.encode(`Tgl   : ${new Date(order.created_at).toLocaleString('id-ID')}\n`));
        buffer.push(...encoder.encode('--------------------------------\n'));

        // Items
        for (const item of order.items) {
            buffer.push(...encoder.encode(`${item.name}\n`));
            const qtyPrice = `${item.quantity} x ${item.unit_price.toLocaleString('id-ID')}`;
            const subtotal = `${item.subtotal.toLocaleString('id-ID')}`;
            const spaces = Math.max(1, 32 - qtyPrice.length - subtotal.length);
            buffer.push(...encoder.encode(`${qtyPrice}${' '.repeat(spaces)}${subtotal}\n`));
        }

        buffer.push(...encoder.encode('--------------------------------\n'));

        // Total & Pembayaran
        const totalLine = `TOTAL:    Rp${order.final_amount.toLocaleString('id-ID')}\n`;
        const payLine = `Metode:   ${order.payment?.method || 'CASH'}\n`;
        buffer.push(...encoder.encode(totalLine));
        buffer.push(...encoder.encode(payLine));

        // Footer
        buffer.push(ESC, 0x61, 0x01); // Center
        buffer.push(...encoder.encode('================================\n'));
        buffer.push(...encoder.encode('Terima Kasih Atas Kunjungan Anda\n\n\n\n'));

        // Cut Paper
        buffer.push(GS, 0x56, 0x41, 0x10);

        const uint8 = new Uint8Array(buffer);
        await this.characteristic.writeValue(uint8);
        return true;
    }
}

// Inisialisasi ke global window
window.thermalPrinter = new ThermalPrinter();

