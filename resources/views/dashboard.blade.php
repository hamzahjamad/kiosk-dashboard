<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Dashboard</title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            background: #000;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #ffffff;
            overflow: hidden;
            cursor: none;
        }
        /* Background Slideshow */
        .slideshow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        .slideshow-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 2s ease-in-out;
        }
        .slideshow-image.active {
            opacity: 1;
        }
        /* Dark overlay for better text readability */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2;
        }
        .kiosk-container {
            position: fixed;
            top: 50%;
            left: 50%;
            text-align: center;
            padding: 2rem;
            z-index: 3;
            transition: transform 3s ease-in-out, color 5s ease-in-out;
        }
        .time-display {
            font-size: 16rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
            text-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
}
        .time-display .seconds {
            font-size: 8rem;
            font-weight: 300;
            opacity: 0.7;
            vertical-align: super;
        }
        .date-display {
            font-size: 4rem;
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 0.3rem;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.5);
        }
        .day-display {
            font-size: 3rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            color: #ffd369;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.5);
            transition: color 5s ease-in-out;
            margin-bottom: 1rem;
        }
        /* Holiday Notice */
        .holiday-notice {
            font-size: 1.8rem;
            padding: 0.75rem 1.5rem;
            margin-bottom: 1.5rem;
            background: rgba(255, 215, 105, 0.2);
            border-radius: 0.75rem;
            display: none;
        }
        .holiday-notice.visible {
            display: inline-block;
        }
            .holiday-notice i {
            margin-right: 0.5rem;
        }
        /* Info panels container */
        .info-panels {
            display: flex;
            flex-direction: row;
            align-items: stretch;
            justify-content: center;
            gap: 3rem;
            margin-top: 2rem;
        }
        /* Weather Panel */
        .weather-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1.5rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            backdrop-filter: blur(10px);
            text-align: center;
        }
        .weather-icon {
            font-size: 3.5rem;
            margin-bottom: 0.3rem;
        }
        .weather-main {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
        }
        .weather-temp {
            font-size: 2.5rem;
            font-weight: 600;
        }
        .weather-desc {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .weather-extra {
            font-size: 1rem;
            opacity: 0.8;
            text-transform: capitalize;
        }
        .weather-location {
            font-size: 1rem;
            opacity: 0.6;
        }
        /* Prayer Times Panel */
        .prayer-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            padding: 1.5rem 2.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            backdrop-filter: blur(10px);
        }
        .prayer-title {
            font-size: 1.4rem;
            opacity: 0.7;
            margin-bottom: 1rem;
            text-align: left;
            padding-left: 1.5rem;
        }
        .prayer-items {
            display: flex;
            gap: 2rem;
            justify-content: center;
        }
        .prayer-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem 1rem;
        }
        .prayer-item.next {
            color: #ffd369;
        }
        .prayer-item.next .prayer-time {
            background: rgba(255, 211, 105, 0.2);
            border-radius: 0.5rem;
            padding: 0.2rem 0.6rem;
        }
        .prayer-name {
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .prayer-time {
            font-size: 1.8rem;
            font-weight: 600;
        }
        /* Pulse animation for colon */
        .colon {
            animation: pulse 1s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        /* Responsive */
        @media (max-width: 1200px) {
            .time-display {
                font-size: 9rem;
            }
            .time-display .seconds {
                font-size: 4.5rem;
            }
            .date-display {
                font-size: 3rem;
            }
            .day-display {
                font-size: 2.2rem;
            }
            .prayer-items {
                gap: 1.2rem;
            }
            .prayer-name {
                font-size: 0.9rem;
            }
            .prayer-time {
                font-size: 1.4rem;
            }
            .weather-temp {
                font-size: 2.2rem;
            }
            .weather-desc {
                font-size: 1.1rem;
            }
        }
        @media (max-width: 768px) {
            .time-display {
                font-size: 5rem;
            }
            .time-display .seconds {
                font-size: 2.5rem;
            }
            .date-display {
                font-size: 2rem;
            }
            .day-display {
                font-size: 1.5rem;
            }
            .info-panels {
                gap: 1rem;
                width: 100%;
                padding: 0 1rem;
            }
            .prayer-panel {
                padding: 1rem;
            }
            .prayer-items {
                gap: 0.5rem;
                flex-wrap: wrap;
                justify-content: space-around;
            }
            .prayer-item {
                padding: 0.3rem 0.5rem;
            }
            .prayer-name {
                font-size: 0.7rem;
            }
            .prayer-time {
                font-size: 1rem;
            }
            .info-panels {
                flex-direction: column;
                gap: 1.5rem;
            }
            .weather-panel {
                padding: 1rem 1.5rem;
            }
            .weather-icon {
                font-size: 2.5rem;
            }
            .weather-temp {
                font-size: 2rem;
            }
            .weather-desc {
                font-size: 1rem;
            }
            .holiday-notice {
                font-size: 1.2rem;
            }
        }
        </style>
</head>
<body>
    <!-- Background Slideshow -->
    <div class="slideshow" id="slideshow"></div>
    <!-- Dark Overlay -->
    <div class="overlay" id="overlay"></div>
    <div class="kiosk-container">
        <div class="time-display">
            <span id="hours">00</span><span class="colon">:</span><span id="minutes">00</span><span class="seconds" id="seconds">00</span>
        </div>
        <div class="date-display" id="date-text">Loading...</div>
        <div class="day-display" id="day-text">Loading...</div>
        <div class="holiday-notice" id="holiday-notice">
            <i class="fa-solid fa-calendar-check"></i>
            <span id="holiday-text"></span>
        </div>
        <div class="info-panels">
            <!-- Prayer Times Panel -->
            <div class="prayer-panel">
                <div class="prayer-title"><i class="fa-solid fa-mosque"></i> Prayer Times</div>
                <div class="prayer-items">
                    <div class="prayer-item" id="prayer-subuh">
                        <span class="prayer-name">Subuh</span>
                        <span class="prayer-time">--:--</span>
                    </div>
                    <div class="prayer-item" id="prayer-syuruk">
                        <span class="prayer-name">Syuruk</span>
                        <span class="prayer-time">--:--</span>
                    </div>
                    <div class="prayer-item" id="prayer-zohor">
                        <span class="prayer-name">Zohor</span>
                        <span class="prayer-time">--:--</span>
                    </div>
                    <div class="prayer-item" id="prayer-asar">
                        <span class="prayer-name">Asar</span>
                        <span class="prayer-time">--:--</span>
                    </div>
                    <div class="prayer-item" id="prayer-maghrib">
                        <span class="prayer-name">Maghrib</span>
                        <span class="prayer-time">--:--</span>
                    </div>
                    <div class="prayer-item" id="prayer-isyak">
                        <span class="prayer-name">Isyak</span>
                        <span class="prayer-time">--:--</span>
                    </div>
                </div>
            </div>
            <!-- Weather Panel -->
            <div class="weather-panel">
                <div class="weather-icon" id="weather-icon"><i class="fa-solid fa-cloud-sun"></i></div>
                <div class="weather-main">
                    <div class="weather-temp" id="weather-temp">--°C</div>
                    <div class="weather-desc" id="weather-desc">Loading...</div>
                </div>
                <div class="weather-extra" id="weather-humidity" style="display: none;"></div>
                <div class="weather-extra" id="weather-wind" style="display: none;"></div>
            </div>
        </div>
    </div>
    <script>
        // DateTime Update
        function updateDateTime() {
            const now = new Date();
            // Time
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('hours').textContent = hours;
            document.getElementById('minutes').textContent = minutes;
            document.getElementById('seconds').textContent = seconds;
            // Date
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('date-text').textContent = now.toLocaleDateString('en-US', options);
            // Day
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            document.getElementById('day-text').textContent = days[now.getDay()];
            // Highlight next prayer
            highlightNextPrayer();
        }
        // Background Slideshow
        let slides = [];
        let currentSlide = 0;
        let slideInterval = 10000;
        let slideIntervalId = null;
        async function initBackgrounds() {
            try {
                const response = await fetch('/api/backgrounds');
                const data = await response.json();
                if (data.success) {
                    const slideshow = document.getElementById('slideshow');
                    const overlay = document.getElementById('overlay');
                    // Apply settings
                    slideInterval = data.settings.slide_interval;
                    overlay.style.background = `rgba(0, 0, 0, ${data.settings.overlay_opacity})`;
                    // Update transition duration in CSS
                    const transitionDuration = data.settings.transition_duration;
                    // Create slide elements
                    if (data.backgrounds.length > 0) {
                        slideshow.innerHTML = data.backgrounds.map((bg, index) => `
                            <div class="slideshow-image ${index === 0 ? 'active' : ''}"
                                 style="background-image: url('${bg.url}'); transition: opacity ${transitionDuration}s ease-in-out;"></div>
                        `).join('');
                        slides = document.querySelectorAll('.slideshow-image');
                        // Start slideshow if more than one image
                        if (slides.length > 1) {
                            if (slideIntervalId) clearInterval(slideIntervalId);
                            slideIntervalId = setInterval(nextSlide, slideInterval);
                        }
                    } else {
                        // Fallback to a solid color if no backgrounds                            slideshow.style.background = '#1a1a2e';
                    }
                }
            } catch (error) {
                console.error('Error loading backgrounds:', error);
                document.getElementById('slideshow').style.background = '#1a1a2e';
            }
        }
        function nextSlide() {
            if (slides.length === 0) return;
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        // Weather Icons mapping
        const weatherIcons = {
            'Clear': 'fa-sun',
            'Sunny': 'fa-sun',
            'Partly cloudy': 'fa-cloud-sun',
            'Cloudy': 'fa-cloud',
            'Overcast': 'fa-cloud',
            'Mist': 'fa-smog',
            'Fog': 'fa-smog',
            'Light rain': 'fa-cloud-rain',
            'Rain': 'fa-cloud-showers-heavy',
            'Heavy rain': 'fa-cloud-showers-heavy',
            'Thunderstorm': 'fa-cloud-bolt',
            'Thunder': 'fa-cloud-bolt',
        };
        function getWeatherIcon(condition) {
            for (const [key, icon] of Object.entries(weatherIcons)) {
                if (condition.toLowerCase().includes(key.toLowerCase())) {
                    return icon;
                }
            }
            return 'fa-cloud';
        }
        // Fetch Weather from backend API
        async function fetchWeather() {
            try {
                const response = await fetch('/api/weather/current');
                const data = await response.json();
                if (data.success) {
                    const weather = data.weather;
                    // Update temperature
                    if (weather.show_temperature) {
                        document.getElementById('weather-temp').textContent = `${weather.temperature}${weather.unit}`;
                        document.getElementById('weather-temp').style.display = 'block';
                    } else {
                        document.getElementById('weather-temp').style.display = 'none';
                    }
                    // Update description
                    if (weather.show_description) {
                        document.getElementById('weather-desc').textContent = weather.description;
                        document.getElementById('weather-desc').style.display = 'block';
                    } else {
                        document.getElementById('weather-desc').style.display = 'none';
                    }
                    // Update icon based on description
                    document.getElementById('weather-icon').innerHTML = `<i class="fa-solid ${getWeatherIcon(weather.description)}"></i>`;
                    // Show/hide humidity
                    const humidityEl = document.getElementById('weather-humidity');
                    if (humidityEl) {
                        if (weather.show_humidity) {
                            humidityEl.textContent = `Humidity: ${weather.humidity}`;
                            humidityEl.style.display = 'block';
                        } else {
                            humidityEl.style.display = 'none';
                        }
                    }
                    // Show/hide wind
                    const windEl = document.getElementById('weather-wind');
                    if (windEl) {
                        if (weather.show_wind) {
                            windEl.textContent = `Wind: ${weather.wind}`;
                            windEl.style.display = 'block';
                        } else {
                            windEl.style.display = 'none';
                        }
                    }
                }
            } catch (error) {
                console.error('Weather fetch error:', error);
                document.getElementById('weather-desc').textContent = 'Unavailable';
            }
        }
        // Prayer times storage
        let prayerTimes = {};
        let visiblePrayers = [];
        // Fetch Prayer Times from backend API
        async function fetchPrayerTimes() {
            try {
                const response = await fetch('/api/prayer/times');
                const data = await response.json();
                if (data.success) {
                    prayerTimes = data.times;
                    visiblePrayers = Object.keys(data.times);
                    // Update display for each prayer
                    const allPrayers = ['subuh', 'syuruk', 'zohor', 'asar', 'maghrib', 'isyak'];
                    allPrayers.forEach(prayer => {
                        const element = document.getElementById(`prayer-${prayer}`);
                        if (data.times[prayer]) {
                            element.style.display = 'flex';
                            element.querySelector('.prayer-time').textContent = data.times[prayer];
                        } else {
                            element.style.display = 'none';
                        }
                    });
                    highlightNextPrayer();
                }
            } catch (error) {
                console.error('Prayer times fetch error:', error);
            }
        }
        // Convert time string to minutes since midnight
        function timeToMinutes(timeStr) {
            if (!timeStr) return 0;
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }
        // Highlight next prayer
        function highlightNextPrayer() {
            if (Object.keys(prayerTimes).length === 0) return;
            const now = new Date();
            const currentMinutes = now.getHours() * 60 + now.getMinutes();
            const allPrayers = ['subuh', 'syuruk', 'zohor', 'asar', 'maghrib', 'isyak'];
            // Remove all highlights
            allPrayers.forEach(prayer => {
                document.getElementById(`prayer-${prayer}`).classList.remove('next');
            });
            // Find next prayer (only from visible prayers)
            for (const prayer of visiblePrayers) {
                if (prayerTimes[prayer]) {
                    const prayerMinutes = timeToMinutes(prayerTimes[prayer]);
                    if (prayerMinutes > currentMinutes) {
                        document.getElementById(`prayer-${prayer}`).classList.add('next');
                        return;
                    }
                }
            }
            // If all prayers passed, highlight first visible prayer (next day)
            if (visiblePrayers.length > 0) {
                document.getElementById(`prayer-${visiblePrayers[0]}`).classList.add('next');
            }
        }
        // Fetch holidays from backend API (Calendarific)
        let cachedHolidays = [];
        async function fetchHolidays() {
            try {
                const response = await fetch('/api/holidays');
                const data = await response.json();
                if (data.success && data.holidays.length > 0) {
                    cachedHolidays = data.holidays;
                    displayUpcomingHoliday();
                }
            } catch (error) {
                console.error('Holiday fetch error:', error);
            }
        }
        function displayUpcomingHoliday() {
            const holidayNotice = document.getElementById('holiday-notice');
            const holidayText = document.getElementById('holiday-text');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            // Use first holiday that is today or in the future (in case cache is stale)
            const nextHoliday = cachedHolidays.find(h => {
                const d = new Date(h.date);
                d.setHours(0, 0, 0, 0);
                return d >= today;
            });
            if (nextHoliday) {
                const holidayDate = new Date(nextHoliday.date);
                holidayDate.setHours(0, 0, 0, 0);
                const diffTime = holidayDate - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                let timeText;
                if (diffDays === 0) {
                    timeText = 'Today';
                } else if (diffDays === 1) {
                    timeText = 'Tomorrow';
                } else {
                    timeText = `in ${diffDays} days`;
                }
                holidayText.textContent = `${nextHoliday.name} ${timeText}`;
                holidayNotice.classList.add('visible');
            } else {
                holidayNotice.classList.remove('visible');
            }
        }
        function checkUpcomingHolidays() {
            // Re-fetch from API (cached on backend for 24 hours)
            fetchHolidays();
        }
        // Position shifting to prevent burn-in
        const container = document.querySelector('.kiosk-container');
        const dayDisplay = document.querySelector('.day-display');
        const maxShift = 30;
        function shiftPosition() {
            const offsetX = Math.floor(Math.random() * (maxShift * 2 + 1)) - maxShift;
            const offsetY = Math.floor(Math.random() * (maxShift * 2 + 1)) - maxShift;
            container.style.transform = `translate(calc(-50% + ${offsetX}px), calc(-50% + ${offsetY}px))`;
        }
        // Color cycling for text
        const textColors = [
            '#ffffff',
            '#f0f0ff',
            '#fff5f0',
            '#f5fff5',
            '#fff0f5',
        ];
        const accentColors = [
            '#ffd369',
            '#ff9a8b',
            '#a8e6cf',
            '#88d8f5',
            '#dda0dd',
        ];
        let colorIndex = 0;
        function cycleColors() {
            colorIndex = (colorIndex + 1) % textColors.length;
            container.style.color = textColors[colorIndex];
            dayDisplay.style.color = accentColors[colorIndex];
        }
        // Initialize
        updateDateTime();
        initBackgrounds();
        fetchWeather();
        fetchPrayerTimes();
        fetchHolidays();
        shiftPosition();
        // Intervals
        setInterval(updateDateTime, 1000);
        // Note: slideshow interval is set dynamically in initBackgrounds()
        setInterval(shiftPosition, 60000);
        setInterval(cycleColors, 30000);
        // Refresh weather every 30 minutes
        setInterval(fetchWeather, 1800000);
        // Refresh prayer times and holidays at next midnight, then reschedule
        function scheduleMidnightRefresh() {
            const now = new Date();
            const nextMidnight = new Date(now);
            nextMidnight.setDate(nextMidnight.getDate() + 1);
            nextMidnight.setHours(0, 0, 0, 0);
            const msUntilMidnight = nextMidnight - now;
            setTimeout(() => {
                fetchPrayerTimes();
                fetchHolidays();
                scheduleMidnightRefresh();
            }, msUntilMidnight);
        }
        scheduleMidnightRefresh();
        // Refresh holiday display every hour (updates "Today"/"Tomorrow"/days text)
        setInterval(displayUpcomingHoliday, 3600000);
        // Prevent screen sleep
        async function requestWakeLock() {
            try {
                if ('wakeLock' in navigator) {
                    await navigator.wakeLock.request('screen');
                }
            } catch (err) {
                console.log('Wake Lock not supported');
            }
        }
        requestWakeLock();
        let lastVisibleDate = new Date().toDateString();
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                requestWakeLock();
                const todayStr = new Date().toDateString();
                if (todayStr !== lastVisibleDate) {
                    lastVisibleDate = todayStr;
                    fetchPrayerTimes();
                    fetchHolidays();
                }
            }
        });
    </script>
</body>
</html>