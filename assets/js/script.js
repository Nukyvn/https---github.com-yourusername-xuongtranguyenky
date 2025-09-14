// Social Media Manager - Custom JavaScript

$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions
    $('.btn-delete, .btn-danger').click(function(e) {
        if (!confirm('Bạn có chắc chắn muốn thực hiện hành động này?')) {
            e.preventDefault();
        }
    });
    
    // Form validation
    $('form').on('submit', function() {
        const requiredFields = $(this).find('[required]');
        let isValid = true;
        
        requiredFields.each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showAlert('Vui lòng điền đầy đủ thông tin bắt buộc', 'danger');
        }
    });
    
    // Real-time character count
    $('textarea[maxlength]').on('input', function() {
        const maxLength = $(this).attr('maxlength');
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        let counter = $(this).siblings('.char-counter');
        if (counter.length === 0) {
            counter = $('<small class="char-counter text-muted"></small>');
            $(this).after(counter);
        }
        
        counter.text(`${currentLength}/${maxLength} ký tự`);
        
        if (remaining < 50) {
            counter.addClass('text-warning');
        } else {
            counter.removeClass('text-warning');
        }
        
        if (remaining < 0) {
            counter.addClass('text-danger');
        } else {
            counter.removeClass('text-danger');
        }
    });
    
    // Image preview
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        const preview = $(this).siblings('.image-preview');
        
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview.length === 0) {
                    $(this).after('<div class="image-preview mt-2"><img class="img-thumbnail" style="max-width: 200px;"></div>');
                }
                $(this).siblings('.image-preview').find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Platform selection handler
    $('#platform').on('change', function() {
        const platform = $(this).val();
        const fanpageSelect = $('#fanpageSelect');
        const zaloSelect = $('#zaloSelect');
        
        if (platform === 'facebook') {
            fanpageSelect.show();
            zaloSelect.hide();
            $('#fanpage_id').prop('required', true);
            $('#zalo_id').prop('required', false);
        } else if (platform === 'zalo') {
            fanpageSelect.hide();
            zaloSelect.show();
            $('#fanpage_id').prop('required', false);
            $('#zalo_id').prop('required', true);
        } else if (platform === 'both') {
            fanpageSelect.show();
            zaloSelect.show();
            $('#fanpage_id').prop('required', true);
            $('#zalo_id').prop('required', true);
        } else {
            fanpageSelect.hide();
            zaloSelect.hide();
            $('#fanpage_id').prop('required', false);
            $('#zalo_id').prop('required', false);
        }
    });
    
    // Schedule type handler
    $('#schedule_type').on('change', function() {
        const scheduleType = $(this).val();
        const scheduleDate = $('#scheduleDate');
        const scheduleDays = $('#scheduleDays');
        
        if (scheduleType === 'once') {
            scheduleDate.show();
            scheduleDays.hide();
            $('#schedule_date').prop('required', true);
        } else if (scheduleType === 'weekly') {
            scheduleDate.hide();
            scheduleDays.show();
            $('#schedule_date').prop('required', false);
        } else {
            scheduleDate.hide();
            scheduleDays.hide();
            $('#schedule_date').prop('required', false);
        }
    });
    
    // Set minimum date for date inputs
    $('input[type="date"]').attr('min', new Date().toISOString().split('T')[0]);
    
    // Set minimum datetime for datetime inputs
    $('input[type="datetime-local"]').attr('min', new Date().toISOString().slice(0, 16));
    
    // Loading state for forms
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
                 .html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
        
        // Re-enable after 10 seconds as fallback
        setTimeout(function() {
            submitBtn.prop('disabled', false).html(originalText);
        }, 10000);
    });
    
    // Refresh data every 30 seconds
    setInterval(function() {
        refreshStats();
    }, 30000);
    
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
});

// Utility functions
function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.container-fluid .row .col-md-9').prepend(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert').first().fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

function refreshStats() {
    // This would typically make an AJAX call to refresh stats
    // For now, we'll just add a visual indicator
    $('.stats-refresh').addClass('fa-spin');
    
    setTimeout(function() {
        $('.stats-refresh').removeClass('fa-spin');
    }, 1000);
}

