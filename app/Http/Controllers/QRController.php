<?php
// =============================================================
// 7NVENT - QR Scanner Controller (Phosphor Icons Edition)
// =============================================================
require_once __DIR__ . '/../../Auth.php';
require_once __DIR__ . '/../../Support/StockStatus.php';

class QRController {

    public function scanner(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')) {
            redirect('/dashboard', 'QR Scanner is restricted to Inventory and Housekeeping Managers.', 'error');
        }
        $user = Auth::user();
        $pageTitle = 'QR / Barcode Scanner';
        ob_start();

        // Prefer the real stored item_code (now settable from Add/Edit Item and
        // from the "Add as New Item" QR flow — see InventoryController::store()/
        // update()); fall back to a computed 7NV-XXXX code for older items that
        // were created before item_code existed, so previously printed/downloaded
        // QR labels for those items keep working.
        $inventory = db()->fetchAll(
            "SELECT i.*,
                    COALESCE(NULLIF(i.item_code, ''), CONCAT('7NV-', LPAD(i.item_id, 4, '0'))) AS item_code,
                    l.location_name AS loc
             FROM inventory_items i
             JOIN locations l ON i.location_id = l.location_id
             ORDER BY i.item_name"
        );

        // Data for the inline "Add Product" quick-add form (same lists
        // InventoryController::create() uses) — kept here so a code that
        // doesn't match anything can be registered without leaving this page.
        $qaLocations   = db()->fetchAll("SELECT * FROM locations WHERE status != 'Low Stock' ORDER BY location_name");
        $qaCategoryIcons = [
            'Toiletries' => 'ph-fill ph-drop',
            'F&B'        => 'ph-fill ph-bowl-food',
            'Linens'     => 'ph-fill ph-towel',
            'Cleaning'   => 'ph-fill ph-broom',
            'Minibar'    => 'ph-fill ph-wine',
        ];
        ?>

        <!-- Phosphor Icons CDN -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />


        <style>
        .qr-page-wrap { opacity:0; transform:translateY(14px); animation:qrUp 0.5s cubic-bezier(0.23,1,0.32,1) forwards; }
        @keyframes qrUp { to { opacity:1; transform:translateY(0); } }

