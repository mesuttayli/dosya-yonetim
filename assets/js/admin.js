/**
 * TechCode - Admin Panel JavaScript
 * Dashboard & Management Functions
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initModals();
    initDataTables();
    initAlerts();
    initFormValidation();
    initSearchFilter();
    initBulkActions();
});

/**
 * Sidebar Toggle
 */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (!sidebar || !sidebarToggle) return;

    // Toggle sidebar on button click
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');

        // On mobile, toggle active class
        if (window.innerWidth <= 992) {
            sidebar.classList.toggle('active');
        }

        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Restore sidebar state
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed && window.innerWidth > 992) {
        sidebar.classList.add('collapsed');
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 992) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('active');
        }
    });
}

/**
 * Modal Functions
 */
function initModals() {
    // Close modal when clicking overlay
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Data Tables
 */
function initDataTables() {
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    const selectAllMessages = document.getElementById('selectAllMessages');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-select');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    if (selectAllMessages) {
        selectAllMessages.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.message-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Row hover effect
    document.querySelectorAll('.data-table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.background = 'rgba(0, 212, 255, 0.02)';
        });
        row.addEventListener('mouseleave', function() {
            this.style.background = '';
        });
    });

    // Sort functionality (demo)
    document.querySelectorAll('.data-table th').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function() {
            // Toggle sort icon (demo only)
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-sort-up');
                icon.classList.toggle('fa-sort-down');
            }
        });
    });
}

/**
 * Alert Notifications
 */
