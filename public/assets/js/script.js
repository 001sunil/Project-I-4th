// SIDEBAR - Always open on desktop, close button to hide
function toggleSidebar() {
    document.body.classList.toggle('sidebar-open');
}

function closeSidebar() {
    document.body.classList.remove('sidebar-open');
}

function openSidebar() {
    document.body.classList.add('sidebar-open');
}

// --- Role Detection ---
function getUserRole() {
    var body = document.body;
    if (body.classList.contains('role-admin')) return 'admin';
    if (body.classList.contains('role-staff')) return 'staff';
    return 'staff'; // default to least-privilege
}

document.addEventListener('DOMContentLoaded', function () {
    var role = getUserRole();

    // On desktop, always open sidebar
    if (window.innerWidth > 1024) {
        document.body.classList.add('sidebar-open');
    }

    // Close sidebar on mobile when a link is clicked
    var sidebarLinks = document.querySelectorAll('.sidebar-menu li a');
    sidebarLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 1024) {
                document.body.classList.remove('sidebar-open');
            }
        });
    });

    // On resize, always open on desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1024) {
            document.body.classList.add('sidebar-open');
        } else {
            document.body.classList.remove('sidebar-open');
        }
    });

    // --- Loading State on Form Submit ---
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.dataset.originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });
    });

    // --- Role-Based UI Restrictions ---
    applyRoleRestrictions(role);

    // --- Real-time Character Counter for Textareas ---
    document.querySelectorAll('textarea[maxlength]').forEach(function (ta) {
        var max = parseInt(ta.getAttribute('maxlength'));
        var counter = document.createElement('small');
        counter.style.color = '#94a3b8';
        counter.style.fontSize = '0.75rem';
        counter.style.textAlign = 'right';
        counter.style.display = 'block';
        counter.style.marginTop = '4px';
        ta.parentNode.appendChild(counter);

        function updateCount() {
            var remaining = max - ta.value.length;
            counter.textContent = remaining + ' characters remaining';
            counter.style.color = remaining < 20 ? '#ef4444' : '#94a3b8';
        }
        ta.addEventListener('input', updateCount);
        updateCount();
    });

    // --- Auto-dismiss alerts after 5 seconds ---
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function () { alert.remove(); }, 300);
        }, 5000);
    });
});

// --- Role-Based Restriction Logic ---
function applyRoleRestrictions(role) {
    // Staff cannot access certain features
    if (role === 'staff') {
        // Disable delete buttons for staff
        document.querySelectorAll('.btn-danger').forEach(function (btn) {
            if (btn.textContent.trim() === 'Delete') {
                btn.title = 'Only admins can delete records';
                btn.style.opacity = '0.5';
                btn.style.pointerEvents = 'none';
                btn.style.cursor = 'not-allowed';
            }
        });

        // Add warning tooltips on admin-only buttons
        document.querySelectorAll('.btn-warning').forEach(function (btn) {
            if (btn.href && btn.href.includes('settings')) {
                btn.title = 'Admin access required';
                btn.style.opacity = '0.5';
                btn.style.pointerEvents = 'none';
            }
        });
    }

    // Admin gets extra capabilities
    if (role === 'admin') {
        // Show any admin-only elements
        document.querySelectorAll('.role-staff-only').forEach(function (el) {
            el.style.display = 'none';
        });
    }
}

// --- Enhanced Form Validation with Role Awareness ---

function togglePrescriptionFields() {
    var select = document.getElementById('medicine_id');
    var section = document.getElementById('rx-fields');
    if (!select || !section) return;
    var selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.getAttribute('data-rx') === '1') {
        section.classList.remove('hidden');
    } else {
        section.classList.add('hidden');
    }
}

function confirmDelete(name) {
    var role = getUserRole();
    if (role !== 'admin') {
        alert('Only administrators can delete records.');
        return false;
    }
    return confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.');
}

// --- Inline Field Error Helper ---
function showFieldError(input, message) {
    var existing = input.parentNode.querySelector('.field-error');
    if (existing) existing.remove();

    var el = document.createElement('small');
    el.className = 'field-error';
    el.textContent = message;
    input.parentNode.appendChild(el);

    // Scroll into view if needed
    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    input.focus();

    // Shake animation
    input.style.animation = 'none';
    input.offsetHeight; // trigger reflow
    input.style.animation = 'shake 0.3s ease';
    return false;
}

function clearFieldErrors(form) {
    form.querySelectorAll('.field-error').forEach(function (el) {
        el.remove();
    });
}

