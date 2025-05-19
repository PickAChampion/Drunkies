// Admin Dashboard JavaScript

$(document).ready(function() {
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Image preview for file inputs
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        const reader = new FileReader();
        const preview = $(this).siblings('.image-preview');

        reader.onload = function(e) {
            if (preview.length === 0) {
                $(this).after('<img class="image-preview mt-2">');
            }
            $('.image-preview').attr('src', e.target.result);
        }.bind(this);

        if (file) {
            reader.readAsDataURL(file);
        }
    });

    // Delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const deleteUrl = $(this).attr('href');
        
        if (confirm('Are you sure you want to delete this item?')) {
            window.location.href = deleteUrl;
        }
    });

    // Status update
    $('.status-select').on('change', function() {
        const orderId = $(this).data('order-id');
        const newStatus = $(this).val();
        
        $.ajax({
            url: 'update_order_status.php',
            method: 'POST',
            data: {
                order_id: orderId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    showToast('Success', 'Order status updated successfully', 'success');
                } else {
                    showToast('Error', response.message || 'Failed to update order status', 'error');
                }
            },
            error: function() {
                showToast('Error', 'Failed to update order status', 'error');
            }
        });
    });

    // Toast notification function
    function showToast(title, message, type = 'success') {
        const toast = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" 
                 role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        const toastContainer = $('#toast-container');
        if (toastContainer.length === 0) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
        }

        $('#toast-container').append(toast);
        const toastElement = $('.toast').last();
        const bsToast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();

        // Remove toast after it's hidden
        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // DataTables initialization
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });
    }

    // Chart.js initialization
    if (typeof Chart !== 'undefined') {
        // Sales Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Sales',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: '#0d6efd',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Products Chart
        const productsCtx = document.getElementById('productsChart');
        if (productsCtx) {
            new Chart(productsCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Wine', 'Beer', 'Spirits', 'Other'],
                    datasets: [{
                        data: [30, 25, 35, 10],
                        backgroundColor: ['#0d6efd', '#198754', '#dc3545', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
}); 