        .qr-tab-btn {
            flex:1; padding:11px; text-align:center; border-radius:9px;
            font-size:13px; font-weight:600; cursor:pointer;
            transition:all 0.2s cubic-bezier(0.23,1,0.32,1);
        }
        .qr-tab-btn.active { background:#0096FF; color:#fff; box-shadow:0 4px 12px rgba(0,150,255,0.35); }
        .qr-tab-btn:not(.active) { color:var(--text-muted); }
        .qr-tab-btn:not(.active):hover { background:var(--bg-subtle); color:var(--text-primary); transform:translateY(-1px); }

        .scan-pulse { animation:sPulse 1.5s ease-in-out infinite; }
        @keyframes sPulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* ======= Camera scan overlay — laser sweep effect ======= */
        /* Bug fix: the old scan line referenced `animation:scanPulse` but no
           such @keyframes existed anywhere in this file — the "scan line" was
           actually a static, motionless gradient bar the whole time. This is
           a real animated sweep, plus breathing corner brackets and a subtle
           scan-grid texture, so the overlay looks alive instead of dead. */
        .scan-frame {
            animation: framePulse 2.4s ease-in-out infinite;
        }
        @keyframes framePulse {
            0%,100% { box-shadow: inset 0 0 20px rgba(0,150,255,0.10); border-color: rgba(0,150,255,.9); }
            50%     { box-shadow: inset 0 0 34px rgba(68,221,255,0.22); border-color: rgba(68,221,255,1); }
        }
        .scan-corner { animation: cornerGlow 2.4s ease-in-out infinite; }
        @keyframes cornerGlow {
            0%,100% { filter: drop-shadow(0 0 2px rgba(0,150,255,.6)); }
            50%     { filter: drop-shadow(0 0 9px rgba(68,221,255,.95)); }
        }
        .scan-laser {
            position:absolute; left:3%; right:3%; height:3px; border-radius:3px; top:2%;
            background:linear-gradient(90deg,transparent,#44ddff 20%,#0096FF 50%,#44ddff 80%,transparent);
            box-shadow:0 0 14px 2px rgba(68,221,255,.95), 0 0 34px 8px rgba(0,150,255,.55);
            animation: scanSweep 2.1s cubic-bezier(0.45,0,0.55,1) infinite;
        }
        .scan-laser::after {
            content:''; position:absolute; left:0; right:0; bottom:3px; height:46px;
            background:linear-gradient(to top, rgba(0,150,255,.22), rgba(0,150,255,0));
            pointer-events:none;
        }
        @keyframes scanSweep {
            0%   { top:2%;  opacity:0; }
            6%   { opacity:1; }
            50%  { top:92%; }
            94%  { opacity:1; }
            100% { top:96%; opacity:0; }
        }
        .scan-grid {
            position:absolute; inset:0; pointer-events:none; opacity:.35;
            background-image: repeating-linear-gradient(0deg, rgba(0,150,255,.10) 0 1px, transparent 1px 22px);
        }
        .scan-hint-text { animation: hintPulse 1.8s ease-in-out infinite; }
        @keyframes hintPulse { 0%,100%{opacity:.7} 50%{opacity:1} }

        /* QR card grid */
        .qr-gen-card {
            background:var(--bg-card); border-radius:14px; padding:16px; text-align:center;
            box-shadow:0 2px 10px var(--shadow-color); border:1px solid var(--border-subtle);
            opacity:0; transform:translateY(12px);
            animation:qrCardIn 0.4s ease forwards;
        }
        @keyframes qrCardIn { to { opacity:1; transform:translateY(0); } }
        @keyframes spinRing  { to { transform:rotate(360deg); } }

        </style>

        <div style="width:100%" class="qr-page-wrap">

        <!-- Tabs -->
        <div style="display:flex;background:var(--bg-card);border-radius:12px;padding:4px;margin-bottom:16px;box-shadow:0 2px 8px var(--shadow-color);gap:4px">
            <div class="qr-tab-btn active" onclick="switchTab('scan',this)"><i class="ph ph-camera" style="margin-right:4px"></i>Scan</div>
            <div class="qr-tab-btn" onclick="switchTab('gen',this)"><i class="ph ph-qr-code" style="margin-right:4px"></i>Generate QR</div>
            <div class="qr-tab-btn" onclick="switchTab('hist',this)"><i class="ph ph-clock-counter-clockwise" style="margin-right:4px"></i>History</div>
        </div>

        <!-- ======= SCAN TAB ======= -->
        <div id="tab-scan">

            <!-- Manual Search + Upload Image (camera scan stays removed — that
                 needed live getUserMedia which has its own hardware/permission
                 issues; Upload Image only needs a static photo, decoded here
                 with the jsQR-first chain that was actually proven to work
                 against this exact deployment, unlike the earlier ZXing-only
                 attempt which failed on a clean, freshly generated QR code). -->
            <div class="stat-card mb-3">
                <div class="fw-bold mb-1" style="font-size:15px"><i class="ph ph-magnifying-glass" style="margin-right:6px"></i>Manual Search / Upload Image</div>
                <div style="font-size:12px;color:var(--text-faint);margin-bottom:14px">
                    Type item name, code (7NV-0001), or upload a photo of the QR/barcode
                </div>
                <div class="d-flex gap-2 mb-3">
                    <input type="text" id="qrManual" class="form-control"
                           style="font-size:15px;padding:12px 14px;border:2px solid var(--border-color);border-radius:10px;transition:all 0.2s;background:var(--bg-subtle);color:var(--text-primary)"
                           placeholder="Type item name or code e.g. 7NV-0001..."
                           oninput="qrSearch(this.value)"
                           onfocus="this.style.borderColor='#0096FF';this.style.boxShadow='0 0 0 4px rgba(0,150,255,0.08)'"
                           onblur="this.style.borderColor='var(--border-color)';this.style.boxShadow=''">
                    <button class="btn btn-outline-primary" onclick="document.getElementById('qrFileInput').click()" id="uploadBtn"
                            style="border-radius:10px;font-size:14px;font-weight:600;padding:10px 16px;white-space:nowrap">
                        <i class="ph ph-image me-1"></i>Upload Image
                    </button>
                    <input type="file" id="qrFileInput" accept="image/*" style="display:none" onchange="handleQRFileUpload(event)">
                </div>
                <div id="qrUploadPreviewWrap" style="display:none;margin-bottom:12px;text-align:center">
                    <img id="qrUploadPreview" style="max-width:180px;max-height:180px;border-radius:10px;border:2px solid var(--border-color);box-shadow:0 2px 10px var(--shadow-color)">
                    <div id="qrUploadStatus" style="font-size:12px;color:var(--text-faint);margin-top:6px;font-weight:600"></div>
                </div>
                <div id="qrSearchResults"></div>
            </div>

            <!-- Item Result -->
            <div class="stat-card" id="qrResultCard" style="display:none;border-left:4px solid #22c55e">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small">Item Found <i class="ph ph-check-circle" style="color:#22c55e"></i></div>
                        <div style="font-size:18px;font-weight:800" id="qrResName">—</div>
                        <div style="font-size:12px;color:var(--text-faint);font-family:monospace" id="qrResCode">—</div>
                    </div>
                    <span id="qrResStatus" class="badge bg-success">—</span>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4 text-center" style="background:var(--bg-subtle);border-radius:8px;padding:10px">
                        <div style="font-size:22px;font-weight:800" id="qrResQty">—</div>
                        <div class="text-muted" style="font-size:10px">QUANTITY</div>
                    </div>
                    <div class="col-4 text-center" style="background:var(--bg-subtle);border-radius:8px;padding:10px">
                        <div style="font-size:22px;font-weight:700;color:var(--text-muted)" id="qrResPar">—</div>
                        <div class="text-muted" style="font-size:10px">PAR LEVEL</div>
                    </div>
                    <div class="col-4 text-center" style="background:var(--bg-subtle);border-radius:8px;padding:10px">
                        <div style="font-size:16px;font-weight:700;color:#22c55e" id="qrResPrice">—</div>
                        <div class="text-muted" style="font-size:10px">PRICE/UNIT</div>
                    </div>
                </div>
                <div class="text-muted small mb-3">
                    <i class="ph ph-map-pin" style="margin-right:4px"></i><span id="qrResLoc">—</span> &nbsp;|&nbsp;
                    <i class="ph ph-tag" style="margin-right:4px"></i><span id="qrResCat">—</span> &nbsp;|&nbsp;
                    <i class="ph ph-calendar" style="margin-right:4px"></i><span id="qrResExpiry">—</span>
                </div>
                <div class="border-top pt-3">
                    <div class="fw-bold small mb-2"><i class="ph ph-lightning" style="margin-right:6px"></i>Quick Stock Update</div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <button class="btn btn-outline-secondary" onclick="qtyChange(-1)"
                                style="width:40px;height:40px;font-size:20px;padding:0;border-radius:10px">−</button>
                        <input type="number" id="qrQtyInput" class="form-control text-center fw-bold"
                               value="1" min="1" style="font-size:18px;border-radius:10px">
                        <button class="btn btn-outline-secondary" onclick="qtyChange(1)"
                                style="width:40px;height:40px;font-size:20px;padding:0;border-radius:10px">+</button>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <button class="btn btn-success flex-grow-1" onclick="submitQRUpdate('receive')" style="border-radius:10px">
                            <i class="ph ph-arrow-down-left me-1"></i>Receive Stock
                        </button>
                        <button class="btn btn-danger flex-grow-1" onclick="submitQRUpdate('issue')" style="border-radius:10px">
                            <i class="ph ph-arrow-up-right me-1"></i>Issue Stock
                        </button>
                    </div>
                    <button class="btn btn-outline-secondary w-100" onclick="clearQRResult()" style="border-radius:10px">
                        <i class="ph ph-x" style="margin-right:4px"></i>Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- ======= GENERATE QR TAB ======= -->
        <div id="tab-gen" style="display:none">
            <div class="stat-card mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <div class="fw-bold" style="font-size:15px"><i class="ph ph-qr-code" style="margin-right:6px"></i>Generate QR Codes</div>
                        <div style="font-size:12px;color:var(--text-faint);margin-top:2px">
                            Scan with iPhone camera · Download & print to stick on item
                        </div>
                    </div>
                </div>
                <input type="text" class="form-control" placeholder="Search item by name or code..."
                       id="qrGenSearch" oninput="renderQRCodes(this.value)"
                       style="font-size:14px;padding:11px 14px;border:2px solid var(--border-color);border-radius:10px;transition:all 0.2s;background:var(--bg-subtle);color:var(--text-primary)"
                       onfocus="this.style.borderColor='#0096FF';this.style.boxShadow='0 0 0 4px rgba(0,150,255,0.08)'"
                       onblur="this.style.borderColor='var(--border-color)';this.style.boxShadow=''">
            </div>
            <div id="qrLoadingSpinner" style="display:none;text-align:center;padding:30px">
                <div style="width:40px;height:40px;border:4px solid var(--border-color);border-top-color:#0096FF;border-radius:50%;animation:spinRing 0.7s linear infinite;margin:0 auto 12px"></div>
                <div style="font-size:13px;color:var(--text-faint);font-weight:600">Generating codes...</div>
            </div>
            <div class="row g-3" id="qrCodeGrid"></div>
        </div>

        <!-- ======= HISTORY TAB ======= -->
        <div id="tab-hist" style="display:none">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-3">
                    <div class="fw-bold"><i class="ph ph-clock-counter-clockwise" style="margin-right:6px"></i>Scan History</div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadHistory()" style="border-radius:8px">
                        <i class="ph ph-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div id="qrHistList"><div class="text-muted text-center py-3">Loading...</div></div>
            </div>
        </div>
        </div>

        <!-- Camera scan overlay stays removed (live getUserMedia has its own
             hardware/permission failure modes). Upload Image is back below —
             decoded via jsQR FIRST, which is what actually worked when tested
             directly against this server; ZXing 0.19.1 alone failed to read a
             clean, freshly generated QR code, so it's now only the last-resort
             fallback (kept for 1D barcodes — EAN-13/UPC/Code128 — that jsQR
             doesn't decode). -->
        <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.19.1/umd/index.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

        <!-- NOTE: pinned to 1.5.1, NOT 1.5.3 — the qrcode package dropped the
             prebuilt build/qrcode.min.js bundle starting at 1.5.2/1.5.3, so that
             URL 404s and window.QRCode never gets defined (this was the actual
             cause of "QR code doesn't appear"). 1.5.1 still ships the bundle. -->
        <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>

        <script>
        const APP_URL  = '<?= APP_URL ?>';
        const inventory = <?= json_encode($inventory) ?>;
        const qaLocations  = <?= json_encode($qaLocations) ?>;
        const qaCategories = <?= json_encode(array_keys($qaCategoryIcons)) ?>;
        let currentItem   = null;
        let zxingImageReader = null;

        const BARCODE_DETECTOR_FORMATS = [
            'qr_code', 'ean_13', 'ean_8', 'upc_a', 'upc_e',
            'code_128', 'code_39', 'itf', 'codabar', 'data_matrix', 'pdf417'
        ];
        let barcodeDetector = null;
        if ('BarcodeDetector' in window) {
            try { barcodeDetector = new BarcodeDetector({ formats: BARCODE_DETECTOR_FORMATS }); }
            catch (e) { barcodeDetector = null; }
        }
        const ZXING_FORMATS = (typeof ZXing !== 'undefined') ? [
            ZXing.BarcodeFormat.QR_CODE,  ZXing.BarcodeFormat.EAN_13, ZXing.BarcodeFormat.EAN_8,
            ZXing.BarcodeFormat.UPC_A,    ZXing.BarcodeFormat.UPC_E,  ZXing.BarcodeFormat.CODE_128,
            ZXing.BarcodeFormat.CODE_39,  ZXing.BarcodeFormat.ITF,    ZXing.BarcodeFormat.CODABAR,
            ZXing.BarcodeFormat.DATA_MATRIX, ZXing.BarcodeFormat.PDF_417,
        ] : [];

        // ======= UPLOAD IMAGE (decode QR/barcode from a picture) =======
        function makeUpscaledCanvas(img, targetMinDim) {
            const minDim = Math.min(img.naturalWidth, img.naturalHeight);
            const scale  = Math.max(1, targetMinDim / minDim);
            const w = Math.round(img.naturalWidth  * scale);
            const h = Math.round(img.naturalHeight * scale);
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = false;
            ctx.drawImage(img, 0, 0, w, h);
            return canvas;
        }

        const jsqrDecodeCanvas = document.createElement('canvas');
        const jsqrDecodeCtx = jsqrDecodeCanvas.getContext('2d', { willReadFrequently: true });
        function tryJsQR(imgOrCanvas, onFound, onFail) {
            if (typeof jsQR === 'undefined') { onFail(); return; }
            try {
                const w = imgOrCanvas.naturalWidth || imgOrCanvas.width;
                const h = imgOrCanvas.naturalHeight || imgOrCanvas.height;
                if (!w || !h) { onFail(); return; }
                jsqrDecodeCanvas.width = w; jsqrDecodeCanvas.height = h;
                jsqrDecodeCtx.drawImage(imgOrCanvas, 0, 0, w, h);
                const imageData = jsqrDecodeCtx.getImageData(0, 0, w, h);
                const result = jsQR(imageData.data, w, h);
                if (result && result.data) onFound(result.data); else onFail();
            } catch (e) { onFail(); }
        }

        function handleQRFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const previewWrap = document.getElementById('qrUploadPreviewWrap');
            const preview = document.getElementById('qrUploadPreview');
            const status = document.getElementById('qrUploadStatus');
            const url = URL.createObjectURL(file);
            preview.src = url;
            previewWrap.style.display = 'block';
            status.innerHTML = '<i class="ph ph-spinner-gap" style="margin-right:4px;animation:spinRing 0.8s linear infinite;display:inline-block"></i>Reading code from image...';

            const cleanup = () => { event.target.value = ''; setTimeout(() => URL.revokeObjectURL(url), 5000); };
            const onFound = (code) => {
                status.innerHTML = '<i class="ph ph-check-circle" style="margin-right:4px;color:#22c55e"></i>Detected: ' + escapeHtml(code);
                processCode(code);
                cleanup();
            };
            const onNotFound = () => {
                status.innerHTML = '<i class="ph ph-x-circle" style="margin-right:4px;color:#ef4444"></i>No QR or barcode detected. Try a clearer photo.';
                showToast('<i class="ph ph-x-circle" style="margin-right:4px"></i>No code detected in image.', false);
                showAddNewItemPrompt('');
                cleanup();
            };

            const tryNative = (source, onFail) => {
                if (!barcodeDetector) { onFail(); return; }
                barcodeDetector.detect(source)
                    .then(codes => { if (codes.length > 0 && codes[0].rawValue) onFound(codes[0].rawValue); else onFail(); })
                    .catch(onFail);
            };

            const decodeWithZXingUrl = (imgUrl, onFail) => {
                if (typeof ZXing === 'undefined') { onFail(); return; }
                if (!zxingImageReader) {
                    const hints = new Map();
                    hints.set(ZXing.DecodeHintType.TRY_HARDER, true);
                    hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, ZXING_FORMATS);
                    zxingImageReader = new ZXing.BrowserMultiFormatReader(hints);
                }
                zxingImageReader.decodeFromImageUrl(imgUrl).then(result => onFound(result.getText())).catch(onFail);
            };

            // Order proven against this exact server: native (if available) ->
            // jsQR (this is the one that actually decoded a clean QR when ZXing
            // alone failed) -> upscaled retries of both -> ZXing as last resort
            // for 1D barcode formats jsQR doesn't handle.
            const img = new Image();
            img.onload = () => {
                tryNative(img, () => {
                    tryJsQR(img, onFound, () => {
                        let upCanvas = null;
                        try { upCanvas = makeUpscaledCanvas(img, 900); } catch (e) {}
                        const afterUpscaled = () => {
                            if (upCanvas) tryJsQR(upCanvas, onFound, () => decodeWithZXingUrl(url, () => {
                                if (upCanvas) decodeWithZXingUrl(upCanvas.toDataURL('image/png'), onNotFound);
                                else onNotFound();
                            }));
                            else decodeWithZXingUrl(url, onNotFound);
                        };
                        if (upCanvas) tryNative(upCanvas, afterUpscaled);
                        else afterUpscaled();
                    });
                });
            };
            img.onerror = () => {
                status.innerHTML = '<i class="ph ph-x-circle" style="margin-right:4px;color:#ef4444"></i>Could not read that file as an image.';
                cleanup();
            };
            img.src = url;
        }

        // ======= TABS =======
        function switchTab(tab, el) {
            ['scan', 'gen', 'hist'].forEach(t => document.getElementById('tab-' + t).style.display = 'none');
            document.querySelectorAll('.qr-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + tab).style.display = '';
            el.classList.add('active');
            if (tab === 'gen')  renderQRCodes('');
            if (tab === 'hist') loadHistory();
        }

        // ======= SEARCH =======
        function qrSearch(val) {
            const div = document.getElementById('qrSearchResults');
            if (!val || val.length < 2) { div.innerHTML = ''; return; }
            const results = inventory.filter(i =>
                i.item_name.toLowerCase().includes(val.toLowerCase()) ||
                (i.item_code || '').toLowerCase().includes(val.toLowerCase())
            ).slice(0, 6);
            if (!results.length) {
                div.innerHTML = '<div class="text-muted small py-2">No items found.</div>';
                return;
            }
            div.innerHTML = results.map(i => `
                <div onclick="selectItem(${i.item_id})"
                     style="padding:10px 12px;border-radius:8px;cursor:pointer;margin-bottom:4px;
                            background:var(--bg-subtle);border:1px solid var(--border-color);
                            display:flex;justify-content:space-between;align-items:center"
                     onmouseover="this.style.background='#eff6ff';this.style.borderColor='#0096FF'"
                     onmouseout="this.style.background='var(--bg-subtle)';this.style.borderColor='var(--border-color)'">
                    <div>
                        <div style="font-weight:600;font-size:13px">${i.item_name}</div>
                        <div style="font-size:11px;color:var(--text-faint);font-family:monospace">${i.item_code} · ${i.loc}</div>
                    </div>
                    <span class="badge ${i.quantity==0?'bg-danger':i.quantity<=i.par_level?'bg-warning':'bg-success'}">
                        ${i.quantity} units
                    </span>
                </div>`).join('');
        }

        function selectItem(id) {
            const item = inventory.find(i => i.item_id == id);
            if (!item) return;
            currentItem = item;
            document.getElementById('qrResName').textContent   = item.item_name;
            document.getElementById('qrResCode').textContent   = item.item_code;
            document.getElementById('qrResQty').textContent    = item.quantity;
            document.getElementById('qrResPar').textContent    = item.par_level;
            document.getElementById('qrResPrice').textContent  = 'RM ' + parseFloat(item.unit_price || 0).toFixed(2);
            document.getElementById('qrResLoc').textContent    = item.loc;
            document.getElementById('qrResCat').textContent    = item.category;
            document.getElementById('qrResExpiry').textContent = 'Expiry: ' + (item.expiry_date || 'N/A');
            const s     = item.quantity == 0 ? 'Out of Stock' : item.quantity <= item.par_level ? 'Low Stock' : 'In-Stock';
            const badge = document.getElementById('qrResStatus');
            badge.textContent = s;
            badge.className   = 'badge ' + (s === 'In-Stock' ? 'bg-success' : s === 'Low Stock' ? 'bg-warning text-dark' : 'bg-danger');
            document.getElementById('qrResultCard').style.display = '';
            document.getElementById('qrSearchResults').innerHTML  = '';
            document.getElementById('qrManual').value = '';
        }

        // ── Register an unrecognized scan as a brand-new inventory item ──
        // Renders a full quick-add form INLINE (Name, Code/SKU, Category,
        // Location, Quantity, Par Level, Unit Price, Expiry) right in the
        // scan results, instead of sending the user to a separate page. The
        // scanned/uploaded code is pre-filled into the Code/SKU field but
        // stays editable, since not every scan produces the code you want to
        // keep (e.g. a manufacturer barcode vs. your own SKU scheme).
        function showAddNewItemPrompt(code) {
            const div = document.getElementById('qrSearchResults');

            const catOptions = qaCategories.map(c =>
                `<option value="${c}">${c}</option>`
            ).join('');
            const locOptions = qaLocations.map(l =>
                `<option value="${l.location_id}">${escapeHtml(l.location_name)}</option>`
            ).join('');

            div.innerHTML += `
                <div id="quickAddForm" style="padding:16px;border-radius:12px;margin-top:8px;
                            background:var(--bg-subtle);border:1px dashed var(--border-color)">
                    <div style="font-weight:700;font-size:14px;margin-bottom:2px">
                        <i class="ph ph-plus-circle" style="margin-right:6px;color:#0096FF"></i>Add Product Details
                    </div>
                    <div style="font-size:11px;color:var(--text-faint);margin-bottom:12px">
                        No item matches <span style="font-family:monospace">${escapeHtml(code)}</span> — register it below.
                    </div>

                    <div id="qaError" style="display:none;font-size:12px;color:#ef4444;background:rgba(239,68,68,.1);
                                border:1px solid rgba(239,68,68,.3);border-radius:8px;padding:8px 12px;margin-bottom:10px"></div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-7">
                            <label class="form-label" style="font-size:11px;font-weight:700;margin-bottom:3px">
                                <i class="ph ph-package me-1"></i>Product Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="qaName" class="form-control form-control-sm"
                                   placeholder="e.g. Shampoo 50ml, Bath Towel..." style="border-radius:8px">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" style="font-size:11px;font-weight:700;margin-bottom:3px">
                                <i class="ph ph-barcode me-1"></i>Code / SKU
                            </label>
                            <input type="text" id="qaCode" class="form-control form-control-sm" maxlength="20"
                                   value="${escapeHtml(code)}" style="border-radius:8px;font-family:monospace">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:11px;font-weight:700;margin-bottom:3px">
                                <i class="ph ph-tag me-1"></i>Category <span class="text-danger">*</span>
                            </label>
                            <select id="qaCategory" class="form-select form-select-sm" style="border-radius:8px">
                                <option value="">Select category...</option>
                                ${catOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:11px;font-weight:700;margin-bottom:3px">
                                <i class="ph ph-map-pin me-1"></i>Location <span class="text-danger">*</span>
                            </label>
                            <select id="qaLocation" class="form-select form-select-sm" style="border-radius:8px">
                                <option value="">Select location...</option>
                                ${locOptions}
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;font-weight:700;margin-bottom:3px">Quantity</label>
                            <input type="number" id="qaQty" class="form-control form-control-sm" min="0" value="0" style="border-radius:8px">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;font-weight:700;margin-bottom:3px">Par Level</label>
                            <input type="number" id="qaPar" class="form-control form-control-sm" min="0" value="0" style="border-radius:8px">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;font-weight:700;margin-bottom:3px">Unit Price (RM)</label>
                            <input type="number" id="qaPrice" class="form-control form-control-sm" min="0" step="0.01" value="0" style="border-radius:8px">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;font-weight:700;margin-bottom:3px">Expiry Date</label>
                            <input type="date" id="qaExpiry" class="form-control form-control-sm" min="<?= date('Y-m-d') ?>" style="border-radius:8px">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" style="border-radius:8px;font-weight:600" onclick="submitQuickAdd()" id="qaSubmitBtn">
                            <i class="ph ph-check me-1"></i>Save Product
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" style="border-radius:8px" onclick="document.getElementById('quickAddForm').remove()">
                            Cancel
                        </button>
                    </div>
                </div>`;
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s == null ? '' : String(s);
            return d.innerHTML;
        }

        function submitQuickAdd() {
            const name = document.getElementById('qaName').value.trim();
            const code = document.getElementById('qaCode').value.trim();
            const category = document.getElementById('qaCategory').value;
            const location = document.getElementById('qaLocation').value;
            const errBox = document.getElementById('qaError');
            errBox.style.display = 'none';

            if (!name) { errBox.textContent = 'Product name is required.'; errBox.style.display = 'block'; return; }
            if (!category) { errBox.textContent = 'Please select a category.'; errBox.style.display = 'block'; return; }
            if (!location) { errBox.textContent = 'Please select a location.'; errBox.style.display = 'block'; return; }

            const btn = document.getElementById('qaSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap" style="margin-right:4px;animation:spinRing 0.8s linear infinite;display:inline-block"></i>Saving...';

            const fd = new FormData();
            fd.append('item_name',   name);
            fd.append('item_code',   code);
            fd.append('category',    category);
            fd.append('location_id', location);
            fd.append('quantity',    document.getElementById('qaQty').value || '0');
            fd.append('par_level',   document.getElementById('qaPar').value || '0');
            fd.append('unit_price',  document.getElementById('qaPrice').value || '0');
            fd.append('expiry_date', document.getElementById('qaExpiry').value || '');

            fetch(APP_URL + '/inventory/quick-add', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (!d.success) {
                        errBox.textContent = d.message || 'Could not save this item.';
                        errBox.style.display = 'block';
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ph ph-check me-1"></i>Save Product';
                        return;
                    }
                    // Add the new item to the in-page inventory list so it's
                    // immediately findable by a repeat scan/search without a
                    // full page reload.
                    inventory.push(d.item);
                    const formEl = document.getElementById('quickAddForm');
                    if (formEl) formEl.remove();
                    showToast('<i class="ph ph-check-circle" style="margin-right:4px"></i>' + d.message, true);
                    selectItem(d.item.item_id);
                })
                .catch(() => {
                    errBox.textContent = 'Network error — could not save. Please try again.';
                    errBox.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-check me-1"></i>Save Product';
                });
        }

        // ── Fix: robust processCode handles code + item_code + partial name ──
        function processCode(raw) {
            let code = raw.trim();
            // The app's own "Generate QR" tab encodes a full deep link
            // (qrPayload(): APP_URL + '/product/view?code=...') so a phone's
            // native camera opens the product page directly. That means
            // scanning/uploading one of THIS APP'S OWN QR codes back into
            // this Scan tab hands processCode() a whole URL, which never
            // matches an item_code/item_id/item_name as-is. Unwrap it first.
            try {
                const maybeUrl = new URL(code);
                const embedded = maybeUrl.searchParams.get('code');
                if (embedded) code = embedded;
            } catch (e) { /* not a URL — plain code/barcode, use as-is */ }
            const item = inventory.find(i =>
                (i.item_code && i.item_code === code) ||
                String(i.item_id) === code ||
                i.item_name.toLowerCase() === code.toLowerCase()
            );
            if (item) {
                selectItem(item.item_id);
                showToast('<i class="ph ph-check-circle" style="margin-right:4px"></i>Found: ' + item.item_name, true);
                switchToScanTab();
            } else {
                document.getElementById('qrManual').value = code;
                qrSearch(code);
                showAddNewItemPrompt(code);
                switchToScanTab();
            }
        }

        function switchToScanTab() {
            document.querySelectorAll('.qr-tab-btn').forEach((b, i) => {
                b.classList.toggle('active', i === 0);
            });
            ['scan','gen','hist'].forEach((t, i) => {
                document.getElementById('tab-' + t).style.display = i === 0 ? '' : 'none';
            });
        }

        function qtyChange(d) {
            const el = document.getElementById('qrQtyInput');
            el.value = Math.max(1, (parseInt(el.value) || 1) + d);
        }

        function submitQRUpdate(action) {
            if (!currentItem) return;
            const qty = parseInt(document.getElementById('qrQtyInput').value) || 1;
            const fd  = new FormData();
            fd.append('item_id', currentItem.item_id);
            fd.append('action',  action);
            fd.append('qty',     qty);
            fetch(APP_URL + '/inventory/qr-update', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        const verb = action === 'receive' ? '<i class="ph ph-check-circle" style="margin-right:4px"></i>Received' : '<i class="ph ph-arrow-up-right" style="margin-right:4px"></i>Issued';
                        showToast(verb + ' ' + qty + ' unit(s) — ' + currentItem.item_name, true);
                        currentItem.quantity = d.new_qty;
                        document.getElementById('qrResQty').textContent = d.new_qty;
                        const badge = document.getElementById('qrResStatus');
                        badge.textContent = d.status;
                        badge.className   = 'badge ' + (d.status === 'In-Stock' ? 'bg-success' : d.status === 'Low Stock' ? 'bg-warning text-dark' : 'bg-danger');
                        const idx = inventory.findIndex(i => i.item_id == currentItem.item_id);
                        if (idx >= 0) inventory[idx].quantity = d.new_qty;
                    } else {
                        showToast('<i class="ph ph-x-circle" style="margin-right:4px"></i>' + (d.message || 'Update failed.'), false);
                    }
                }).catch(() => showToast('<i class="ph ph-x-circle" style="margin-right:4px"></i>Connection error. Please try again.', false));
        }

