@extends('admin.layout')

@section('title', 'Weather Settings')
@section('page-icon')<i class="fa-solid fa-cloud-sun"></i>@endsection
@section('page-title', 'Weather Settings')

@section('header-actions')
<button class="btn btn-secondary" onclick="refreshWeather()">
    <i class="fa-solid fa-sync"></i> Refresh from API
</button>
@endsection

@section('content')
<!-- Location Settings -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-location-dot"></i> Location & Unit
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
            <div class="form-group">
                <label for="unit">Temperature Unit</label>
                <select id="unit" name="unit">
                    <option value="celsius">Celsius (°C)</option>
                    <option value="fahrenheit">Fahrenheit (°F)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Save
            </button>
        </div>
    </form>
</div>

<!-- Display Options -->
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-eye"></i> Display Options
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Element</th>
                    <th>Preview</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="optionsTable">
                <tr>
                    <td colspan="3" class="loading">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading settings...
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
    let currentWeather = null;

    const displayOptions = [
        { key: 'show_temperature', name: 'Temperature', icon: 'fa-temperature-high' },
        { key: 'show_description', name: 'Weather Description', icon: 'fa-cloud' },
        { key: 'show_humidity', name: 'Humidity', icon: 'fa-droplet' },
        { key: 'show_wind', name: 'Wind Speed', icon: 'fa-wind' },
    ];

    document.addEventListener('DOMContentLoaded', () => {
        loadSettings();
    });

    async function loadSettings() {
        try {
            const [settingsRes, weatherRes] = await Promise.all([
                fetch('/api/weather/settings'),
                fetch('/api/weather/current')
            ]);
            
            const settingsData = await settingsRes.json();
            const weatherData = await weatherRes.json();

            if (settingsData.success) {
                currentSettings = settingsData.settings;
                
                // Populate form
                document.getElementById('city').value = currentSettings.city;
                document.getElementById('country').value = currentSettings.country;
                document.getElementById('unit').value = currentSettings.unit;
            }

            if (weatherData.success) {
                currentWeather = weatherData.weather;
            }

            renderOptionsTable();
        } catch (error) {
            console.error('Error loading settings:', error);
            showToast('Error loading settings', 'error');
        }
    }

    function renderOptionsTable() {
        const tbody = document.getElementById('optionsTable');

        tbody.innerHTML = displayOptions.map(option => {
            const isVisible = currentSettings[option.key];
            let preview = '--';

            if (currentWeather) {
                switch (option.key) {
                    case 'show_temperature':
                        preview = `${currentWeather.temperature}${currentWeather.unit}`;
                        break;
                    case 'show_description':
                        preview = currentWeather.description;
                        break;
                    case 'show_humidity':
                        preview = currentWeather.humidity;
                        break;
                    case 'show_wind':
                        preview = currentWeather.wind;
                        break;
                }
            }

            return `
                <tr class="${isVisible ? '' : 'row-hidden'}">
                    <td>
                        <i class="fa-solid ${option.icon}" style="margin-right: 0.5rem; color: #666;"></i>
                        <strong>${option.name}</strong>
                    </td>
                    <td><span class="badge badge-time">${preview}</span></td>
                    <td class="actions">
                        <button class="btn ${isVisible ? 'btn-success' : 'btn-secondary'} btn-sm" 
                                onclick="toggleOption('${option.key}')"
                                title="${isVisible ? 'Click to hide' : 'Click to show'}">
                            <i class="fa-solid ${isVisible ? 'fa-eye' : 'fa-eye-slash'}"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function toggleOption(key) {
        const newValue = !currentSettings[key];
        
        const formData = {
            city: currentSettings.city,
            country: currentSettings.country,
            unit: currentSettings.unit,
            show_temperature: currentSettings.show_temperature,
            show_description: currentSettings.show_description,
            show_humidity: currentSettings.show_humidity,
            show_wind: currentSettings.show_wind,
        };
        formData[key] = newValue;

        try {
            const response = await fetch('/api/weather/settings', {
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
                renderOptionsTable();
                
                const optionName = displayOptions.find(o => o.key === key)?.name || key;
                showToast(`${optionName} is now ${newValue ? 'visible' : 'hidden'}`, 'success');
            } else {
                showToast(data.message || 'Error updating', 'error');
            }
        } catch (error) {
            console.error('Error toggling option:', error);
            showToast('Error updating', 'error');
        }
    }

    async function saveSettings(event) {
        event.preventDefault();

        const formData = {
            city: document.getElementById('city').value,
            country: document.getElementById('country').value,
            unit: document.getElementById('unit').value,
            show_temperature: currentSettings?.show_temperature ?? true,
            show_description: currentSettings?.show_description ?? true,
            show_humidity: currentSettings?.show_humidity ?? false,
            show_wind: currentSettings?.show_wind ?? false,
        };

        try {
            const response = await fetch('/api/weather/settings', {
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
                showToast('Settings saved! Refreshing weather...', 'success');
                
                // Refresh weather with new location
                await refreshWeather();
            } else {
                showToast(data.message || 'Error saving settings', 'error');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            showToast('Error saving settings', 'error');
        }
    }

    async function refreshWeather() {
        try {
            const response = await fetch('/api/weather/refresh', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                currentWeather = data.weather;
                renderOptionsTable();
                showToast('Weather refreshed', 'success');
            } else {
                showToast(data.message || 'Error refreshing weather', 'error');
            }
        } catch (error) {
            console.error('Error refreshing weather:', error);
            showToast('Error refreshing weather', 'error');
        }
    }
</script>
@endsection
