        </main>
    </div>

    <footer class="admin-footer">
        <div class="footer-content">
            <p>&copy; 2026 Asmara Restaurant. All rights reserved.</p>
            <p>Admin Dashboard v1.0</p>
        </div>
    </footer>

    <script>
        // Auto-refresh dashboard every 60 seconds
        if (document.location.pathname.endsWith('/index') || document.location.pathname.endsWith('/index.php')) {
            setTimeout(function() {
                location.reload();
            }, 60000);
        }
    </script>
    <script>
        // Image preview helpers for admin upload fields
        (function(){
            function readAndPreview(input, previewEl) {
                if (!input || !previewEl) return;
                const file = input.files && input.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function(e){
                    previewEl.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'preview';
                    previewEl.appendChild(img);
                };
                reader.readAsDataURL(file);
            }

            const menuInput = document.getElementById('menu-image-input');
            const menuPreview = document.getElementById('menu-image-preview');
            if (menuInput && menuPreview) {
                menuInput.addEventListener('change', function(){ readAndPreview(menuInput, menuPreview); });
            }

            const branchInput = document.getElementById('branch-image-input');
            const branchPreview = document.getElementById('branch-image-preview');
            if (branchInput && branchPreview) {
                branchInput.addEventListener('change', function(){ readAndPreview(branchInput, branchPreview); });
            }

            // Simple drag & drop support for inputs inside .upload-dropzone
            document.querySelectorAll('.upload-dropzone').forEach(zone => {
                zone.addEventListener('dragover', (e)=>{ e.preventDefault(); zone.style.borderColor = 'var(--color-primary)'; });
                zone.addEventListener('dragleave', ()=>{ zone.style.borderColor = ''; });
                zone.addEventListener('drop', (e)=>{
                    e.preventDefault(); zone.style.borderColor = '';
                    const input = zone.querySelector('input[type=file]');
                    if (!input) return;
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                });
            });
        })();

        // Admin UI helpers: view toggle and availability AJAX
        (function(){
            function setView(viewKey, view) {
                try { localStorage.setItem(viewKey, view); } catch(e){}
            }
            function getView(viewKey) { try { return localStorage.getItem(viewKey) || 'cards'; } catch(e){ return 'cards'; } }

            function applyViewControls() {
                const page = location.pathname.split('/').pop().replace('.php','') || 'dashboard';
                const key = 'admin_view_' + page;
                const view = getView(key);
                const selectors = ['.items-grid', '.cards-grid', '.branches-grid', '.messages-list', '.data-table', '.users-list'];
                selectors.forEach(sel => {
                    document.querySelectorAll(sel).forEach(el => {
                        if (view === 'list') el.classList.add('list-view'); else el.classList.remove('list-view');
                    });
                });
            }

            function setupViewControls() {
                const page = location.pathname.split('/').pop().replace('.php','') || 'dashboard';
                const key = 'admin_view_' + page;
                const btnList = document.getElementById('btnListView');
                const btnCard = document.getElementById('btnCardView');
                if (btnList && btnCard) {
                    btnList.addEventListener('click', ()=>{ setView(key,'list'); applyViewControls(); updateBtnActiveState(); });
                    btnCard.addEventListener('click', ()=>{ setView(key,'cards'); applyViewControls(); updateBtnActiveState(); });
                }
                updateBtnActiveState();
            }

            function updateBtnActiveState() {
                const page = location.pathname.split('/').pop().replace('.php','') || 'dashboard';
                const key = 'admin_view_' + page;
                const view = getView(key);
                const btnList = document.getElementById('btnListView');
                const btnCard = document.getElementById('btnCardView');
                if (btnList && btnCard) {
                    if (view === 'list') {
                        btnList.classList.add('active');
                        btnCard.classList.remove('active');
                    } else {
                        btnCard.classList.add('active');
                        btnList.classList.remove('active');
                    }
                }
            }

            function initAvailabilityToggles() {
                document.querySelectorAll('.js-availability-toggle').forEach(btn => {
                    btn.addEventListener('click', function(){
                        const id = this.dataset.id;
                        if (!id) return;
                        const formData = new FormData();
                        formData.append('action_type','toggle_availability');
                        formData.append('id', id);
                        fetch(location.pathname, { method:'POST', credentials:'same-origin', body: formData })
                        .then(r=> r.text())
                        .then(() => {
                            // toggle UI
                            const status = document.getElementById('status-' + id);
                            if (status) {
                                const isAvail = status.classList.contains('status-confirmed');
                                if (isAvail) {
                                    status.classList.remove('status-confirmed');
                                    status.classList.add('status-cancelled');
                                    status.textContent = 'Unavailable';
                                    btn.textContent = 'Enable';
                                } else {
                                    status.classList.remove('status-cancelled');
                                    status.classList.add('status-confirmed');
                                    status.textContent = 'Available';
                                    btn.textContent = 'Disable';
                                }
                            }
                        })
                        .catch(()=>{ alert('Failed to update availability'); });
                    });
                });
            }

            // Run on DOM ready
            document.addEventListener('DOMContentLoaded', function(){ applyViewControls(); setupViewControls(); initAvailabilityToggles(); });
            // Also run immediately in case DOMContentLoaded already fired
            applyViewControls(); setupViewControls(); initAvailabilityToggles();
        })();
    </script>
    <script>
        // Robust PDF export handler. Replaces raw anchor navigation so we can
        // surface server errors as a styled toast instead of an HTML response.
        (function(){
            function ensureToastContainer() {
                let host = document.getElementById('ad-toast-host');
                if (host) return host;
                host = document.createElement('div');
                host.id = 'ad-toast-host';
                host.style.cssText = [
                    'position:fixed', 'top:24px', 'right:24px', 'z-index:9999',
                    'display:flex', 'flex-direction:column', 'gap:10px',
                    'max-width:380px', 'pointer-events:none'
                ].join(';');
                document.body.appendChild(host);
                return host;
            }
            function showToast(kind, title, body) {
                const host = ensureToastContainer();
                const t = document.createElement('div');
                const palette = {
                    error: { bg: '#fff1f4', border: '#ed174b', icon: '!' },
                    success: { bg: '#ecfdf5', border: '#0f8a5f', icon: '\u2713' },
                    info: { bg: '#fbf8f5', border: '#d9cfc4', icon: 'i' }
                }[kind] || { bg: '#fff1f4', border: '#ed174b', icon: '!' };
                t.style.cssText = [
                    'pointer-events:auto',
                    'background:' + palette.bg,
                    'border:1px solid ' + palette.border,
                    'border-left:4px solid ' + palette.border,
                    'border-radius:14px',
                    'padding:14px 16px',
                    'box-shadow:0 12px 32px rgba(21,18,26,0.12)',
                    'font:500 13.5px/1.45 "Plus Jakarta Sans", system-ui, sans-serif',
                    'color:#15121a',
                    'display:flex', 'gap:12px', 'align-items:flex-start',
                    'animation:adToastIn .25s cubic-bezier(.22,1,.36,1)'
                ].join(';');
                t.innerHTML = ''
                    + '<span style="flex:0 0 22px;height:22px;border-radius:50%;background:' + palette.border + ';color:#fff;'
                    + 'display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;line-height:1">' + palette.icon + '</span>'
                    + '<div style="min-width:0;flex:1">'
                    +   '<div style="font-weight:700;font-size:13.5px;margin-bottom:2px">' + (title || '') + '</div>'
                    +   '<div style="color:#554f5e;font-size:12.5px">' + (body || '') + '</div>'
                    + '</div>';
                host.appendChild(t);
                setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .25s'; }, kind === 'error' ? 7000 : 4500);
                setTimeout(() => { t.remove(); if (!host.children.length) host.remove(); }, 8000);
            }

            function timestamp() {
                const d = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                return d.getFullYear() + pad(d.getMonth()+1) + pad(d.getDate())
                    + '-' + pad(d.getHours()) + pad(d.getMinutes()) + pad(d.getSeconds());
            }

            function bindExport() {
                const link = document.querySelector('.js-export-pdf');
                if (!link || link.dataset.bound === '1') return;
                link.dataset.bound = '1';
                link.addEventListener('click', async function(ev){
                    ev.preventDefault();
                    const label = link.querySelector('.js-export-label');
                    const originalLabel = label ? label.textContent : '';
                    if (label) label.textContent = 'Generating\u2026';
                    link.classList.add('is-loading');
                    link.style.pointerEvents = 'none';
                    try {
                        const url = link.getAttribute('href');
                        const resp = await fetch(url, {
                            method: 'GET',
                            credentials: 'same-origin',
                            redirect: 'follow',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/pdf, application/json' }
                        });
                        const finalUrl = resp.url || url;
                        const ct = (resp.headers.get('Content-Type') || '').toLowerCase();
                        if (finalUrl.indexOf('/login') !== -1) {
                            throw { error: 'session_expired',
                                message: 'Your admin session expired or you are not logged in. Please log in and try again.' };
                        }
                        if (!resp.ok) {
                            // Try to parse JSON error from server
                            let err = { error: 'http_' + resp.status, message: 'Server returned ' + resp.status };
                            try { if (ct.indexOf('application/json') !== -1) err = await resp.json(); }
                            catch(e){ err.message = err.message + ' (response was not JSON)'; }
                            throw err;
                        }
                        if (ct.indexOf('application/json') !== -1) {
                            const err = await resp.json();
                            throw err;
                        }
                        if (ct.indexOf('application/pdf') === -1) {
                            throw { error: 'wrong_content_type',
                                message: 'Server returned ' + ct + ' instead of a PDF. The PDF generator may be missing on this host.' };
                        }
                        const blob = await resp.blob();
                        if (blob.size < 500) {
                            throw { error: 'tiny_pdf', message: 'PDF was empty (' + blob.size + ' bytes). Generator may have failed silently.' };
                        }
                        const prefix = link.dataset.filenamePrefix || 'report';
                        const filename = prefix + '-' + timestamp() + '.pdf';
                        const objUrl = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = objUrl;
                        a.download = filename;
                        a.style.display = 'none';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        setTimeout(() => URL.revokeObjectURL(objUrl), 4000);
                        showToast('success', 'Report downloaded', 'Saved as ' + filename + ' (' + Math.round(blob.size/1024) + ' KB).');
                    } catch (err) {
                        const msg = (err && err.message) ? err.message : (typeof err === 'string' ? err : 'Could not generate the PDF.');
                        showToast('error', 'PDF export failed', msg);
                        // eslint-disable-next-line no-console
                        console.error('[asmara-export]', err);
                    } finally {
                        if (label) label.textContent = originalLabel || 'Export PDF';
                        link.classList.remove('is-loading');
                        link.style.pointerEvents = '';
                    }
                });
            }

            // Inject the toast animation once
            if (!document.getElementById('ad-toast-style')) {
                const s = document.createElement('style');
                s.id = 'ad-toast-style';
                s.textContent = '@keyframes adToastIn { from { transform: translateY(-6px); opacity:0 } to { transform: translateY(0); opacity:1 } }'
                    + '.js-export-pdf.is-loading { opacity: .7; cursor: progress !important; }';
                document.head.appendChild(s);
            }

            document.addEventListener('DOMContentLoaded', bindExport);
            bindExport();
        })();
    </script>
</body>
</html>