        function clearQRResult() {
            currentItem = null;
            document.getElementById('qrResultCard').style.display = 'none';
            document.getElementById('qrManual').value = '';
            document.getElementById('qrSearchResults').innerHTML = '';
        }

        // ======= GENERATE QR + BARCODE =======
        const catColors = { 'Toiletries':'#3b82f6','F&B':'#22c55e','Linens':'#f59e0b','Cleaning':'#8b5cf6','Minibar':'#ef4444' };
        const catIcons  = { 'Toiletries':'ph-fill ph-drop','F&B':'ph-fill ph-bowl-food','Linens':'ph-fill ph-towel','Cleaning':'ph-fill ph-broom','Minibar':'ph-fill ph-wine' };
        function catIconClass(cat) { return catIcons[cat] || 'ph-fill ph-package'; }

        function statusColor(i) { return i.quantity==0?'#ef4444':i.quantity<=i.par_level?'#f59e0b':'#22c55e'; }
        function statusBg(i)    { return i.quantity==0?'#fee2e2':i.quantity<=i.par_level?'#fef9c3':'#dcfce7'; }
        function statusText(i)  { return i.quantity==0?'Out of Stock':i.quantity<=i.par_level?'Low Stock':'In-Stock'; }

        function renderQRCodes(search) {
            // Capped to 8 cards per request — was rendering the entire
            // inventory at once, which made this tab a long, slow scroll.
            const items   = inventory.filter(i => !search ||
                i.item_name.toLowerCase().includes(search.toLowerCase()) ||
                i.item_code.toLowerCase().includes(search.toLowerCase())
            ).slice(0, 8);
            const grid    = document.getElementById('qrCodeGrid');
            const spinner = document.getElementById('qrLoadingSpinner');

            grid.innerHTML = '';
            spinner.style.display = 'block';

            setTimeout(() => {
                spinner.style.display = 'none';
                // NOTE: the QR background chip below is intentionally always
                // white (#f8fafc) regardless of theme — QR codes need a light
                // background with dark modules to stay scannable; inverting
                // it in dark mode would break scanning.
                grid.innerHTML = items.map((i, idx) => `
                <div class="col-md-3 col-sm-6 col-6">
                <div class="qr-gen-card" style="border-top:3px solid ${catColors[i.category]||'#0096FF'};animation-delay:${idx*0.04}s">

                    <!-- Always the category icon — never the uploaded photo.
                         This used to key off i.image_path (same field the big
                         box uses), which is why it looked fine right after an
                         upload — the JS patches the big box in place — but
                         flipped to showing the photo too on the NEXT
                         re-render (switch tabs, search, reload), since both
                         boxes were reading the same field in the template.
                         Hard-coded to the icon now so that can't happen again. -->
                    <div style="width:64px;height:64px;border-radius:12px;overflow:hidden;margin:0 auto 8px;
                                background:#f8fafc;display:flex;align-items:center;justify-content:center" id="photo-${i.item_id}">
                        <i class="${catIconClass(i.category)}" style="font-size:26px;color:#c9ccd6"></i>
                    </div>

                    <div style="margin-bottom:10px">
                        <div style="font-size:12px;font-weight:800;color:var(--text-primary);line-height:1.3">${i.item_name}</div>
                        <div style="font-size:9px;color:#0096FF;font-family:monospace;font-weight:700;
                                    margin-top:3px;background:#eff6ff;border-radius:4px;padding:2px 6px;display:inline-block">
                            ${i.item_code}
                        </div>
                    </div>

                    <div style="margin-bottom:10px">
                        <div id="qrBigBox-${i.item_id}" style="background:#f8fafc;border-radius:8px;padding:6px;display:inline-block;width:110px;height:110px;box-sizing:content-box">
                            ${i.image_path
                                ? `<img src="${APP_URL}/${i.image_path}?v=${Date.now()}" style="width:110px;height:110px;object-fit:cover;border-radius:4px;display:block" alt="">`
                                : `<canvas id="qrc-${i.item_id}" style="border-radius:4px;display:block"></canvas>`}
                        </div>
                    </div>

                    <div style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;
                                background:${statusBg(i)};color:${statusColor(i)};
                                display:inline-block;margin-bottom:10px">
                        ● ${statusText(i)} (${i.quantity})
                    </div>

                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                        <button onclick="downloadQR(${i.item_id}, '${i.item_code}', '${i.item_name.replace(/'/g, "\\'")}' )"
                            style="font-size:10px;padding:5px 10px;border:1.5px solid #0096FF;
                                   color:#0096FF;background:var(--bg-card);border-radius:8px;cursor:pointer;
                                   transition:all 0.15s;font-weight:600"
                            onmouseover="this.style.background='#0096FF';this.style.color='#fff'"
                            onmouseout="this.style.background='var(--bg-card)';this.style.color='#0096FF'">
                            <i class="ph ph-download-simple" style="margin-right:4px"></i>Download QR</button>
                        <label style="font-size:10px;padding:5px 10px;border:1.5px solid #22c55e;
                                   color:#22c55e;background:var(--bg-card);border-radius:8px;cursor:pointer;
                                   transition:all 0.15s;font-weight:600;margin:0"
                            onmouseover="this.style.background='#22c55e';this.style.color='#fff'"
                            onmouseout="this.style.background='var(--bg-card)';this.style.color='#22c55e'">
                            <i class="ph ph-camera-plus" style="margin-right:4px"></i>${i.image_path ? 'Change' : 'Upload'} Photo
                            <input type="file" accept="image/*" style="display:none" onchange="uploadItemPhoto(${i.item_id}, this)">
                        </label>
                    </div>
                </div>
                </div>`).join('');

                // Render QR codes after DOM ready
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    if (typeof QRCode === 'undefined') {
                        console.error('7NVENT: QRCode library failed to load (CDN blocked or offline?). QR codes cannot render.');
                        showToast('<i class="ph ph-warning-circle" style="margin-right:4px"></i>QR library not loaded — check internet connection.', false);
                        return;
                    }
                    items.forEach(i => {
                        const canvas = document.getElementById('qrc-' + i.item_id);
                        if (!canvas) return;
                        QRCode.toCanvas(canvas, qrPayload(i.item_code), {
                            width: 110, margin: 1,
                            color: { dark: '#1a1a2e', light: '#ffffff' }
                        }, function(err) {
                            if (err) console.error('7NVENT: QR render failed for ' + i.item_code, err);
                        });
                    });
                }));
            }, 350);
        }

        // ── Fix: reliable QR download ─────────────────────────────────
        // QR payload is a deep link (not bare text) so scanning with a phone's
        // native camera opens straight into this item's lookup, instead of just
        // popping up plain text with nowhere to go.
        //
        // Points at /product/view, NOT /qr-scanner — the scanner page requires
        // a 7nvent login (Auth::required() + Inventory/Housekeeping Manager
        // role), so anyone scanning the printed label without an account was
        // just redirected to the login page instead of seeing the product.
        // /product/view is a public, read-only page showing photo + name +
        // category + price + stock badge, no login needed.
        function qrPayload(code) {
            return APP_URL + '/product/view?code=' + encodeURIComponent(code);
        }

        // ── Upload/replace a product photo for one item ──
        // This is the photo the public /product/view page (linked from the
        // printed QR — see qrPayload() above) actually displays. Uploads via
        // AJAX so the whole "Generate QR" grid doesn't need a page reload.
        // Uploading a photo replaces the BIG QR/barcode box (qrBigBox-{id}),
        // NOT the small top icon (photo-{id}) — that one stays untouched per
        // explicit request ("dont touch the logo on the top of the qr, it is
        // already good"). Download QR still works after this: downloadQR()
        // below renders its own independent temp canvas from item_code, it
        // doesn't read from the visible box at all.
        function uploadItemPhoto(itemId, inputEl) {
            const file = inputEl.files[0];
            if (!file) return;

            const bigBox = document.getElementById('qrBigBox-' + itemId);
            const prevHtml = bigBox.innerHTML;
            bigBox.innerHTML = '<div style="width:110px;height:110px;display:flex;align-items:center;justify-content:center"><i class="ph ph-spinner-gap" style="font-size:22px;color:#0096FF;animation:spinRing 0.8s linear infinite;display:inline-block"></i></div>';

            const fd = new FormData();
            fd.append('item_id', itemId);
            fd.append('image', file);

            fetch(APP_URL + '/inventory/upload-image', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    inputEl.value = '';
                    if (!d.success) {
                        bigBox.innerHTML = prevHtml;
                        showToast('<i class="ph ph-x-circle" style="margin-right:4px"></i>' + (d.message || 'Upload failed.'), false);
                        return;
                    }
                    const idx = inventory.findIndex(i => i.item_id == itemId);
                    if (idx >= 0) inventory[idx].image_path = d.image_path;
                    bigBox.innerHTML = `<img src="${d.image_url}?v=${Date.now()}" style="width:110px;height:110px;object-fit:cover;border-radius:4px;display:block" alt="">`;
                    showToast('<i class="ph ph-check-circle" style="margin-right:4px"></i>Photo saved.', true);
                    // Refresh the button label (Upload -> Change) without a full re-render.
                    const btnLabel = bigBox.closest('.qr-gen-card')?.querySelector('label i.ph-camera-plus');
                    if (btnLabel && btnLabel.parentElement) {
                        btnLabel.parentElement.innerHTML = btnLabel.parentElement.innerHTML.replace('Upload Photo', 'Change Photo');
                    }
                })
                .catch(() => {
                    inputEl.value = '';
                    bigBox.innerHTML = prevHtml;
                    showToast('<i class="ph ph-x-circle" style="margin-right:4px"></i>Network error — could not upload.', false);
                });
        }

        function downloadQR(id, code, name) {
            if (typeof QRCode === 'undefined') { showToast('<i class="ph ph-warning-circle" style="margin-right:4px"></i>QR library not loaded.', false); return; }
            const tmp = document.createElement('canvas');
            QRCode.toCanvas(tmp, qrPayload(code), {
                width: 400, margin: 3,
                color: { dark: '#000000', light: '#ffffff' }
            }, function(err) {
                if (err) { showToast('<i class="ph ph-x-circle" style="margin-right:4px"></i>Failed to generate QR.', false); return; }
                const a = document.createElement('a');
                a.download = 'QR_' + code + '.png';
                a.href     = tmp.toDataURL('image/png');
                a.click();
                showToast('<i class="ph ph-check-circle" style="margin-right:4px"></i>QR downloaded: ' + code, true);
            });
        }

        // ======= HISTORY =======
        function loadHistory() {
            fetch(APP_URL + '/inventory/scan-log')
                .then(r => r.json())
                .then(data => {
                    const div = document.getElementById('qrHistList');
                    if (!data.length) {
                        div.innerHTML = '<div style="color:var(--text-faint);text-align:center;padding:20px">No scan history yet.</div>';
                        return;
                    }
                    div.innerHTML = data.map(l => `
                        <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-subtle)">
                            <div style="font-size:22px">${l.action === 'QR_RECEIVE' ? '<i class="ph ph-package"></i>' : '<i class="ph ph-arrow-up-right"></i>'}</div>
                            <div style="flex:1">
                                <div style="font-size:13px;font-weight:600">${l.description}</div>
                                <div style="font-size:11px;color:var(--text-faint)">${l.timestamp}</div>
                            </div>
                        </div>`).join('');
                }).catch(() => {
                    document.getElementById('qrHistList').innerHTML =
                        '<div class="text-muted text-center py-3">Failed to load history.</div>';
                });
        }

        // ======= TOAST =======
        function showToast(msg, ok = true) {
            const existing = document.querySelector('.qr-toast');
            if (existing) existing.remove();
            const t = document.createElement('div');
            t.className = 'qr-toast';
            t.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
                background:${ok ? '#22c55e' : '#ef4444'};color:#fff;
                padding:12px 24px;border-radius:12px;font-size:13px;font-weight:600;
                z-index:99999;text-align:center;max-width:90vw;
                box-shadow:0 4px 20px rgba(0,0,0,0.25);
                animation:toastIn 0.3s cubic-bezier(0.34,1.56,0.64,1)`;
            t.innerHTML = msg;
            if (!document.getElementById('toastStyle')) {
                const s = document.createElement('style');
                s.id = 'toastStyle';
                s.textContent = '@keyframes toastIn{from{opacity:0;transform:translateX(-50%) translateY(10px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}';
                document.head.appendChild(s);
            }
            document.body.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity 0.3s'; setTimeout(() => t.remove(), 300); }, 3500);
        }

        // ── Auto-handle a deep-linked QR (?code=...) ──
        // When a printed/generated QR is scanned with a phone's native camera,
        // it opens this page with ?code=<item_code> in the URL. Process it the
        // same way a camera scan would, then clean the URL.
        (function() {
            const params = new URLSearchParams(window.location.search);
            const code = params.get('code');
            if (code) {
                processCode(code);
                if (window.history.replaceState) {
                    window.history.replaceState({}, '', window.location.pathname);
                }
            }
        })();

        // Init
        renderQRCodes('');
        </script>

        <?php
        $content = ob_get_clean();
        require_once __DIR__ . '/../../../resources/views/layouts/app.php';
    }

    // ── AJAX: Stock update via QR ────────────────────────────────────
    public function qrUpdate(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }
        header('Content-Type: application/json');

        $itemId = (int)($_POST['item_id'] ?? 0);
        $action = clean($_POST['action']  ?? '');
        $qty    = (int)($_POST['qty']     ?? 0);

        // qty <= 0 must be rejected explicitly — "!$qty" alone only catches
        // zero, so a negative number (e.g. -5) would previously slip through
        // and silently invert receive/issue math, corrupting stock counts.
        if (!$itemId || $qty <= 0 || !in_array($action, ['receive', 'issue'], true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            return;
        }

        $item = db()->fetchOne("SELECT * FROM inventory_items WHERE item_id = ?", [$itemId]);
        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            return;
        }

        if ($action === 'receive') {
            $newQty = $item['quantity'] + $qty;
            $desc   = "QR Scan: Received +{$qty} units of '{$item['item_name']}' ({$item['quantity']} → {$newQty})";
            $logAct = 'QR_RECEIVE';
        } else {
            if ($qty > $item['quantity']) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock. Current: ' . $item['quantity']]);
                return;
            }
            $newQty = $item['quantity'] - $qty;
            $desc   = "QR Scan: Issued -{$qty} units of '{$item['item_name']}' ({$item['quantity']} → {$newQty})";
            $logAct = 'QR_ISSUE';
        }

        $status = StockStatus::determine($newQty, (int)$item['par_level']);

        // ── Fix: removed last_updated (column doesn't exist) ─────────
        db()->execute(
            "UPDATE inventory_items SET quantity = ?, status = ? WHERE item_id = ?",
            [$newQty, $status, $itemId]
        );

        Auth::log($logAct, 'inventory_items', $itemId, $desc);

        // Auto-alert if needed
        if ($status !== 'In-Stock') {
            $existing = db()->fetchOne(
                "SELECT alert_id FROM alerts WHERE item_id=? AND status='Active' LIMIT 1",
                [$itemId]
            );
            if (!$existing) {
                $alertType = $status === 'Out of Stock' ? 'Critical' : 'Warning';
                db()->execute(
                    "INSERT INTO alerts (alert_type, title, description, item_id, location_id, auto_generated)
                     VALUES (?,?,?,?,?,1)",
                    [
                        $alertType,
                        "{$item['category']} - {$item['item_name']} {$status}",
                        "After QR scan: {$newQty} unit(s). Par level: {$item['par_level']} unit(s).",
                        $itemId,
                        $item['location_id']
                    ]
                );
            }
        } else {
            db()->execute(
                "UPDATE alerts SET status='Resolved', resolved_at=NOW()
                 WHERE item_id=? AND status='Active' AND alert_type IN ('Critical','Warning')",
                [$itemId]
            );
        }

        echo json_encode(['success' => true, 'new_qty' => $newQty, 'status' => $status]);
    }

    // ── AJAX: Scan history ───────────────────────────────────────────
    public function scanLog(): void {
        Auth::required();
        header('Content-Type: application/json');
        $logs = db()->fetchAll(
            "SELECT description, timestamp, action
             FROM audit_logs
             WHERE action IN ('QR_RECEIVE','QR_ISSUE')
             ORDER BY timestamp DESC LIMIT 30"
        );
        echo json_encode($logs ?: []);
    }
}