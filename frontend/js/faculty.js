/**
 * Smart Campus - Faculty Interactive Operations
 * Handles Attendance Updates, Marks Entry, Assignment Creation, and Notice Publishing
 */

document.addEventListener('DOMContentLoaded', function () {
    // Helper: Form submit handler with toast/alert
    function setupAjaxForm(formId, endpoint, successCallback) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            try {
                const formData = new FormData(form);
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    alert('Success: ' + result.message);
                    form.reset();
                    if (successCallback) successCallback(result);
                    window.location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Operation failed.'));
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred while processing request.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    setupAjaxForm('attendanceForm', '../../backend/faculty/update_attendance.php');
    setupAjaxForm('marksForm', '../../backend/faculty/update_marks.php');
    setupAjaxForm('assignmentForm', '../../backend/faculty/create_assignment.php');
    setupAjaxForm('noticeForm', '../../backend/faculty/post_notice.php');
});