function initAlerts() {
    // Auto-dismiss alerts after 5 seconds (if they have dismiss class)
    document.querySelectorAll('.alert.auto-dismiss').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

function showAlert(type, title, message, autoDismiss = true) {
    const iconMap = {
        success: 'fa-check-circle',
        warning: 'fa-exclamation-triangle',
        error: 'fa-times-circle',
        info: 'fa-info-circle'
    };

    const alertHtml = `
        <div class="alert alert-${type} ${autoDismiss ? 'auto-dismiss' : ''}" style="position: fixed; top: 100px; right: 20px; z-index: 1001; max-width: 400px;">
            <i class="fas ${iconMap[type]}"></i>
            <div class="alert-content">
                <h5>${title}</h5>
                <p>${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;margin-left:auto;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHtml);

    const alertElement = document.body.lastElementChild;

    if (autoDismiss) {
        setTimeout(() => {
            alertElement.style.opacity = '0';
            setTimeout(() => alertElement.remove(), 300);
        }, 5000);
    }
}

/**
 * Form Validation
 */
function initFormValidation() {
    document.querySelectorAll('.admin-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Check required fields
            this.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ff5f57';
                    field.focus();
                } else {
                    field.style.borderColor = '';
                }
            });

            // Email validation
            this.querySelectorAll('input[type="email"]').forEach(email => {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email.value && !emailRegex.test(email.value)) {
                    isValid = false;
                    email.style.borderColor = '#ff5f57';
                }
            });

            // Password validation (min 8 chars)
            this.querySelectorAll('input[type="password"]').forEach(password => {
                if (password.value && password.value.length < 8) {
                    isValid = false;
                    password.style.borderColor = '#ff5f57';
                }
            });

            if (!isValid) {
                e.preventDefault();
                showAlert('error', 'Hata', 'Lütfen gerekli alanları doğru şekilde doldurun.');
            }
        });

        // Real-time validation feedback
        form.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.style.borderColor = '#ff5f57';
                } else if (this.value) {
                    this.style.borderColor = 'var(--accent-green)';
                }
            });

            input.addEventListener('focus', function() {
                this.style.borderColor = 'var(--accent-blue)';
            });
        });
    });
}

/**
 * Search & Filter
 */
function initSearchFilter() {
    const searchInputs = document.querySelectorAll('.header-search input, .admin-content input[type="search"]');

    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            filterContent(searchTerm);
        }, 300));
    });
}

function filterContent(searchTerm) {
    // Filter table rows
    document.querySelectorAll('.data-table tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });

    // Filter message items
    document.querySelectorAll('.message-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

/**
 * Bulk Actions
 */
function initBulkActions() {
    // Delete confirmation
    document.querySelectorAll('.table-action-btn.delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Bu öğeyi silmek istediğinizden emin misiniz?')) {
                // Find parent row
                const row = this.closest('tr');
                if (row) {
                    row.style.opacity = '0.5';
                    setTimeout(() => {
                        row.remove();
                        showAlert('success', 'Silindi', 'Öğe başarıyla silindi.');
                    }, 300);
                }
            }
        });
    });
}

/**
 * Utility Functions
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatDate(date) {
    return new Intl.DateTimeFormat('tr-TR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(date));
}

function formatNumber(num) {
    return new Intl.NumberFormat('tr-TR').format(num);
}

/**
 * File Upload Handler
 */
document.querySelectorAll('.file-upload').forEach(upload => {
    const input = upload.querySelector('input[type="file"]');

    upload.addEventListener('click', () => input.click());

    upload.addEventListener('dragover', (e) => {
        e.preventDefault();
        upload.style.borderColor = 'var(--accent-blue)';
        upload.style.background = 'rgba(0, 212, 255, 0.05)';
    });

    upload.addEventListener('dragleave', () => {
        upload.style.borderColor = '';
        upload.style.background = '';
    });

    upload.addEventListener('drop', (e) => {
        e.preventDefault();
        upload.style.borderColor = '';
        upload.style.background = '';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0], upload);
        }
    });

    input.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileUpload(this.files[0], upload);
        }
    });
});

function handleFileUpload(file, container) {
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showAlert('error', 'Hata', 'Sadece resim dosyaları yükleyebilirsiniz.');
        return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showAlert('error', 'Hata', 'Dosya boyutu 5MB\'dan küçük olmalıdır.');
        return;
    }

    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        container.innerHTML = `
            <img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 150px; border-radius: 8px;">
            <p style="margin-top: 10px; font-size: 0.85rem; color: var(--text-secondary);">${file.name}</p>
            <button type="button" class="btn btn-ghost btn-sm" style="margin-top: 10px;" onclick="resetFileUpload(this.parentElement)">
                <i class="fas fa-times"></i> Kaldır
            </button>
        `;
    };
    reader.readAsDataURL(file);

    showAlert('success', 'Başarılı', 'Dosya yüklendi.');
}

function resetFileUpload(container) {
    container.innerHTML = `
        <i class="fas fa-cloud-upload-alt"></i>
        <p>Görsel yüklemek için tıklayın veya sürükleyin</p>
        <input type="file" accept="image/*">
    `;

    // Re-attach event listener
    const newInput = container.querySelector('input[type="file"]');
    newInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileUpload(this.files[0], container);
        }
    });
}

/**
 * Pagination Handler
 */
document.querySelectorAll('.pagination-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (this.disabled) return;

        // Remove active class from all buttons
        document.querySelectorAll('.pagination-btn').forEach(b => b.classList.remove('active'));

        // Add active class to clicked button (if it's a number)
        if (!this.querySelector('i')) {
            this.classList.add('active');
        }

        // In a real application, this would trigger data loading
        console.log('Page changed to:', this.textContent || 'next/prev');
    });
});

/**
 * Quick Stats Animation
 */
function animateStats() {
    document.querySelectorAll('.stat-card-info h3').forEach(stat => {
        const value = parseInt(stat.textContent.replace(/[^0-9]/g, ''));
        const suffix = stat.textContent.replace(/[0-9]/g, '');

        let current = 0;
        const increment = value / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= value) {
                stat.textContent = value + suffix;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current) + suffix;
            }
        }, 20);
    });
}

// Run stats animation when page loads
if (document.querySelector('.stat-card')) {
    setTimeout(animateStats, 300);
}

/**
 * Real-time Clock
 */
function updateClock() {
    const clockElement = document.getElementById('adminClock');
    if (clockElement) {
        clockElement.textContent = new Date().toLocaleTimeString('tr-TR');
    }
}

setInterval(updateClock, 1000);

/**
 * Session Timeout Warning
 */
let sessionTimeout;

function resetSessionTimeout() {
    clearTimeout(sessionTimeout);
    sessionTimeout = setTimeout(() => {
        if (confirm('Oturumunuz sona ermek üzere. Devam etmek istiyor musunuz?')) {
            // Refresh session
            resetSessionTimeout();
        } else {
            window.location.href = 'login.html';
        }
    }, 30 * 60 * 1000); // 30 minutes
}

// Reset timeout on user activity
['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
    document.addEventListener(event, debounce(resetSessionTimeout, 1000));
});

// Initialize session timeout
resetSessionTimeout();

/**
 * Dark/Light Mode Toggle (for future use)
 */
function toggleTheme() {
    document.body.classList.toggle('light-mode');
    localStorage.setItem('adminTheme', document.body.classList.contains('light-mode') ? 'light' : 'dark');
}

// Check saved theme
const savedTheme = localStorage.getItem('adminTheme');
if (savedTheme === 'light') {
    document.body.classList.add('light-mode');
}

/**
 * Export functionality (demo)
 */
function exportData(format) {
    showAlert('info', 'Dışa Aktarılıyor', `Veriler ${format.toUpperCase()} formatında dışa aktarılıyor...`);

    setTimeout(() => {
        showAlert('success', 'Tamamlandı', 'Veriler başarıyla dışa aktarıldı.');
    }, 2000);
}

// Make functions globally available
window.openModal = openModal;
window.closeModal = closeModal;
window.showAlert = showAlert;
window.exportData = exportData;
window.resetFileUpload = resetFileUpload;
