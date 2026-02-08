@extends('admin.layout')

@section('title', 'User Management')
@section('page-icon')<i class="fa-solid fa-users"></i>@endsection
@section('page-title', 'User Management')

@section('styles')
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show {
            display: flex;
        }
        .modal-box {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .modal-box h3 {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        .modal-box .form-group {
            margin-bottom: 1rem;
        }
        .modal-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.25rem;
        }
        .modal-actions .btn { flex: 1; }
        .loading td { color: #666; }
        .empty-state { text-align: center; padding: 2rem; color: #666; }
        .empty-state i { font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5; }
@endsection

@section('content')
<!-- Add User Form -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-user-plus"></i> Add User
    </div>
    <form id="addUserForm" onsubmit="addUser(event)">
        <div class="form-row">
            <div class="form-group">
                <label for="add_name">Name</label>
                <input type="text" id="add_name" name="name" required placeholder="Full name">
            </div>
            <div class="form-group">
                <label for="add_email">Email</label>
                <input type="email" id="add_email" name="email" required placeholder="user@example.com">
            </div>
            <div class="form-group">
                <label for="add_password">Password</label>
                <input type="password" id="add_password" name="password" required placeholder="••••••••">
            </div>
            <div class="form-group">
                <label for="add_password_confirmation">Confirm Password</label>
                <input type="password" id="add_password_confirmation" name="password_confirmation" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add User
            </button>
        </div>
    </form>
</div>

<!-- Users List -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-list"></i> All Users
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersTable">
                <tr>
                    <td colspan="4" class="loading">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading users...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal-overlay" onclick="if (event.target === this) closeEditModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3><i class="fa-solid fa-user-edit"></i> Edit User</h3>
        <form id="editUserForm" onsubmit="updateUser(event)">
            <input type="hidden" id="edit_id" name="id">
            <div class="form-group">
                <label for="edit_name">Name</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Email</label>
                <input type="email" id="edit_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="edit_password">New password (leave blank to keep)</label>
                <input type="password" id="edit_password" name="password" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label for="edit_password_confirmation">Confirm new password</label>
                <input type="password" id="edit_password_confirmation" name="password_confirmation" placeholder="••••••••">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
    });

    async function loadUsers() {
        const tbody = document.getElementById('usersTable');
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="loading">
                    <i class="fa-solid fa-spinner fa-spin"></i> Loading users...
                </td>
            </tr>
        `;

        try {
            const response = await fetch('/api/users');
            const data = await response.json();

            if (data.success && data.users && data.users.length > 0) {
                const currentUserId = {{ auth()->id() }};
                tbody.innerHTML = data.users.map(user => {
                    const isSelf = user.id === currentUserId;
                    const deleteBtn = isSelf
                        ? '<span class="btn btn-secondary btn-sm" disabled title="Cannot delete yourself"><i class="fa-solid fa-trash"></i></span>'
                        : `<button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>`;
                    return `<tr>
                        <td><strong>${escapeHtml(user.name)}</strong></td>
                        <td>${escapeHtml(user.email)}</td>
                        <td>${formatDate(user.created_at)}</td>
                        <td class="actions">
                            <button class="btn btn-secondary btn-sm" onclick="openEditModal(${user.id}, '${escapeHtml(user.name)}', '${escapeHtml(user.email)}')" title="Edit"><i class="fa-solid fa-pen"></i></button>
                            ${deleteBtn}
                        </td>
                    </tr>`;
                }).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fa-solid fa-users-slash"></i>
                            <p>No users yet</p>
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error loading users:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="empty-state">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        <p>Error loading users</p>
                    </td>
                </tr>
            `;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/'/g, '&#39;').replace(/"/g, '&quot;');
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    async function addUser(event) {
        event.preventDefault();
        const form = event.target;
        const payload = {
            name: form.name.value,
            email: form.email.value,
            password: form.password.value,
            password_confirmation: form.password_confirmation.value
        };

        try {
            const response = await fetch('/api/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (data.success) {
                showToast('User created successfully', 'success');
                form.reset();
                loadUsers();
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error creating user');
                showToast(msg, 'error');
            }
        } catch (error) {
            console.error('Error adding user:', error);
            showToast('Error creating user', 'error');
        }
    }

    function openEditModal(id, name, email) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';
        document.getElementById('editModal').classList.add('show');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('show');
    }

    async function updateUser(event) {
        event.preventDefault();
        const form = event.target;
        const id = form.id.value;
        const payload = {
            name: form.name.value,
            email: form.email.value
        };
        if (form.password.value) {
            payload.password = form.password.value;
            payload.password_confirmation = form.password_confirmation.value;
        }

        try {
            const response = await fetch(`/api/users/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (data.success) {
                showToast('User updated successfully', 'success');
                closeEditModal();
                loadUsers();
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error updating user');
                showToast(msg, 'error');
            }
        } catch (error) {
            console.error('Error updating user:', error);
            showToast('Error updating user', 'error');
        }
    }

    async function deleteUser(id) {
        if (!confirm('Are you sure you want to delete this user?')) {
            return;
        }
        try {
            const response = await fetch(`/api/users/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await response.json();

            if (data.success) {
                showToast('User deleted successfully', 'success');
                loadUsers();
            } else {
                showToast(data.message || 'Error deleting user', 'error');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            showToast('Error deleting user', 'error');
        }
    }
</script>
@endsection
