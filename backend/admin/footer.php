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
</body>
</html>
