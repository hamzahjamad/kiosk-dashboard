@extends('admin.layout')

@section('title', 'Prayer Times Settings')
@section('page-icon')<i class="fa-solid fa-mosque"></i>@endsection
@section('page-title', 'Prayer Times Settings')

@section('header-actions')
<button class="btn btn-secondary" onclick="refreshTimes()">
    <i class="fa-solid fa-sync"></i> Refresh from API
</button>
@endsection

@section('content')
<!-- Location Settings -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-location-dot"></i> Location & Calculation Method
    </div>
    <form id="settingsForm" onsubmit="saveSettings(event)">
        <div class="form-row">
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" required placeholder="e.g., Labuan">
            </div>
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" required placeholder="e.g., Malaysia">
            </div>
            <div class="form-group" style="flex: 2;">
                <label for="method">Calculation Method</label>
                <select id="method" name="method" onchange="updateMethodName()">
                    <option value="3" data-name="JAKIM (Malaysia)">JAKIM (Malaysia)</option>
                    <option value="1" data-name="University of Islamic Sciences, Karachi">University of Islamic Sciences, Karachi</option>
                    <option value="2" data-name="Islamic Society of North America (ISNA)">Islamic Society of North America (ISNA)</option>
                    <option value="4" data-name="Umm Al-Qura University, Makkah">Umm Al-Qura University, Makkah</option>
                    <option value="5" data-name="Egyptian General Authority of Survey">Egyptian General Authority of Survey</option>
                    <option value="7" data-name="Institute of Geophysics, University of Tehran">Institute of Geophysics, University of Tehran</option>
                    <option value="8" data-name="Gulf Region">Gulf Region</option>
                    <option value="9" data-name="Kuwait">Kuwait</option>
                    <option value="10" data-name="Qatar">Qatar</option>
                    <option value="11" data-name="Majlis Ugama Islam Singapura">Majlis Ugama Islam Singapura</option>
                </select>
                <input type="hidden" id="method_name" name="method_name" value="JAKIM (Malaysia)">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Save
            </button>
        </div>
    </form>
</div>

<!-- Prayer Times Table -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-clock"></i> Today's Prayer Times
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Prayer</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="prayerTable">
                <tr>
                    <td colspan="3" class="loading">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading prayer times...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentSettings = null;

    const prayerNames = {
        subuh: 'Subuh (Fajr)',
        syuruk: 'Syuruk (Sunrise)',
        zohor: 'Zohor (Dhuhr)',
        asar: 'Asar (Asr)',
        maghrib: 'Maghrib',
        isyak: 'Isyak (Isha)'
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadSettings();
    });

    async function loadSettings() {
        try {
            const response = await fetch('/api/prayer/settings');
            const data = await response.json();

            if (data.success) {
                currentSettings = data.settings;
                
                // Populate form
                document.getElementById('city').value = currentSettings.city;
                document.getElementById('country').value = currentSettings.country;
                document.getElementById('method').value = currentSettings.method;
                document.getElementById('method_name').value = currentSettings.method_name;
                
                renderPrayerTable();
            }
        } catch (error) {
            console.error('Error loading settings:', error);
            showToast('Error loading settings', 'error');
        }
    }

    function renderPrayerTable() {
        const tbody = document.getElementById('prayerTable');
        const prayers = ['subuh', 'syuruk', 'zohor', 'asar', 'maghrib', 'isyak'];
        const times = currentSettings.cached_times || {};

        tbody.innerHTML = prayers.map(prayer => {
            const isVisible = currentSettings[`show_${prayer}`];
            const time = times[prayer] || '--:--';
            
            return `
                <tr class="${isVisible ? '' : 'row-hidden'}">
                    <td><strong>${prayerNames[prayer]}</strong></td>
                    <td><span class="badge badge-time">${time}</span></td>
                    <td class="actions">
                        <button class="btn ${isVisible ? 'btn-success' : 'btn-secondary'} btn-sm" 
                                onclick="togglePrayer('${prayer}')"
                                title="${isVisible ? 'Click to hide' : 'Click to show'}">
                            <i class="fa-solid ${isVisible ? 'fa-eye' : 'fa-eye-slash'}"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function togglePrayer(prayer) {
        const settingKey = `show_${prayer}`;
        const newValue = !currentSettings[settingKey];
        
        // Prepare update data
        const formData = {
            city: currentSettings.city,
            country: currentSettings.country,
            method: currentSettings.method,
            method_name: currentSettings.method_name,
            show_subuh: currentSettings.show_subuh,
            show_syuruk: currentSettings.show_syuruk,
            show_zohor: currentSettings.show_zohor,
            show_asar: currentSettings.show_asar,
            show_maghrib: currentSettings.show_maghrib,
            show_isyak: currentSettings.show_isyak,
        };
        formData[settingKey] = newValue;

        try {
            const response = await fetch('/api/prayer/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                currentSettings = data.settings;
                renderPrayerTable();
                showToast(`${prayerNames[prayer]} is now ${newValue ? 'visible' : 'hidden'}`, 'success');
            } else {
                showToast(data.message || 'Error updating', 'error');
            }
        } catch (error) {
            console.error('Error toggling prayer:', error);
            showToast('Error updating', 'error');
        }
    }

    async function saveSettings(event) {
        event.preventDefault();

        const formData = {
            city: document.getElementById('city').value,
            country: document.getElementById('country').value,
            method: parseInt(document.getElementById('method').value),
            method_name: document.getElementById('method_name').value,
            show_subuh: currentSettings?.show_subuh ?? true,
            show_syuruk: currentSettings?.show_syuruk ?? true,
            show_zohor: currentSettings?.show_zohor ?? true,
            show_asar: currentSettings?.show_asar ?? true,
            show_maghrib: currentSettings?.show_maghrib ?? true,
            show_isyak: currentSettings?.show_isyak ?? true,
        };

        try {
            const response = await fetch('/api/prayer/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                currentSettings = data.settings;
                showToast('Settings saved! Refreshing times...', 'success');
                
                // If location changed, refresh times
                await refreshTimes();
            } else {
                showToast(data.message || 'Error saving settings', 'error');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            showToast('Error saving settings', 'error');
        }
    }

    async function refreshTimes() {
        try {
            const response = await fetch('/api/prayer/refresh', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast('Prayer times refreshed', 'success');
                loadSettings();
            } else {
                showToast(data.message || 'Error refreshing times', 'error');
            }
        } catch (error) {
            console.error('Error refreshing times:', error);
            showToast('Error refreshing times', 'error');
        }
    }

    function updateMethodName() {
        const select = document.getElementById('method');
        const selectedOption = select.options[select.selectedIndex];
        document.getElementById('method_name').value = selectedOption.dataset.name;
    }
</script>
@endsection
