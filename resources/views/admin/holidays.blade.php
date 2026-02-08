@extends('admin.layout')

@section('title', 'Holiday Management')
@section('page-icon')<i class="fa-solid fa-calendar-days"></i>@endsection
@section('page-title', 'Holiday Management')

@section('header-actions')
<button class="btn btn-secondary" onclick="syncFromApi()">
    <i class="fa-solid fa-sync"></i> Sync from API
</button>
@endsection

@section('content')
<!-- Add Holiday Form -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-plus"></i> Add Custom Holiday
    </div>
    <form id="addHolidayForm" onsubmit="addHoliday(event)">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Holiday Name</label>
                <input type="text" id="name" name="name" required placeholder="e.g., Family Birthday">
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
                <label for="notes">Notes (optional)</label>
                <input type="text" id="notes" name="notes" placeholder="Any additional notes...">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add
            </button>
        </div>
    </form>
</div>

<!-- Holidays List -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-list"></i> All Holidays
    </div>
    
    <div class="year-filter">
        <label for="yearFilter">Year:</label>
        <select id="yearFilter" onchange="loadHolidays()">
            <option value="2025">2025</option>
            <option value="2026" selected>2026</option>
            <option value="2027">2027</option>
        </select>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="holidaysTable">
                <tr>
                    <td colspan="5" class="loading">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading holidays...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadHolidays();
    });

    async function loadHolidays() {
        const year = document.getElementById('yearFilter').value;
        const tbody = document.getElementById('holidaysTable');
        
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="loading">
                    <i class="fa-solid fa-spinner fa-spin"></i> Loading holidays...
                </td>
            </tr>
        `;

        try {
            const response = await fetch(`/api/holidays/all?year=${year}`);
            const data = await response.json();

            if (data.success && data.holidays.length > 0) {
                tbody.innerHTML = data.holidays.map(holiday => {
                    const visibleBtn = `<button class="btn ${holiday.is_visible ? 'btn-success' : 'btn-secondary'} btn-sm" onclick="toggleVisibility(${holiday.id})" title="${holiday.is_visible ? 'Click to hide' : 'Click to show'}"><i class="fa-solid ${holiday.is_visible ? 'fa-eye' : 'fa-eye-slash'}"></i></button>`;
                    const deleteBtn = holiday.source === 'manual' ? `<button class="btn btn-danger btn-sm" onclick="deleteHoliday(${holiday.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>` : '';
                    
                    return `<tr class="${holiday.is_visible ? '' : 'row-hidden'}">
                        <td>${formatDate(holiday.date)}</td>
                        <td><strong>${holiday.name}</strong></td>
                        <td><span class="badge badge-${holiday.type}">${holiday.type}</span></td>
                        <td><span class="badge badge-${holiday.source === 'calendarific' ? 'api' : 'manual'}">${holiday.source === 'calendarific' ? 'API' : 'Manual'}</span></td>
                        <td class="actions">${visibleBtn}${deleteBtn}</td>
                    </tr>`;
                }).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <p>No holidays found for ${year}</p>
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error loading holidays:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        <p>Error loading holidays</p>
                    </td>
                </tr>
            `;
        }
    }

    async function addHoliday(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = {
            name: form.name.value,
            date: form.date.value,
            notes: form.notes.value
        };

        try {
            const response = await fetch('/api/holidays', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                showToast('Holiday added successfully', 'success');
                form.reset();
                loadHolidays();
            } else {
                showToast(data.message || 'Error adding holiday', 'error');
            }
        } catch (error) {
            console.error('Error adding holiday:', error);
            showToast('Error adding holiday', 'error');
        }
    }

    async function toggleVisibility(id) {
        try {
            const response = await fetch(`/api/holidays/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message, 'success');
                loadHolidays();
            } else {
                showToast(data.message || 'Error toggling visibility', 'error');
            }
        } catch (error) {
            console.error('Error toggling visibility:', error);
            showToast('Error toggling visibility', 'error');
        }
    }

    async function deleteHoliday(id) {
        if (!confirm('Are you sure you want to delete this holiday?')) {
            return;
        }

        try {
            const response = await fetch(`/api/holidays/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast('Holiday deleted successfully', 'success');
                loadHolidays();
            } else {
                showToast(data.message || 'Error deleting holiday', 'error');
            }
        } catch (error) {
            console.error('Error deleting holiday:', error);
            showToast('Error deleting holiday', 'error');
        }
    }

    async function syncFromApi() {
        showToast('Syncing holidays from API...', 'success');

        try {
            const response = await fetch('/api/holidays/sync', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message, 'success');
                loadHolidays();
            } else {
                showToast(data.message || 'Error syncing holidays', 'error');
            }
        } catch (error) {
            console.error('Error syncing holidays:', error);
            showToast('Error syncing holidays', 'error');
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }
</script>
@endsection
