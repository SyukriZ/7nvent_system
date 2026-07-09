<?php $pageTitle = 'Add Supplier'; ob_start(); ?>

<style>
/* Page entrance */
.sup-card { opacity:0; transform:translateY(20px); animation:supUp 0.55s cubic-bezier(0.23,1,0.32,1) forwards; }
@keyframes supUp { to { opacity:1; transform:translateY(0); } }
.sup-step { opacity:0; transform:translateY(14px); animation:supUp 0.45s ease forwards; }

/* Floating header icon */
.header-icon {
    width:60px; height:60px; border-radius:18px;
    background:linear-gradient(135deg,#0096FF,#6366f1);
    display:flex; align-items:center; justify-content:center;
    font-size:26px; box-shadow:0 8px 24px rgba(0,150,255,0.35);
    animation:iconFloat 3s ease-in-out infinite;
    flex-shrink:0;
}
.header-icon i { color:#fff; font-size:28px; }
@keyframes iconFloat { 0%,100%{transform:translateY(0) rotate(-2deg)} 50%{transform:translateY(-7px) rotate(2deg)} }

/* Section dividers */
.sec-div {
    display:flex; align-items:center; gap:10px;
    margin:22px 0 14px; color:var(--text-faint);
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;
}
.sec-div::after { content:''; flex:1; height:1px; background:var(--border-color); }

/* Field labels */
.field-lbl {
    font-size:11px; font-weight:700; color:var(--text-muted);
    text-transform:uppercase; letter-spacing:0.7px; margin-bottom:6px; display:block;
}

/* Inputs */
.sup-input {
    font-size:15px !important; padding:12px 14px !important;
    border:2px solid var(--border-color) !important; border-radius:10px !important;
    transition:all 0.2s !important; background:var(--bg-subtle) !important;
    color:var(--text-primary) !important;
}
.sup-input:focus {
    border-color:#0096FF !important;
    box-shadow:0 0 0 4px rgba(0,150,255,0.08) !important;
    background:var(--bg-card) !important;
}
.sup-input::placeholder { color:var(--text-faint); }

/* Star rating widget - DIKEKALKAN EMOJI ⭐ */
.star-row { display:flex; gap:6px; align-items:center; }
.star-btn {
    font-size:24px; cursor:pointer; transition:transform 0.15s ease;
    background:none; border:none; padding:2px; line-height:1;
    filter:grayscale(1) opacity(0.4);
}
.star-btn.lit { filter:none; }
.star-btn:hover { transform:scale(1.25); }
.star-val { font-size:13px; font-weight:700; color:#f59e0b; min-width:30px; }

/* Submit button */
.btn-save-sup {
    background:linear-gradient(135deg,#0096FF,#6366f1);
    background-size:200%; animation:btnShift 3s ease infinite;
    border:none; color:#fff; font-size:16px; font-weight:700;
    padding:13px 40px; border-radius:12px;
    box-shadow:0 4px 16px rgba(0,150,255,0.35);
    transition:all 0.22s ease; cursor:pointer;
}
.btn-save-sup:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,150,255,0.5); color:#fff; }
@keyframes btnShift { 0%,100%{background-position:0%} 50%{background-position:100%} }

/* Preview card */
.preview-wrap { background:linear-gradient(135deg,#f0f9ff,#e0f2fe); border-radius:14px; padding:18px; border:1px solid #bae6fd; }
.preview-avatar {
    width:52px; height:52px; border-radius:14px;
    background:linear-gradient(135deg,#0096FF,#6366f1);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:18px; font-weight:800;
    animation:avatarPop 0.5s cubic-bezier(0.34,1.56,0.64,1) forwards;
}
@keyframes avatarPop { from{transform:scale(0)} to{transform:scale(1)} }

/* Lead time pill */
.lead-pill { background:#dcfce7; color:#166534; border-radius:20px; padding:3px 12px; font-size:12px; font-weight:700; }

/* Tips */
.tip-item { display:flex; gap:10px; padding:10px; border-radius:10px; margin-bottom:8px; }
</style>

<div class="row g-4">

    <!-- LEFT: FORM -->
    <div class="col-md-8">
        <div class="stat-card sup-card">

            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="header-icon"><i class="ph-fill ph-truck"></i></div>
                <div>
                    <h5 class="mb-0 fw-bold">Add New Supplier</h5>
                    <div style="font-size:12px;color:var(--text-faint)">Register a new supplier to the 7NVENT network</div>
                </div>
            </div>

            <form method="POST" action="<?= APP_URL ?>/suppliers/store" id="supForm">

                <!-- Company Info -->
                <div class="sec-div sup-step" style="animation-delay:0.1s">
                    <span><i class="ph-fill ph-buildings me-1"></i>Company Information</span>
                </div>
                <div class="row g-3 sup-step" style="animation-delay:0.12s">
                    <div class="col-md-8">
                        <label class="field-lbl"><i class="ph-fill ph-buildings me-1"></i>Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="supplier_name" class="form-control sup-input"
                               placeholder="e.g. Nestle Products Sdn Bhd" required
                               oninput="updatePreview(this.value)">
                    </div>
                    <div class="col-md-4">
                        <label class="field-lbl"><i class="ph-fill ph-tag me-1"></i>Product Category</label>
                        <input type="text" name="category" class="form-control sup-input"
                               placeholder="e.g. F&B, Toiletries">
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="sec-div sup-step" style="animation-delay:0.18s">
                    <span><i class="ph-fill ph-user me-1"></i>Contact Details</span>
                </div>
                <div class="row g-3 sup-step" style="animation-delay:0.2s">
                    <div class="col-md-6">
                        <label class="field-lbl"><i class="ph-fill ph-user me-1"></i>Representative Name</label>
                        <input type="text" name="contact_person" class="form-control sup-input"
                               placeholder="e.g. Ahmad bin Ali">
                    </div>
                    <div class="col-md-6">
                        <label class="field-lbl"><i class="ph-fill ph-phone me-1"></i>Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border:2px solid var(--border-color);border-right:none;border-radius:10px 0 0 10px;background:#f0f9ff;color:#0096FF;font-weight:700"><i class="ph-fill ph-phone"></i></span>
                            <input type="tel" name="phone" class="form-control sup-input"
                                   placeholder="03-XXXX XXXX"
                                   style="border-left:none!important;border-radius:0 10px 10px 0!important">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="field-lbl"><i class="ph-fill ph-envelope-simple me-1"></i>Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border:2px solid var(--border-color);border-right:none;border-radius:10px 0 0 10px;background:#f0f9ff;color:#0096FF;font-weight:700"><i class="ph-fill ph-envelope-simple"></i></span>
                            <input type="email" name="email" class="form-control sup-input"
                                   placeholder="procurement@company.com.my"
                                   style="border-left:none!important;border-radius:0 10px 10px 0!important">
                        </div>
                    </div>
                </div>

                <!-- Performance -->
                <div class="sec-div sup-step" style="animation-delay:0.26s">
                    <span><i class="ph-fill ph-chart-bar me-1"></i>Performance Metrics</span>
                </div>
                <div class="row g-3 sup-step" style="animation-delay:0.28s">
                    <div class="col-md-6">
                        <label class="field-lbl">⭐ Supplier Rating</label>
                        <div class="star-row" id="starRow">
                            <?php for($s=1;$s<=5;$s++): ?>
                            <button type="button" class="star-btn <?= $s<=4?'lit':'' ?>"
                                    data-val="<?= $s ?>"
                                    onclick="setRating(<?= $s ?>)">⭐</button>
                            <?php endfor; ?>
                            <span class="star-val" id="starVal">4.0</span>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="4.0">
                        <div style="font-size:10px;color:var(--text-faint);margin-top:5px">Click stars to set rating</div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-lbl"><i class="ph-fill ph-truck me-1"></i>Lead Time (Days)</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="lead_time_days" id="leadSlider"
                                   min="0.5" max="14" step="0.5" value="3.0"
                                   style="flex:1;accent-color:#0096FF"
                                   oninput="updateLead(this.value)">
                            <span id="leadVal" class="lead-pill">3.0d</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-faint);margin-top:4px">
                            <span>0.5 days</span><span>14 days</span>
                        </div>
                        <input type="hidden" name="lead_time_hidden" id="leadHidden" value="3.0">
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex gap-3 sup-step" style="animation-delay:0.36s">
                    <button type="submit" class="btn-save-sup">
                        <i class="ph-fill ph-floppy-disk me-2"></i>Save Supplier
                    </button>
                    <a href="<?= APP_URL ?>/suppliers"
                       class="btn btn-outline-secondary px-4"
                       style="padding:12px 24px;font-size:15px;border-radius:12px">
                        <i class="ph-bold ph-x me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- RIGHT: PREVIEW + TIPS -->
    <div class="col-md-4">

        <!-- Live Preview -->
        <div class="stat-card sup-card mb-4" style="animation-delay:0.15s">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-eye me-1"></i>Live Preview
            </div>
            <div class="preview-wrap">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="preview-avatar" id="previewAvatar">??</div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1e293b" id="previewName">New Supplier</div>
                        <div style="font-size:11px;color:#64748b" id="previewCat">Category not set</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <div style="text-align:center">
                        <div style="font-size:18px;font-weight:800;color:#f59e0b" id="previewRating">4.0 ⭐</div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:700">RATING</div>
                    </div>
                    <div style="text-align:center">
                        <div style="font-size:18px;font-weight:800;color:#22c55e" id="previewLead">3.0d</div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:700">LEAD TIME</div>
                    </div>
                    <div style="text-align:center">
                        <div style="font-size:18px;font-weight:800;color:#0096FF"><i class="ph-fill ph-plus-circle"></i></div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:700">STATUS</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="stat-card sup-card" style="animation-delay:0.28s">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-lightbulb me-1"></i>Quick Guide
            </div>
            <div class="tip-item" style="background:#eff6ff">
                <span>⭐</span>
                <div style="font-size:12px;color:#1d4ed8"><strong>Rating</strong> — Reflects delivery reliability and product quality</div>
            </div>
            <div class="tip-item" style="background:#f0fdf4">
                <span><i class="ph-fill ph-truck" style="color:#166534"></i></span>
                <div style="font-size:12px;color:#166534"><strong>Lead Time</strong> — Days from PO to delivery. Affects auto-reorder timing</div>
            </div>
            <div class="tip-item" style="background:#fdf4ff">
                <span><i class="ph-fill ph-envelope-simple" style="color:#6b21a8"></i></span>
                <div style="font-size:12px;color:#6b21a8"><strong>Email</strong> — Used for automated PO notifications and contacts</div>
            </div>
            <div class="tip-item" style="background:#fff7ed">
                <span><i class="ph-fill ph-buildings" style="color:#c2410c"></i></span>
                <div style="font-size:12px;color:#c2410c"><strong>SSM Name</strong> — Use the official registered company name</div>
            </div>
        </div>
    </div>
</div>

<script>
// Star rating - DIKEKALKAN EMOJI ⭐
let currentRating = 4.0;
function setRating(val) {
    currentRating = val;
    document.getElementById('ratingInput').value = val + '.0';
    document.getElementById('starVal').textContent = val + '.0';
    document.querySelectorAll('.star-btn').forEach((btn, i) => {
        btn.classList.toggle('lit', i < val);
        if (i === val - 1) {
            btn.style.transform = 'scale(1.4)';
            setTimeout(() => btn.style.transform = '', 250);
        }
    });
    document.getElementById('previewRating').textContent = val + '.0 ⭐';
}

// Lead time slider
function updateLead(val) {
    const v = parseFloat(val).toFixed(1);
    document.getElementById('leadVal').textContent = v + 'd';
    document.getElementById('leadHidden').value = v;
    document.getElementById('previewLead').textContent = v + 'd';
    const el = document.getElementById('leadVal');
    el.style.background = val <= 3 ? '#dcfce7' : val <= 7 ? '#fef9c3' : '#fee2e2';
    el.style.color       = val <= 3 ? '#166534' : val <= 7 ? '#92400e' : '#991b1b';
}

// Preview update
function updatePreview(name) {
    const ini = name ? name.trim().split(' ').slice(0,2).map(w=>w[0]||'').join('').toUpperCase() : '??';
    document.getElementById('previewAvatar').textContent = ini || '??';
    document.getElementById('previewName').textContent   = name || 'New Supplier';
}

// Fix lead_time_days on submit
document.getElementById('supForm').addEventListener('submit', function() {
    const slider = document.getElementById('leadSlider');
    slider.name = 'lead_time_days';
});
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/app.php'; ?>