function initializeCharts() {
    // Chart initialization code would go here
    // This is a placeholder for future chart implementations
}

// API functions
function syncFanpage(fanpageId) {
    showAlert('Đang đồng bộ fanpage...', 'info');
    
    // Simulate API call
    setTimeout(function() {
        showAlert('Đồng bộ fanpage thành công', 'success');
    }, 2000);
}

function syncZalo(zaloId) {
    showAlert('Đang đồng bộ Zalo OA...', 'info');
    
    // Simulate API call
    setTimeout(function() {
        showAlert('Đồng bộ Zalo OA thành công', 'success');
    }, 2000);
}

function deleteFanpage(fanpageId) {
    if (confirm('Bạn có chắc chắn muốn xóa fanpage này?')) {
        $('#deleteFanpageId').val(fanpageId);
        $('#deleteForm').submit();
    }
}

function deleteZalo(zaloId) {
    if (confirm('Bạn có chắc chắn muốn xóa Zalo OA này?')) {
        $('#deleteZaloId').val(zaloId);
        $('#deleteZaloForm').submit();
    }
}

// Post management functions
function editPost(postId) {
    // Implementation for editing posts
    showAlert('Tính năng chỉnh sửa bài đăng sẽ được triển khai', 'info');
}

function deletePost(postId) {
    if (confirm('Bạn có chắc chắn muốn xóa bài đăng này?')) {
        // Implementation for deleting posts
        showAlert('Xóa bài đăng thành công', 'success');
    }
}

function schedulePost(postId) {
    // Implementation for scheduling posts
    showAlert('Tính năng lên lịch bài đăng sẽ được triển khai', 'info');
}

// Schedule management functions
function editSchedule(scheduleId) {
    // Implementation for editing schedules
    showAlert('Tính năng chỉnh sửa lịch sẽ được triển khai', 'info');
}

function deleteSchedule(scheduleId) {
    if (confirm('Bạn có chắc chắn muốn xóa lịch này?')) {
        // Implementation for deleting schedules
        showAlert('Xóa lịch thành công', 'success');
    }
}

function toggleSchedule(scheduleId, isActive) {
    // Implementation for toggling schedule status
    const status = isActive ? 'tạm dừng' : 'kích hoạt';
    showAlert(`Đã ${status} lịch thành công`, 'success');
}

// Export functions
function exportData(type) {
    showAlert(`Đang xuất dữ liệu ${type}...`, 'info');
    
    // Simulate export process
    setTimeout(function() {
        showAlert(`Xuất dữ liệu ${type} thành công`, 'success');
    }, 2000);
}

// Search and filter functions
function searchPosts(query) {
    $('.post-item').each(function() {
        const content = $(this).text().toLowerCase();
        if (content.includes(query.toLowerCase())) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function filterByPlatform(platform) {
    $('.post-item').each(function() {
        if (platform === 'all' || $(this).data('platform') === platform) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function filterByStatus(status) {
    $('.post-item').each(function() {
        if (status === 'all' || $(this).data('status') === status) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Keyboard shortcuts
$(document).keydown(function(e) {
    // Ctrl + N: New post
    if (e.ctrlKey && e.keyCode === 78) {
        e.preventDefault();
        $('#createPostModal').modal('show');
    }
    
    // Ctrl + S: Save (for forms)
    if (e.ctrlKey && e.keyCode === 83) {
        e.preventDefault();
        $('form:visible').first().submit();
    }
    
    // Escape: Close modals
    if (e.keyCode === 27) {
        $('.modal.show').modal('hide');
    }
});

// Responsive table handling
function makeTableResponsive() {
    $('.table-responsive').each(function() {
        if ($(this).find('table').width() > $(this).width()) {
            $(this).addClass('table-scroll');
        }
    });
}

$(window).resize(function() {
    makeTableResponsive();
});

// Initialize on page load
$(document).ready(function() {
    makeTableResponsive();
});