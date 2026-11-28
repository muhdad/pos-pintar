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
        if (!order) return false;

        // Prioritas 1: Mode RawBT (Android)
        if (this.useRawBt || localStorage.getItem('POS_USE_RAWBT') === 'true') {
            try {
                const bytes = this.buildKotEscPosBytes(order);
                const base64Data = this.uint8ToBase64(bytes);
                window.location.href = `intent:base64,${base64Data}#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;`;
                return true;
            } catch (e) {
                console.error('Gagal cetak KOT via RawBT:', e);
            }
        }

        // Prioritas 2: Direct Web Bluetooth
        if (this.isConnected && this.connectionType === 'bluetooth' && this.characteristic) {
            try {
                const data = this.buildKotEscPosBytes(order);
                const CHUNK_SIZE = 20;
                for (let i = 0; i < data.length; i += CHUNK_SIZE) {
                    const chunk = data.slice(i, i + CHUNK_SIZE);
                    if (this.characteristic.writeValueWithoutResponse) {
                        await this.characteristic.writeValueWithoutResponse(chunk);
                    } else {
                        await this.characteristic.writeValue(chunk);
                    }
                    await new Promise(r => setTimeout(r, 15));
                }
                return true;
            } catch (e) {
                console.error('Gagal cetak KOT via Bluetooth:', e);
            }
        }

        // Prioritas 3: Direct WebUSB
        if (this.isConnected && this.connectionType === 'usb' && this.usbDevice) {
            try {
                const data = this.buildKotEscPosBytes(order);
                await this.usbDevice.transferOut(this.usbEndpoint, data);
                return true;
            } catch (e) {
                console.error('Gagal cetak KOT via USB:', e);
            }
        }

        // Prioritas 4: Fallback Browser Print dengan Iframe Terisolasi 58mm
        return this.printBrowser(order, true);
    }

    /**
     * Generate Binary ESC/POS Byte Buffer untuk Tiket Dapur (KOT)
     */
    buildKotEscPosBytes(order) {
        const encoder = new TextEncoder();
        const ESC = 0x1b;
        const GS = 0x1d;
        const maxCols = this.paperWidth === 80 ? 42 : 32;

        let buffer = [];
        buffer.push(ESC, 0x40); // Reset (standar ESC/POS universal)

        // Header KOT (standar ESC E Bold + GS ! Double Size tanpa perintah non-standar yang memicu karakter aneh di RawBT)
        buffer.push(ESC, 0x61, 0x01); // Center
        buffer.push(ESC, 0x45, 0x01); // Bold on
        buffer.push(GS, 0x21, 0x11);  // Double size (58mm max 16 chars/baris)
        if (maxCols < 42) {
            buffer.push(...encoder.encode('TIKET DAPUR\n'));
            buffer.push(...encoder.encode('(KOT)\n'));
        } else {
            buffer.push(...encoder.encode('TIKET DAPUR (KOT)\n'));
        }
        buffer.push(GS, 0x21, 0x00);  // Normal size
        buffer.push(ESC, 0x45, 0x00); // Bold off

        buffer.push(...encoder.encode('='.repeat(maxCols) + '\n'));
        buffer.push(ESC, 0x61, 0x00); // Left

        const now = order.created_at ? new Date(order.created_at) : new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        buffer.push(...encoder.encode(`TIPE  : ${(order.order_type || 'DINE_IN').toUpperCase()}\n`));
        buffer.push(...encoder.encode(`MEJA  : ${order.table_name || order.customer_name || 'Dine-In'}\n`));
        buffer.push(...encoder.encode(`WAKTU : ${timeStr}\n`));
        buffer.push(...encoder.encode('-'.repeat(maxCols) + '\n'));

        // Items List (Tanpa harga, qty tebal)
        const items = order.items || [];
        for (const item of items) {
            const name = item.product?.name || item.name || 'Menu';
            buffer.push(ESC, 0x45, 0x01);
            buffer.push(...encoder.encode(`[ ] ${item.quantity}x ${name}\n`));
            buffer.push(ESC, 0x45, 0x00);
            if (item.notes) {
                buffer.push(...encoder.encode(`    > CATATAN: ${item.notes}\n`));
            }
        }

        buffer.push(...encoder.encode('='.repeat(maxCols) + '\n'));
        buffer.push(ESC, 0x61, 0x01); // Center
        buffer.push(ESC, 0x45, 0x01);
        buffer.push(...encoder.encode('*** SEGERA DISAJIKAN PANAS ***\n\n\n\n'));
        buffer.push(ESC, 0x45, 0x00);

        // Feed paper 5 lines past tear bar
        buffer.push(ESC, 0x64, 0x05);
        return new Uint8Array(buffer);
    }

    /**
     * Generate HTML struk 58mm mandiri (self-contained) tanpa bergantung CSS luar
     */
    generateReceiptHtml(order, isKot = false) {
        if (!order) return '';

        if (isKot) {
            const items = order.items || [];
            const now = order.created_at ? new Date(order.created_at) : new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            let itemsHtml = '';
            for (const item of items) {
                const name = item.product?.name || item.name || 'Menu';
                const notes = item.notes ? `<div style="font-size:9px; font-style:italic; padding-left:12px;">➔ Catatan: ${item.notes}</div>` : '';
                itemsHtml += `
                    <div style="padding: 2px 0;">
                        <div style="font-weight:700; font-size:11px;">[ ] ${item.quantity}x ${name}</div>
                        ${notes}
                    </div>
                `;
            }

            return `
                <div style="text-align:center; padding-bottom:5px; border-bottom:1.5px dashed #000;">
                    <h2 style="font-size:15px; font-weight:900; margin:0; text-transform:uppercase;">TIKET DAPUR (KOT)</h2>
                    <p style="font-size:12px; font-weight:800; margin:2px 0 0 0;">${order.table_name || order.customer_name || 'Dine-In'}</p>
                </div>
                <div style="padding:5px 0; border-bottom:1.5px dashed #000; font-size:11.5px; font-weight:700; line-height:1.35;">
                    <div style="display:flex; justify-content:space-between; gap:6px;"><span>Tipe:</span><strong style="text-transform:uppercase; font-weight:800;">${order.order_type || 'DINE_IN'}</strong></div>
                    <div style="display:flex; justify-content:space-between; gap:6px;"><span>Waktu:</span><span>${timeStr}</span></div>
                </div>
                <div style="padding:5px 0; border-bottom:1.5px dashed #000;">
                    ${itemsHtml}
                </div>
                <div style="text-align:center; padding-top:6px; font-size:11.5px; font-weight:800;">
                    *** SEGERA DISAJIKAN PANAS ***
                </div>
            `;
        }

        const items = order.items || [];
        const now = order.created_at ? new Date(order.created_at) : new Date();
        const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        let itemsHtml = '';
        for (const item of items) {
            const name = item.product?.name || item.name || 'Menu';
            const qty = Number(item.quantity || 1);
            const unitPrice = item.unit_price != null ? Number(item.unit_price) : 0;
            const subtotalVal = item.subtotal != null ? Number(item.subtotal) : (qty * unitPrice);
            const notes = item.notes ? `<div style="font-size:10.5px; font-weight:700; font-style:italic; padding-left:8px;">* ${item.notes}</div>` : '';

            itemsHtml += `
                <div style="padding:2.5px 0;">
                    <div style="font-weight:800; font-size:12px;">${name}</div>
                    <div style="display:flex; justify-content:space-between; gap:6px; font-size:11.5px; font-weight:700; font-variant-numeric:tabular-nums;">
                        <span>${qty} x ${unitPrice.toLocaleString('id-ID')}</span>
                        <span style="font-weight:800;">${subtotalVal.toLocaleString('id-ID')}</span>
                    </div>
                    ${notes}
                </div>
            `;
        }

        const totalAmount = Number(order.total_amount || 0);
        const discountAmount = Number(order.discount_amount || 0);
        const taxAmount = Number(order.tax_amount || 0);
        const finalAmount = Number(order.final_amount || 0);
        const paymentMethod = order.payment?.method || (order.payments && order.payments[0]?.payment_method) || (order.payments && order.payments[0]?.method) || 'CASH';

        let discountRow = discountAmount > 0 ? `<div style="display:flex; justify-content:space-between; gap:6px;"><span>Diskon:</span><span>-${discountAmount.toLocaleString('id-ID')}</span></div>` : '';
        let taxRow = taxAmount > 0 ? `<div style="display:flex; justify-content:space-between; gap:6px;"><span>Pajak:</span><span>${taxAmount.toLocaleString('id-ID')}</span></div>` : '';

        let cashRow = '';
        if (paymentMethod === 'CASH') {
            const cashVal = order.cashGiven || (order.payments && order.payments[0]?.amount) || finalAmount || 0;
            const changeVal = order.changeDue || 0;
            cashRow = `
                <div style="display:flex; justify-content:space-between; gap:6px;"><span>Bayar Tunai:</span><span>${Number(cashVal).toLocaleString('id-ID')}</span></div>
                ${changeVal > 0 ? `<div style="display:flex; justify-content:space-between; gap:6px;"><span>Kembalian:</span><span>${Number(changeVal).toLocaleString('id-ID')}</span></div>` : ''}
            `;
        }

        return `
            <div style="text-align:center; padding-bottom:5px; border-bottom:1.5px dashed #000;">
                <h2 style="font-size:14px; font-weight:900; margin:0; text-transform:uppercase; letter-spacing:0.3px;">SOTO LAMONGAN LESTARI</h2>
                <p style="font-size:11.5px; font-weight:700; margin:2px 0 0 0;">Cabang Utama</p>
                <p style="font-size:11.5px; font-weight:700; margin:1px 0 0 0;">Telp: 0852-1325-4219</p>
            </div>
            <div style="padding:5px 0; border-bottom:1.5px dashed #000; font-size:11px; font-weight:700; line-height:1.35; font-variant-numeric:tabular-nums;">
                <div style="display:flex; justify-content:space-between; gap:6px;"><span style="white-space:nowrap;">No:</span><strong style="font-weight:800; text-align:right;">${order.order_number || ''}</strong></div>
                <div style="display:flex; justify-content:space-between; gap:6px;"><span style="white-space:nowrap;">Waktu:</span><span style="text-align:right;">${dateStr} ${timeStr}</span></div>
                ${order.customer_name ? `<div style="display:flex; justify-content:space-between; gap:6px;"><span style="white-space:nowrap;">Pelanggan:</span><span style="text-align:right;">${order.customer_name}</span></div>` : ''}
            </div>
            <div style="padding:5px 0; border-bottom:1.5px dashed #000;">
                ${itemsHtml}
            </div>
            <div style="padding:5px 0; border-bottom:1.5px dashed #000; font-size:11.5px; font-weight:700; line-height:1.4; font-variant-numeric:tabular-nums;">
                <div style="display:flex; justify-content:space-between; gap:6px;"><span>Subtotal:</span><span>${totalAmount.toLocaleString('id-ID')}</span></div>
                ${discountRow}
                ${taxRow}
                <div style="display:flex; justify-content:space-between; gap:6px; font-weight:900; font-size:13.5px; padding-top:4px; margin-top:2px; border-top:1.5px dotted #000;">
                    <span>TOTAL:</span>
                    <span>Rp${finalAmount.toLocaleString('id-ID')}</span>
                </div>
                <div style="display:flex; justify-content:space-between; gap:6px; padding-top:3px;"><span>Metode:</span><span>${paymentMethod}</span></div>
                ${cashRow}
            </div>
            <div style="text-align:center; padding-top:6px; font-size:11px; line-height:1.35; font-weight:700;">
                <div style="font-weight:800;">Terima Kasih Atas Kunjungannya!</div>
                <div>Rasa Mantap, Harga Bersahabat</div>
                <div style="font-size:10px; font-weight:700; padding-top:3px; color:#000000;">SLL Smart POS Cloud</div>
            </div>
        `;
    }

    /**
     * Fallback Cetak via Browser (Dengan Iframe Terisolasi 58mm)
     * Solusi 100% anti-terpotong untuk Google Chrome Android & RawBT Print Service:
     * Menghilangkan seluruh konflik height: 100vh / overflow: hidden dari layout utama.
     */
    printBrowser(order, isKot = false) {
        try {
            // Selalu utamakan generate HTML struk murni (fully rendered) dari data order
            let contentHtml = order ? this.generateReceiptHtml(order, isKot) : '';
            if (!contentHtml) {
                const targetEl = document.getElementById(isKot ? 'printable-kot' : 'printable-receipt');
                if (targetEl) contentHtml = targetEl.innerHTML;
            }

            if (!contentHtml) {
                setTimeout(() => window.print(), 150);
                return true;
            }

            // Update elemen DOM utama agar jika browser mencetak window utama, isinya sudah lengkap
            const domTarget = document.getElementById(isKot ? 'printable-kot' : 'printable-receipt');
            if (domTarget) {
                domTarget.innerHTML = contentHtml;
            }

            // Gunakan iframe terisolasi yang bersih dari parent CSS / height lock
            let iframe = document.getElementById('thermal-print-iframe');
            if (iframe) {
                iframe.remove();
            }

            iframe = document.createElement('iframe');
            iframe.id = 'thermal-print-iframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = 'none';
            iframe.style.opacity = '0';
            iframe.style.zIndex = '-9999';
            document.body.appendChild(iframe);

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>${isKot ? 'KOT' : 'Struk'}</title>
                    <style>
                        @page {
                            size: 58mm auto;
                            margin: 0mm !important;
                        }
                        * {
                            box-sizing: border-box !important;
                            color: #000000 !important;
                            border-color: #000000 !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                            -webkit-font-smoothing: none !important;
                            -webkit-text-stroke: 0.25px #000000 !important;
                            page-break-inside: auto !important;
                            break-inside: auto !important;
                        }
                        html, body {
                            width: 48mm !important;
                            max-width: 48mm !important;
                            margin: 0 auto !important;
                            padding: 1mm 1mm 6mm 1mm !important;
                            background: #ffffff !important;
                            color: #000000 !important;
                            font-family: 'Roboto', Arial, Helvetica, sans-serif !important;
                            font-size: 11.5px !important;
                            font-weight: 700 !important;
                            line-height: 1.3 !important;
                            height: auto !important;
                            min-height: 0 !important;
                            max-height: none !important;
                            overflow: visible !important;
                        }
                        div, p, span, h1, h2, h3 {
                            overflow: visible !important;
                            height: auto !important;
                            min-height: 0 !important;
                        }
                        .flex { display: flex !important; }
                        .justify-between { justify-content: space-between !important; }
                        .text-center { text-align: center !important; }
                        .font-bold { font-weight: 700 !important; }
                        .border-b { border-bottom: 1px dashed #000 !important; }
                        .border-t { border-top: 1px dashed #000 !important; }
                    </style>
                </head>
                <body>
                    <div style="width: 48mm; margin: 0 auto;">
                        ${contentHtml}
                    </div>
                </body>
                </html>
            `);
            doc.close();

            setTimeout(() => {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch (e) {
                    console.warn('Iframe print fallback to window.print():', e);
                    window.print();
                }
            }, 250);

            return true;
        } catch (err) {
            console.error('Error in printBrowser:', err);
            setTimeout(() => window.print(), 100);
            return false;
        }
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

        // Inisialisasi printer (ESC @ standar universal)
        // Catatan: Perintah proprietary seperti ESC 7 / ESC G dihapus karena RawBT tidak mengenalinya
        // dan justru mencetak karakter aneh ("áG") di awal struk.
        buffer.push(ESC, 0x40);

        // Header Toko (Rata Tengah & Bold)
        // Catatan: GS ! 0x11 (Double Size) hanya muat 16 karakter/baris pada kertas 58mm (32 kolom).
        // Pecah jadi 2 baris agar "SOTO LAMONGAN LESTARI" tidak terpotong di tengah kata ("L / ESTARI").
        buffer.push(ESC, 0x61, 0x01); // Center
        buffer.push(ESC, 0x45, 0x01); // Bold on
        buffer.push(GS, 0x21, 0x11);  // Double size
        if (maxCols < 42) {
            buffer.push(...encoder.encode('SOTO LAMONGAN\n'));
            buffer.push(...encoder.encode('LESTARI\n'));
        } else {
            buffer.push(...encoder.encode('SOTO LAMONGAN LESTARI\n'));
        }
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

            const qty = Number(item.quantity || 1);
            const unitPrice = item.unit_price != null ? Number(item.unit_price) : 0;
            const subtotalVal = item.subtotal != null ? Number(item.subtotal) : (qty * unitPrice);

            const qtyPrice = `${qty} x ${unitPrice.toLocaleString('id-ID')}`;
            const subtotal = `${subtotalVal.toLocaleString('id-ID')}`;
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

        const paymentMethod = order.payment?.method || (order.payments && order.payments[0]?.payment_method) || (order.payments && order.payments[0]?.method) || 'CASH';
        buffer.push(...encoder.encode(formatRow('Metode', paymentMethod)));

        if (paymentMethod === 'CASH') {
            const cashVal = order.cashGiven || (order.payments && order.payments[0]?.amount) || order.final_amount || 0;
            const changeVal = order.changeDue || 0;
            buffer.push(...encoder.encode(formatRow('Bayar Tunai', Number(cashVal).toLocaleString('id-ID'))));
            if (Number(changeVal) > 0) {
                buffer.push(...encoder.encode(formatRow('Kembalian', Number(changeVal).toLocaleString('id-ID'))));
            }
        }

        // Footer (Center) — Pastikan <= 32 karakter agar tanda '!' tidak turun ke baris baru
        buffer.push(ESC, 0x61, 0x01);
        buffer.push(...encoder.encode('='.repeat(maxCols) + '\n'));
        buffer.push(...encoder.encode('Rasa Mantap, Harga Bersahabat\n'));
        buffer.push(...encoder.encode('Terima Kasih Atas Kunjungannya!\n\n\n\n'));

        // Feed paper 5 lines past tear bar on 58mm manual tear printers (ESC d 5)
        buffer.push(ESC, 0x64, 0x05);

        return new Uint8Array(buffer);
    }

    /**
     * Kirim ESC/POS via Bluetooth
     * Standar BLE ATT MTU universal adalah 20 bytes payload (23 bytes MTU - 3 bytes header).
     * Printer thermal seperti PUTIAN POS / PT-58 / RPP02N akan langsung menolak (GATT error)
     * jika dikirim chunk > 20 bytes tanpa negosiasi MTU, sehingga hanya judul yang sempat tercetak.
     */
    async sendEscPosBluetooth(order) {
        try {
            const data = this.buildEscPosBytes(order);
            const CHUNK_SIZE = 20;
            for (let i = 0; i < data.length; i += CHUNK_SIZE) {
                const chunk = data.slice(i, i + CHUNK_SIZE);
                if (this.characteristic.writeValueWithoutResponse) {
                    await this.characteristic.writeValueWithoutResponse(chunk);
                } else {
                    await this.characteristic.writeValue(chunk);
                }
                await new Promise(r => setTimeout(r, 15));
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
