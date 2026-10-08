/**
 * Smart Campus - Admin Operations Script
 * Handles Add Student, Add Faculty, Add Subject, and Delete User operations
 */

document.addEventListener('DOMContentLoaded', function () {
    // Helper: Form submit handler with feedback
    function setupAdminForm(formId, endpoint) {
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
                    window.location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Operation failed.'));
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred while saving.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    setupAdminForm('addUserForm', '../../backend/admin/add_user.php');
    setupAdminForm('addSubjectForm', '../../backend/admin/add_subject.php');
});

// Delete User function
async function deleteUser(userId, userName) {
    let confirmed = false;
    if (typeof window.showInteractiveConfirm === 'function') {
        confirmed = await window.showInteractiveConfirm({
            title: 'Remove User Account',
            subtitle: `Are you sure you want to remove this account from the campus system?`,
            name: userName,
            role: 'User Account',
            meta: `ID: #${userId}`,
            impact1: 'Related student attendance, marks, and historical submissions will be removed.',
            impact2: 'This action is irreversible and permanent.',
            confirmText: 'Remove User',
            cancelText: 'Keep User'
        });
    } else {
        confirmed = confirm(`Are you sure you want to remove user "${userName}"? This will also remove related attendance and marks.`);
    }

    if (!confirmed) return;

    try {
        const formData = new FormData();
        formData.append('user_id', userId);

        const response = await fetch('../../backend/admin/delete_user.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            if (typeof showToast === 'function') {
                showToast('User removed successfully.', 'success');
            } else {
                alert('User removed successfully.');
            }
            setTimeout(() => window.location.reload(), 800);
        } else {
            if (typeof showToast === 'function') {
                showToast(result.message || 'Failed to delete user.', 'danger');
            } else {
                alert('Error: ' + (result.message || 'Failed to delete user.'));
            }
        }
    } catch (err) {
        console.error(err);
        if (typeof showToast === 'function') {
            showToast('Server communication error.', 'danger');
        } else {
            alert('Server communication error.');
        }
    }
}
