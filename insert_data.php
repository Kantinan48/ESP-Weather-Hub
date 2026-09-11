<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Header -->
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 glass-card p-6 rounded-2xl shadow-sm">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="cloud-sun" class="w-7 h-7 text-amber-500"></i>
                สถานีอุตุนิยมวิทยา
            </h1>
            <p class="text-sm text-slate-500 mt-1">ระบบติดตามอุณหภูมิและความชื้น Real-time (ESP8266)</p>
        </div>
        <div class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3.5 py-1.5 rounded-full text-xs font-medium border border-emerald-200">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-green"></span>
            <span>เชื่อมต่อระบบแล้ว</span>
        </div>
    </header>

    <!-- Main Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Temperature C / F -->
        <div class="glass-card p-6 rounded-2xl shadow-sm relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-medium text-slate-500">อุณหภูมิห้อง</span>
                <div class="p-2.5 bg-rose-50 rounded-xl text-rose-500">
                    <i data-lucide="thermometer" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex items-baseline gap-1">
                    <span id="temp-c" class="text-4xl font-semibold text-slate-900 tracking-tight">
                        <?= $initialData['temp_c'] ?? '--' ?>
                    </span>
                    <span class="text-xl font-medium text-slate-500">°C</span>
                </div>
                <div class="text-xs text-slate-400 font-light">
                    เท่ากับ <span id="temp-f"><?= $initialData['temp_f'] ?? '--' ?></span> °F
                </div>
            </div>
        </div>

        <!-- Humidity -->
        <div class="glass-card p-6 rounded-2xl shadow-sm relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-medium text-slate-500">ความชื้นสัมพัทธ์</span>
                <div class="p-2.5 bg-sky-50 rounded-xl text-sky-500">
                    <i data-lucide="droplets" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex items-baseline gap-1">
                    <span id="humidity" class="text-4xl font-semibold text-slate-900 tracking-tight">
                        <?= $initialData['humidity'] ?? '--' ?>
                    </span>
                    <span class="text-xl font-medium text-slate-500">%</span>
                </div>
                <div class="text-xs text-slate-400 font-light">
                    ความชื้นในอากาศขณะนี้
                </div>
            </div>
        </div>

        <!-- Feels Like -->
        <div class="glass-card p-6 rounded-2xl shadow-sm relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-medium text-slate-500">ความรู้สึกจริง (Feels Like)</span>
                <div class="p-2.5 bg-amber-50 rounded-xl text-amber-500">
                    <i data-lucide="flame" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex items-baseline gap-1">
                    <span id="feels-like" class="text-4xl font-semibold text-slate-900 tracking-tight">
                        <?= $initialData['feels_like'] ?? '--' ?>
                    </span>
                    <span class="text-xl font-medium text-slate-500">°C</span>
                </div>
                <div class="text-xs text-slate-400 font-light">
                    ดัชนีความร้อนสะสม
                </div>
            </div>
        </div>

    </div>

    <!-- History Table -->
    <div class="glass-card p-6 rounded-2xl shadow-sm space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-slate-500"></i>
                ประวัติการบันทึกล่าสุด
            </h2>
            <span id="last-update" class="text-xs text-slate-400 font-light">
                อัปเดตล่าสุด: <?= $initialData['created_at'] ?? 'ไม่มีข้อมูล' ?>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100/60 text-slate-500 text-xs uppercase font-medium rounded-lg">
                    <tr>
                        <th class="py-3 px-4 rounded-l-lg">เวลาบันทึก</th>
                        <th class="py-3 px-4">อุณหภูมิ (°C)</th>
                        <th class="py-3 px-4">อุณหภูมิ (°F)</th>
                        <th class="py-3 px-4">ความชื้น (%)</th>
                        <th class="py-3 px-4 rounded-r-lg">Feels Like (°C)</th>
                    </tr>
                </thead>
                <tbody id="history-rows" class="divide-y divide-slate-100/80">
                    <?php if(!empty($initialHistory)): ?>
                        <?php foreach($initialHistory as$item): ?>
                            <tr class="hover:bg-white/40 transition-colors">
                                <td class="py-3 px-4 font-mono text-xs text-slate-500"><?= $item['created_at'] ?></td>
                                <td class="py-3 px-4 font-medium text-slate-800"><?= $item['temp_c'] ?> °C</td>
                                <td class="py-3 px-4"><?= $item['temp_f'] ?> °F</td>
                                <td class="py-3 px-4 text-sky-600 font-medium"><?= $item['humidity'] ?> %</td>
                                <td class="py-3 px-4 text-amber-600 font-medium"><?= $item['feels_like'] ?> °C</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400">ยังไม่มีข้อมูลบันทึกในระบบ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // AJAX Auto Refresh function
    async function fetchLatestData() {
        try {
            const response = await fetch('index.php?ajax=1');
            if (!response.ok) return;
            
            const data = await response.json();
            
            if (data.latest) {
                // Update main cards
                document.getElementById('temp-c').innerText = data.latest.temp_c ?? '--';
                document.getElementById('temp-f').innerText = data.latest.temp_f ?? '--';
                document.getElementById('humidity').innerText = data.latest.humidity ?? '--';
                document.getElementById('feels-like').innerText = data.latest.feels_like ?? '--';
                document.getElementById('last-update').innerText = 'อัปเดตล่าสุด: ' + (data.latest.created_at ?? '--');
            }

            if (data.history && data.history.length > 0) {
                // Update history table
                const tbody = document.getElementById('history-rows');
                tbody.innerHTML = data.history.map(item => `
                    <tr class="hover:bg-white/40 transition-colors">
                        <td class="py-3 px-4 font-mono text-xs text-slate-500">${item.created_at}</td>
                        <td class="py-3 px-4 font-medium text-slate-800">${item.temp_c} °C</td>
                        <td class="py-3 px-4">${item.temp_f} °F</td>
                        <td class="py-3 px-4 text-sky-600 font-medium">${item.humidity} %</td>
                        <td class="py-3 px-4 text-amber-600 font-medium">${item.feels_like} °C</td>
                    </tr>
                `).join('');
            }
        } catch (err) {
            console.error("Error fetching live data:", err);
        }
    }

    // Fetch new data every 3 seconds
    setInterval(fetchLatestData, 3000);
</script>
