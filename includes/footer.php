</div>
        </main>
    </div>
    
    <script>
        // ==================== UPDATE DATETIME ====================
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            
            const datetimeElement = document.getElementById('datetime');
            if (datetimeElement) {
                datetimeElement.textContent = now.toLocaleDateString('id-ID', options);
            }
        }

        // Update setiap detik
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // ==================== PREVIEW IMAGE ====================
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-width: 100%; border-radius: 10px;">';
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // ==================== CONFIRM DELETE ====================
        function confirmDelete(message) {
            return confirm(message || 'Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan!');
        }

        // ==================== AUTO HIDE ALERT ====================
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            
            alerts.forEach(alert => {
                // Auto hide setelah 5 detik
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
                
                // Close button (jika ada)
                const closeBtn = alert.querySelector('.alert-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        alert.style.transition = 'opacity 0.3s ease';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    });
                }
            });
        });

        // ==================== FORM VALIDATION ====================
        // Validasi form sebelum submit
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                alert('Mohon lengkapi semua field yang wajib diisi!');
            }
            
            return isValid;
        }

        // ==================== TABLE UTILITIES ====================
        // Toggle select all checkboxes di table
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            
            // Update bulk action button jika ada
            if (typeof updateBulkDeleteButton === 'function') {
                updateBulkDeleteButton();
            }
        }

        // Update bulk delete button state
        function updateBulkDeleteButton() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const selectedCount = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAll');
            
            if (selectedCount) {
                selectedCount.textContent = checkboxes.length;
            }
            
            if (bulkDeleteBtn) {
                if (checkboxes.length > 0) {
                    bulkDeleteBtn.disabled = false;
                    bulkDeleteBtn.style.opacity = '1';
                } else {
                    bulkDeleteBtn.disabled = true;
                    bulkDeleteBtn.style.opacity = '0.5';
                }
            }
            
            // Update select all checkbox
            if (selectAll) {
                const allCheckboxes = document.querySelectorAll('.row-checkbox');
                selectAll.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
            }
        }

        // Confirm bulk delete
        function confirmBulkDelete() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const count = checkboxes.length;
            
            if (count === 0) {
                alert('Pilih minimal 1 item untuk dihapus!');
                return false;
            }
            
            if (confirm('Yakin ingin menghapus ' + count + ' item yang dipilih?\n\nTindakan ini tidak dapat dibatalkan!')) {
                const form = document.getElementById('bulkDeleteForm');
                if (form) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'bulk_delete';
                    input.value = '1';
                    form.appendChild(input);
                    form.submit();
                }
            }
        }

        // ==================== LOADING INDICATOR ====================
        // Show loading overlay
        function showLoading() {
            const overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center;';
            overlay.innerHTML = '<div style="background: white; padding: 2rem; border-radius: 10px; text-align: center;"><i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: var(--primary-blue);"></i><p style="margin-top: 1rem; font-weight: 600;">Loading...</p></div>';
            document.body.appendChild(overlay);
        }

        // Hide loading overlay
        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.remove();
            }
        }

        // ==================== FILE SIZE VALIDATOR ====================
        // Validasi ukuran file
        function validateFileSize(input, maxSizeMB = 5) {
            if (input.files && input.files[0]) {
                const fileSizeMB = input.files[0].size / 1024 / 1024;
                
                if (fileSizeMB > maxSizeMB) {
                    alert('Ukuran file terlalu besar! Maksimal ' + maxSizeMB + 'MB.\nUkuran file Anda: ' + fileSizeMB.toFixed(2) + 'MB');
                    input.value = '';
                    return false;
                }
            }
            return true;
        }

        // ==================== SMOOTH SCROLL ====================
        // Smooth scroll to element
        function smoothScrollTo(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }
        }

        // ==================== COPY TO CLIPBOARD ====================
        // Copy text to clipboard
        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('Text berhasil dicopy ke clipboard!');
                }, function(err) {
                    console.error('Failed to copy: ', err);
                });
            } else {
                // Fallback untuk browser lama
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Text berhasil dicopy ke clipboard!');
            }
        }

        // ==================== DEBOUNCE FUNCTION ====================
        // Debounce untuk search input
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // ==================== FORMAT NUMBER ====================
        // Format number dengan separator
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // ==================== FORMAT DATE ====================
        // Format date ke Indonesia
        function formatDateIndo(dateString) {
            const date = new Date(dateString);
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            return date.toLocaleDateString('id-ID', options);
        }

        // ==================== CONSOLE LOG STYLING ====================
        // Console log untuk debugging (hanya untuk development)
        console.log('%c🚀 Dashboard Admin KSI Loaded!', 'color: #2E9FD8; font-size: 16px; font-weight: bold;');
        console.log('%c📅 Version: 1.0.0', 'color: #76C757; font-size: 12px;');
        console.log('%c⚠️ Debug Mode: ' + (window.location.hostname === 'localhost' ? 'ON' : 'OFF'), 'color: #ff9800; font-size: 12px;');

        // ==================== PREVENT ACCIDENTAL PAGE LEAVE ====================
        // Warn user jika ada perubahan belum disimpan (untuk form edit)
        let formChanged = false;

        document.addEventListener('DOMContentLoaded', function() {
            // Track form changes
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('change', function() {
                        formChanged = true;
                    });
                });

                // Reset flag on submit
                form.addEventListener('submit', function() {
                    formChanged = false;
                });
            });
        });

        // Warn before leaving page with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });

        // ==================== KEYBOARD SHORTCUTS ====================
        // Keyboard shortcuts untuk admin
        document.addEventListener('keydown', function(e) {
            // Ctrl+S untuk save form (prevent default browser save)
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.click();
                }
            }
        });

        // ==================== UTILITY: CHECK INTERNET CONNECTION ====================
        window.addEventListener('online', function() {
            console.log('✅ Koneksi internet tersambung');
        });

        window.addEventListener('offline', function() {
            console.log('❌ Koneksi internet terputus');
            alert('⚠️ Koneksi internet terputus! Pastikan Anda terhubung ke internet.');
        });

        // ==================== INITIALIZE ON PAGE LOAD ====================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Dashboard initialized');
            
            // Auto-focus first input in forms
            const firstInput = document.querySelector('.form-control:not([type="hidden"])');
            if (firstInput && !firstInput.value) {
                firstInput.focus();
            }

            // Initialize tooltips (jika ada)
            const tooltips = document.querySelectorAll('[data-tooltip]');
            tooltips.forEach(tooltip => {
                tooltip.addEventListener('mouseenter', function() {
                    const text = this.getAttribute('data-tooltip');
                    const tip = document.createElement('div');
                    tip.className = 'tooltip-popup';
                    tip.textContent = text;
                    tip.style.cssText = 'position: absolute; background: #333; color: white; padding: 0.5rem 1rem; border-radius: 5px; font-size: 0.85rem; z-index: 9999;';
                    document.body.appendChild(tip);
                    
                    const rect = this.getBoundingClientRect();
                    tip.style.top = (rect.top - tip.offsetHeight - 5) + 'px';
                    tip.style.left = (rect.left + (rect.width / 2) - (tip.offsetWidth / 2)) + 'px';
                });
                
                tooltip.addEventListener('mouseleave', function() {
                    const tip = document.querySelector('.tooltip-popup');
                    if (tip) tip.remove();
                });
            });
        });
    </script>
</body>
</html>