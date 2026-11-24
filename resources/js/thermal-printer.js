/**
 * Thermal Printer Driver (Web Bluetooth & WebUSB ESC/POS + Direct Print)
 * Standar kompatibel penuh untuk printer struk kasir 58mm & 80mm di Indonesia.
 * Mendukung merk: Panda, Xprinter, VSC, EPPOS, GOOJPRT, Iware, Sunmi, Epson, dll.
 * 
 * Mode Cetak:
 * 1. Direct ESC/POS via Web Bluetooth (0ms, LANGSUNG KELUAR KERTAS TANPA DIALOG BROWSER).
 * 2. Direct ESC/POS via WebUSB (Kabel USB OTG langsung ke printer kasir).
 * 3. Silent / Kiosk Print Fallback (--kiosk-printing di browser untuk auto-print tanpa dialog).
 */
export class ThermalPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.characteristic = null;
        this.usbDevice = null;
        this.usbEndpoint = null;
        this.isConnected = false;
        this.connectionType = null; // 'bluetooth' | 'usb'
        this.paperWidth = 58; // Default 58mm (32 kolom), opsi 80mm (42 kolom)
        this.autoPrintEnabled = localStorage.getItem('POS_AUTO_PRINT') !== 'false';
        this.printerName = localStorage.getItem('POS_PRINTER_NAME') || '';
        this.useRawBt = localStorage.getItem('POS_USE_RAWBT') === 'true';
    }

    /**
     * Deteksi apakah perangkat menggunakan OS Android
     */
    isAndroid() {
        return typeof navigator !== 'undefined' && /Android/i.test(navigator.userAgent);
    }

    /**
     * Aktifkan / Matikan mode RawBT khusus Android
     */
    toggleRawBt(val) {
        this.useRawBt = typeof val === 'boolean' ? val : !this.useRawBt;
        localStorage.setItem('POS_USE_RAWBT', this.useRawBt ? 'true' : 'false');
        return this.useRawBt;
    }

    /**
     * Konversi Uint8Array ESC/POS ke Base64 String
     */
    uint8ToBase64(uint8Array) {
        let binary = '';
        const len = uint8Array.byteLength;
        for (let i = 0; i < len; i++) {
            binary += String.fromCharCode(uint8Array[i]);
        }
        return window.btoa(binary);
    }

    /**
     * Cetak langsung via Aplikasi RawBT di Android.
     * Solusi #1 paling stabil & kompatibel untuk SEMUA printer Bluetooth di Indonesia (Bluetooth Classic 2.0/3.0 SPP maupun BLE).
     */
    printRawBt(order) {
        if (!order) return false;
        try {
            const bytes = this.buildEscPosBytes(order);
            const base64Data = this.uint8ToBase64(bytes);
            const rawBtUrl = `intent:base64,${base64Data}#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;`;
            window.location.href = rawBtUrl;
            return true;
        } catch (err) {
            console.error('Gagal cetak via RawBT:', err);
            return this.printBrowser(order);
        }
    }

    /**
     * Hubungkan ke printer via Web Bluetooth (BLE ESC/POS)
     * Langsung cetak tanpa settingan browser sama sekali.
     */
    async connectBluetooth() {
        if (!navigator.bluetooth) {
            const isHttp = window.location.protocol === 'http:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
            if (isHttp) {
                alert('⚠️ Web Bluetooth dibatasi Google Chrome pada koneksi HTTP (bukan HTTPS).\n\nSolusi Cepat Android:\n1. Aktifkan Mode RawBT di menu printer (Gunakan aplikasi RawBT gratis di Play Store — bekerja untuk SEMUA merk printer Bluetooth/USB),\nATAU\n2. Buka tab baru di Chrome tablet: chrome://flags -> cari "unsafely-treat-insecure-origin-as-secure" -> tambahkan ' + window.location.origin + ' -> Relaunch Chrome.');
            } else {
                alert('Browser ini belum mendukung Web Bluetooth. Gunakan Google Chrome versi terbaru.');
            }
            return false;
        }

        const knownServices = [
            '000018f0-0000-1000-8000-00805f9b34fb', // Standar thermal printer BLE
            '49535343-fe7d-4ae5-8fa9-9fafd205e455', // ISSC BLE serial (Panda, EPPOS, VSC)
            'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
            '0000e0ff-0000-1000-8000-00805f9b34fb',
            '0000ff00-0000-1000-8000-00805f9b34fb',
        ];

        try {
            this.device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: knownServices
            });

            this.device.addEventListener('gattserverdisconnected', () => {
                this.isConnected = false;
                this.characteristic = null;
                this.connectionType = null;
                console.log('Bluetooth Printer terputus.');
            });

            this.server = await this.device.gatt.connect();

            // Cari characteristic yang mendukung 'write' atau 'writeWithoutResponse'
            let foundChar = null;
            for (const sUuid of knownServices) {
                try {
                    const service = await this.server.getPrimaryService(sUuid);
                    const characteristics = await service.getCharacteristics();
                    for (const c of characteristics) {
                        if (c.properties.write || c.properties.writeWithoutResponse) {
                            foundChar = c;
                            break;
                        }
                    }
                    if (foundChar) break;
                } catch (e) {
                    // Coba service berikutnya
                }
            }

            if (!foundChar) {
                // Fallback: telusuri semua primary services
                const services = await this.server.getPrimaryServices();
                for (const service of services) {
                    try {
                        const characteristics = await service.getCharacteristics();
                        for (const c of characteristics) {
                            if (c.properties.write || c.properties.writeWithoutResponse) {
                                foundChar = c;
                                break;
                            }
                        }
                        if (foundChar) break;
                    } catch (e) {}
                }
            }

            if (!foundChar) {
                throw new Error('Tidak menemukan port tulis data (write characteristic) pada printer ini.');
            }

            this.characteristic = foundChar;
            this.isConnected = true;
            this.connectionType = 'bluetooth';
            this.printerName = this.device.name || 'Thermal Bluetooth Printer';
            localStorage.setItem('POS_PRINTER_NAME', this.printerName);

            alert(`✓ Berhasil Terhubung ke ${this.printerName}!\nSetiap transaksi akan otomatis langsung tercetak ke printer ini tanpa buka dialog browser.`);
            return true;
        } catch (error) {
            console.error('Gagal menghubungkan Bluetooth Printer:', error);
            if (error.name === 'NotFoundError') {
                alert('⚠️ Printer tidak ditemukan atau dialog pemilihan dibatalkan.\n\nPenyebab Umum di Tablet Android:\n1. GPS / Lokasi tablet MATI (Wajib AKTIF di Android agar Chrome bisa scan Bluetooth).\n2. Printer kasir Anda bertipe Bluetooth Classic 2.0/3.0 SPP (Chrome Web Bluetooth hanya mendeteksi tipe BLE).\n\n👉 Solusi Terbaik: Aktifkan "Mode RawBT" di menu printer untuk langsung mencetak via aplikasi RawBT.');
            } else {
                alert('Gagal menghubungkan printer: ' + error.message);
            }
            return false;
        }
    }

    /**
     * Hubungkan ke printer via WebUSB (Kabel USB OTG ke Tablet / PC)
     */
    async connectUsb() {
        if (!navigator.usb) {
            alert('Browser ini belum mendukung WebUSB. Gunakan Google Chrome versi terbaru.');
            return false;
        }

        try {
            this.usbDevice = await navigator.usb.requestDevice({ filters: [] });
            await this.usbDevice.open();
            await this.usbDevice.selectConfiguration(1);

            // Cari USB Interface & Endpoint OUT (bulk transfer)
            let interfaceNum = 0;
            let endpointOut = null;

            for (const iface of this.usbDevice.configuration.interfaces) {
                for (const alt of iface.alternates) {
                    for (const ep of alt.endpoints) {
                        if (ep.direction === 'out' && ep.type === 'bulk') {
                            interfaceNum = iface.interfaceNumber;
                            endpointOut = ep.endpointNumber;
                            break;
                        }
                    }
                    if (endpointOut) break;
                }
                if (endpointOut) break;
            }

            if (!endpointOut) {
                throw new Error('Tidak menemukan USB OUT endpoint pada perangkat ini.');
            }

            await this.usbDevice.claimInterface(interfaceNum);
            this.usbEndpoint = endpointOut;
            this.isConnected = true;
            this.connectionType = 'usb';
            this.printerName = this.usbDevice.productName || 'Thermal USB Printer';
            localStorage.setItem('POS_PRINTER_NAME', this.printerName);

            alert(`✓ Berhasil Terhubung ke ${this.printerName} via USB!\nCetak struk akan langsung dikirim ke printer tanpa dialog browser.`);
            return true;
        } catch (error) {
            console.error('Gagal menghubungkan USB Printer:', error);
            if (error.name !== 'NotFoundError') {
                alert('Gagal menghubungkan USB: ' + error.message);
            }
            return false;
        }
    }

    /**
     * Cetak Struk Transaksi Utama
     * 1. Mode RawBT (Android): Kirim langsung via Android Intent ke aplikasi RawBT (0ms).
     * 2. Direct Web Bluetooth / WebUSB: Kirim byte ESC/POS langsung (0ms tanpa dialog browser).
     * 3. Fallback: Panggil window.print() (Cetak instan / silent print jika pakai flag --kiosk-printing).
     */
    async printReceipt(order) {
        if (!order) {
            console.warn('Tidak ada data order untuk dicetak.');
            return false;
        }

        document.body.classList.remove('print-kot');

        // Prioritas 1: Mode RawBT (Khusus Android - Mendukung Bluetooth Classic SPP & BLE)
        if (this.useRawBt || localStorage.getItem('POS_USE_RAWBT') === 'true') {
            return this.printRawBt(order);
        }

        // Prioritas 2: Direct Web Bluetooth
        if (this.isConnected && this.connectionType === 'bluetooth' && this.characteristic) {
            return await this.sendEscPosBluetooth(order);
        } else if (this.isConnected && this.connectionType === 'usb' && this.usbDevice) {
            return await this.sendEscPosUsb(order);
        } else {
            // Browser print fallback
            return this.printBrowser(order);
        }
    }

    /**
     * Cetak Tiket Dapur (KOT)
     */
    async printKitchenTicket(order) {
        document.body.classList.add('print-kot');
        setTimeout(() => {
            window.print();
            setTimeout(() => {
                document.body.classList.remove('print-kot');
            }, 600);
        }, 100);
        return true;
    }

    /**
     * Fallback Cetak via Browser (Dengan CSS print 58mm/80mm)
     */
    printBrowser(order) {
        setTimeout(() => {
            window.print();
        }, 80);
        return true;
    }

    /**
     * Generate Binary ESC/POS Byte Buffer untuk Struk Kasir
     */
    buildEscPosBytes(order) {
        const encoder = new TextEncoder();
        const ESC = 0x1b;
        const GS = 0x1d;
        const maxCols = this.paperWidth === 80 ? 42 : 32;

        let buffer = [];

        // Inisialisasi printer (ESC @)
        buffer.push(ESC, 0x40);

        // Header Toko (Rata Tengah & Bold)
        buffer.push(ESC, 0x61, 0x01); // Center
        buffer.push(ESC, 0x45, 0x01); // Bold on
        buffer.push(GS, 0x21, 0x11);  // Double size
        buffer.push(...encoder.encode('SOTO LAMONGAN LESTARI\n'));
        buffer.push(GS, 0x21, 0x00);  // Normal size
        buffer.push(ESC, 0x45, 0x00); // Bold off

        buffer.push(...encoder.encode('Cabang Utama\n'));
        buffer.push(...encoder.encode('Telp: 0852-1325-4219\n'));
        buffer.push(...encoder.encode('='.repeat(maxCols) + '\n'));

        // Metadata Order (Rata Kiri)
        buffer.push(ESC, 0x61, 0x00);
        const now = order.created_at ? new Date(order.created_at) : new Date();
        const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        buffer.push(...encoder.encode(`No. Struk : ${order.order_number}\n`));
        buffer.push(...encoder.encode(`Waktu     : ${dateStr} ${timeStr}\n`));
        if (order.customer_name) {
            buffer.push(...encoder.encode(`Pelanggan : ${order.customer_name}\n`));
        }
        buffer.push(...encoder.encode('-'.repeat(maxCols) + '\n'));

        // Items List
        const items = order.items || [];
        for (const item of items) {
            const name = item.product?.name || item.name || 'Menu';
            buffer.push(ESC, 0x45, 0x01);
            buffer.push(...encoder.encode(`${name}\n`));
            buffer.push(ESC, 0x45, 0x00);

            const qtyPrice = `${item.quantity} x ${Number(item.unit_price).toLocaleString('id-ID')}`;
            const subtotal = `${Number(item.subtotal).toLocaleString('id-ID')}`;
            const spaces = Math.max(1, maxCols - qtyPrice.length - subtotal.length);
            buffer.push(...encoder.encode(`${qtyPrice}${' '.repeat(spaces)}${subtotal}\n`));

            if (item.notes) {
                buffer.push(...encoder.encode(`  * ${item.notes}\n`));
            }
        }

        buffer.push(...encoder.encode('-'.repeat(maxCols) + '\n'));

        const formatRow = (label, valStr) => {
            const sp = Math.max(1, maxCols - label.length - valStr.length);
            return `${label}${' '.repeat(sp)}${valStr}\n`;
        };

        buffer.push(...encoder.encode(formatRow('Subtotal', Number(order.total_amount || 0).toLocaleString('id-ID'))));
        if (Number(order.discount_amount || 0) > 0) {
            buffer.push(...encoder.encode(formatRow('Diskon', '-' + Number(order.discount_amount).toLocaleString('id-ID'))));
        }
        if (Number(order.tax_amount || 0) > 0) {
            buffer.push(...encoder.encode(formatRow('Pajak', Number(order.tax_amount).toLocaleString('id-ID'))));
        }

        buffer.push(ESC, 0x45, 0x01);
        buffer.push(...encoder.encode(formatRow('TOTAL', 'Rp' + Number(order.final_amount || 0).toLocaleString('id-ID'))));
        buffer.push(ESC, 0x45, 0x00);

        const paymentMethod = order.payment?.method || (order.payments && order.payments[0]?.method) || 'CASH';
        buffer.push(...encoder.encode(formatRow('Metode', paymentMethod)));

        if (paymentMethod === 'CASH') {
            const cashVal = order.cashGiven || order.final_amount || 0;
            const changeVal = order.changeDue || 0;
            buffer.push(...encoder.encode(formatRow('Bayar Tunai', Number(cashVal).toLocaleString('id-ID'))));
            buffer.push(...encoder.encode(formatRow('Kembalian', Number(changeVal).toLocaleString('id-ID'))));
        }

        // Footer (Center)
        buffer.push(ESC, 0x61, 0x01);
        buffer.push(...encoder.encode('='.repeat(maxCols) + '\n'));
        buffer.push(...encoder.encode('Rasa Mantap, Harga Bersahabat\n'));
        buffer.push(...encoder.encode('Terima Kasih Atas Kunjungan Anda!\n\n\n\n'));

        // Cut paper command
        buffer.push(GS, 0x56, 0x41, 0x10);

        return new Uint8Array(buffer);
    }

    /**
     * Kirim ESC/POS via Bluetooth
     */
    async sendEscPosBluetooth(order) {
        try {
            const data = this.buildEscPosBytes(order);
            const CHUNK_SIZE = 512;
            for (let i = 0; i < data.length; i += CHUNK_SIZE) {
                const chunk = data.slice(i, i + CHUNK_SIZE);
                if (this.characteristic.writeValueWithoutResponse) {
                    await this.characteristic.writeValueWithoutResponse(chunk);
                } else {
                    await this.characteristic.writeValue(chunk);
                }
            }
            return true;
        } catch (err) {
            console.error('Gagal kirim via Bluetooth, fallback ke browser print:', err);
            return this.printBrowser(order);
        }
    }

    /**
     * Kirim ESC/POS via USB
     */
    async sendEscPosUsb(order) {
        try {
            const data = this.buildEscPosBytes(order);
            await this.usbDevice.transferOut(this.usbEndpoint, data);
            return true;
        } catch (err) {
            console.error('Gagal kirim via USB, fallback ke browser print:', err);
            return this.printBrowser(order);
        }
    }
}

// Inisialisasi instance tunggal di window
window.thermalPrinter = new ThermalPrinter();