function validateSaleForm() {
    var role = getUserRole();
    var form = document.querySelector('#rx-fields').closest('form');
    clearFieldErrors(form);

    var medicineSelect = document.getElementById('medicine_id');
    if (!medicineSelect.value) {
        return showFieldError(medicineSelect, 'Please select a medicine.');
    }

    var quantity = document.getElementById('quantity_sold');
    var qtyVal = parseInt(quantity.value);
    if (isNaN(qtyVal) || qtyVal <= 0) {
        return showFieldError(quantity, 'Please enter a valid quantity (must be a positive number).');
    }

    // Staff: check if user is trying to sell a prescription medicine without permission
    if (role === 'staff') {
        var selectedOption = medicineSelect.options[medicineSelect.selectedIndex];
        if (selectedOption && selectedOption.getAttribute('data-rx') === '1') {
            var section = document.getElementById('rx-fields');
            if (section.classList.contains('hidden')) {
                // This shouldn't happen normally, but as a safeguard
                return showFieldError(medicineSelect, 'This medicine requires prescription details.');
            }
        }
    }

    var section = document.getElementById('rx-fields');
    if (!section.classList.contains('hidden')) {
        var doctorName = document.getElementById('doctor_name');
        var nmcNumber = document.getElementById('nmc_number');
        var prescDate = document.getElementById('prescription_date');

        if (doctorName.value.trim() === '') {
            return showFieldError(doctorName, "Please enter the doctor's name.");
        }
        if (nmcNumber.value.trim() === '' || nmcNumber.value.trim().length < 6 || isNaN(nmcNumber.value.trim())) {
            return showFieldError(nmcNumber, 'Please enter a valid NMC registration number (at least 6 digits).');
        }
        if (prescDate.value === '') {
            return showFieldError(prescDate, 'Please enter the prescription date.');
        }
    }
    return true;
}

function validateMedicineForm() {
    var role = getUserRole();
    var form = document.getElementById('name').closest('form');
    clearFieldErrors(form);

    var name = document.getElementById('name');
    var batch = document.getElementById('batch_no');
    var quantity = document.getElementById('quantity');
    var price = document.getElementById('price');
    var expiry = document.getElementById('expiry_date');
    var category = document.getElementById('category_id');
    var supplier = document.getElementById('supplier_id');

    if (name.value.trim() === '') return showFieldError(name, 'Medicine name is required.');
    if (name.value.trim().length > 150) return showFieldError(name, 'Name must be 150 characters or fewer.');
    if (category && !category.value) return showFieldError(category, 'Please select a category.');
    if (supplier && !supplier.value) return showFieldError(supplier, 'Please select a supplier.');
    if (batch.value.trim() === '') return showFieldError(batch, 'Batch number is required.');
    if (expiry.value === '') return showFieldError(expiry, 'Expiry date is required.');

    // Check if expiry date is in the past (warning for staff)
    if (expiry.value) {
        var expiryDate = new Date(expiry.value);
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        if (expiryDate < today && role === 'staff') {
            return showFieldError(expiry, 'Expiry date is in the past. Please verify.');
        }
    }

    if (parseInt(quantity.value) < 0 || isNaN(parseInt(quantity.value))) {
        return showFieldError(quantity, 'Please enter a valid quantity (0 or more).');
    }
    if (parseFloat(price.value) <= 0 || isNaN(parseFloat(price.value))) {
        return showFieldError(price, 'Please enter a valid price (greater than 0).');
    }
    return true;
}

function validateUserForm() {
    var role = getUserRole();
    var form = document.getElementById('username').closest('form');
    clearFieldErrors(form);

    var username = document.getElementById('username');
    var fullName = document.getElementById('full_name');

    if (username && username.value.trim() === '') return showFieldError(username, 'Username is required.');
    if (username && username.value.trim().length > 50) return showFieldError(username, 'Username must be 50 characters or fewer.');
    if (fullName.value.trim() === '') return showFieldError(fullName, 'Full name is required.');
    if (fullName.value.trim().length > 100) return showFieldError(fullName, 'Full name must be 100 characters or fewer.');

    var password = document.getElementById('password');
    if (password && password.value.trim() !== '') {
        if (password.value.length < 6) {
            return showFieldError(password, 'Password must be at least 6 characters.');
        }
        // Warn if password is weak
        if (password.value.length < 8) {
            var existing = password.parentNode.querySelector('.field-error');
            if (!existing) {
                var hint = document.createElement('small');
                hint.className = 'field-error';
                hint.style.color = '#f59e0b';
                hint.textContent = 'Tip: Use 8+ characters with mixed letters and numbers for a stronger password.';
                password.parentNode.appendChild(hint);
            }
        }
    }

    // Staff cannot change roles
    var roleSelect = document.getElementById('role');
    if (roleSelect && role === 'staff' && roleSelect.value === 'admin') {
        return showFieldError(roleSelect, 'Only administrators can assign admin role.');
    }

    return true;
}

function validateAdjustmentForm() {
    var role = getUserRole();
    var form = document.getElementById('quantity_change').closest('form');
    clearFieldErrors(form);

    var medicine = document.getElementById('medicine_id');
    if (medicine && !medicine.value) {
        return showFieldError(medicine, 'Please select a medicine.');
    }

    var qty = document.getElementById('quantity_change');
    if (parseInt(qty.value) === 0 || isNaN(parseInt(qty.value))) {
        return showFieldError(qty, 'Please enter a valid quantity change (positive or negative, not zero).');
    }

    var reason = document.getElementById('reason');
    if (reason && !reason.value) {
        return showFieldError(reason, 'Please select a reason.');
    }

    return true;
}

// --- PDF Export via Browser Print ---
function exportPrescriptionPDF() {
    // Use the browser's built-in print-to-PDF
    // This gives a clean PDF without the sidebar/nav
    window.print();
}

// --- Shake Animation for Invalid Fields ---
(function () {
    var style = document.createElement('style');
    style.textContent = '@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }';
    document.head.appendChild(style);
})();
