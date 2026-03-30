<!-- gemini https://gemini.google.com/app/81d3b802295e6065 -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Workout Tracker Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        .screen { animation: fadeIn 0.2s ease-out; display: none; }
        .screen.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .unit-badge.active { background-color: black !important; color: white !important; }
        .archive-card { opacity: 0.6; filter: grayscale(0.4); }
        .today-card { border: 2px solid #000 !important; background: #fff; position: relative; }
        .today-card::after { content: 'СЕГОДНЯ'; position: absolute; top: -10px; right: 15px; background: black; color: white; font-size: 8px; font-weight: 900; padding: 2px 8px; border-radius: 4px; }
        #lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 100; align-items: center; justify-content: center; flex-direction: column; }
        #lightbox.active { display: flex; }
        .dragging { opacity: 0.5; background: #f3f4f6; border: 2px dashed #ccc !important; }
    </style>
</head>
<body>
    <div class="max-w-lg mx-auto min-h-screen flex flex-col relative pb-24">
        
        <div id="screen-main" class="screen p-5">
            <header class="mb-8"><h1 class="text-3xl font-black italic uppercase text-gray-900">Мои Тренировки</h1></header>
            <div id="workouts-list" class="space-y-6"></div>
            <div id="archive-separator" class="hidden my-10 flex items-center gap-4">
                <div class="h-px bg-gray-300 flex-1"></div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Архив</span>
                <div class="h-px bg-gray-300 flex-1"></div>
            </div>
            <div id="archive-list" class="space-y-6"></div>
            <button onclick="createNewWorkout()" class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[90%] max-w-md bg-black text-white py-4 rounded-2xl font-bold shadow-2xl active:scale-95 transition-all z-10">+ Новая тренировка</button>
        </div>

        <div id="screen-workout" class="screen p-5">
            <nav class="flex justify-between items-center mb-6">
                <button onclick="window.history.back()" class="text-2xl">←</button>
                <button onclick="duplicateWorkout(currentWId)" class="text-[10px] font-bold uppercase bg-gray-100 px-3 py-2 rounded-lg">Копия</button>
            </nav>
            <div class="bg-white p-5 rounded-2xl shadow-sm mb-6 border border-gray-100">
                <input type="date" id="w-date" oninput="autoSave()" class="w-full mb-2 font-bold text-gray-800 outline-none">
                <input type="text" id="w-title" oninput="autoSave()" placeholder="Название тренировки..." class="w-full text-lg font-bold outline-none">
            </div>
            <div id="w-exercises-list" class="space-y-3 mb-6"></div>
            <button onclick="openExerciseSelector()" class="w-full border-2 border-black py-4 rounded-2xl font-bold active:bg-gray-100 mb-4">Добавить упражнение</button>
            <button onclick="deleteWorkout()" class="w-full py-2 text-red-400 text-xs font-bold uppercase">Удалить тренировку</button>
        </div>

        <div id="screen-edit-lib" class="screen p-5">
            <h2 class="text-xl font-bold mb-6">Настройка упражнения</h2>
            <div class="bg-white p-6 rounded-3xl space-y-6 shadow-sm border border-gray-100">
                <input type="text" id="ex-name" class="w-full bg-gray-50 p-4 rounded-xl outline-none font-bold" placeholder="Название">
                <textarea id="ex-desc" class="w-full bg-gray-50 p-4 rounded-xl outline-none text-sm min-h-[100px]" placeholder="Описание..."></textarea>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 mb-2 block uppercase">Файлы</label>
                    <input type="file" id="ex-files" multiple accept="image/*" class="w-full text-xs">
                </div>
                <div class="flex gap-2">
                    <button data-unit="кг/повторы" class="unit-badge flex-1 py-3 rounded-xl bg-gray-100 text-[10px] font-bold uppercase">кг/повторы</button>
                    <button data-unit="раз" class="unit-badge flex-1 py-3 rounded-xl bg-gray-100 text-[10px] font-bold uppercase">Раз</button>
                    <button data-unit="км" class="unit-badge flex-1 py-3 rounded-xl bg-gray-100 text-[10px] font-bold uppercase">Км</button>
                </div>
                <div class="flex gap-3 pt-4 border-t">
                    <button onclick="window.history.back()" class="flex-1 py-4 bg-gray-100 rounded-2xl font-bold text-gray-500">Отмена</button>
                    <button onclick="saveLibraryItemWithUpload()" id="btn-save-lib" class="flex-1 py-4 bg-black text-white rounded-2xl font-bold">Готово</button>
                </div>
            </div>
        </div>

        <div id="screen-run" class="screen p-5">
            <nav class="flex justify-between items-center mb-6">
                <button onclick="window.history.back()" class="text-2xl">←</button>
                <button onclick="router.navigate('edit-lib', {libId: currentLibId})" class="text-blue-500 font-bold text-sm uppercase">Ред.</button>
            </nav>
            <div class="flex items-center gap-4 mb-4 bg-white p-4 rounded-3xl shadow-sm border border-gray-100">
                <div id="run-main-img" class="w-20 h-20 bg-gray-50 rounded-2xl overflow-hidden flex-shrink-0 cursor-pointer" onclick="openSlider(currentLibId)"></div>
                <div class="flex-1 overflow-hidden">
                    <h2 id="run-ex-name" class="font-black text-xl text-gray-800 truncate"></h2>
                    <p id="run-ex-desc" class="text-xs text-gray-400 line-clamp-2 mt-1"></p>
                </div>
            </div>

            <div id="rest-timer" class="hidden bg-blue-50 border border-blue-100 p-4 rounded-2xl mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold italic">R</div>
                    <div>
                        <p class="text-[10px] font-black text-blue-400 uppercase">Отдых</p>
                        <p id="timer-display" class="text-xl font-bold text-blue-600 tabular-nums">02:00</p>
                    </div>
                </div>
                <button onclick="stopTimer()" class="p-2 text-blue-300 font-bold">✕</button>
            </div>

            <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 mb-8 flex items-center gap-2">
                <div id="input-group-1" class="flex-1"><input type="number" id="v1" class="w-full bg-gray-50 p-4 rounded-xl text-center font-bold text-xl outline-none" placeholder="Кг"></div>
                <span id="multiplier" class="text-gray-300 font-bold">×</span>
                <div id="input-group-2" class="flex-1"><input type="number" id="v2" class="w-full bg-gray-50 p-4 rounded-xl text-center font-bold text-xl outline-none" placeholder="Раз"></div>
                <button onclick="addSet()" class="w-14 h-14 bg-black text-white rounded-2xl text-2xl font-bold active:scale-90">+</button>
            </div>
            
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 ml-2">История выполнения</h3>
            <div id="history-list" class="space-y-6"></div>
        </div>

        <div id="overlay" class="fixed inset-0 bg-black/60 hidden z-50 flex flex-col justify-end backdrop-blur-sm">
            <div class="bg-white rounded-t-3xl w-full max-h-[85vh] flex flex-col p-5">
                <div id="lib-list" class="flex-1 overflow-y-auto space-y-2 mb-4"></div>
                <button onclick="toggleOverlay(false); router.navigate('edit-lib')" class="w-full py-4 bg-black text-white rounded-2xl font-bold">Создать новое</button>
                <button onclick="toggleOverlay(false)" class="w-full py-3 text-gray-400 font-bold">Закрыть</button>
            </div>
        </div>

        <div id="lightbox" onclick="this.classList.remove('active')">
            <div class="relative w-full h-[70vh] flex items-center justify-center p-5">
                <img id="lightbox-img" src="" class="max-w-full max-h-full object-contain rounded-xl shadow-2xl" onclick="nextSlide(event)">
            </div>
            <div class="text-white text-xs font-bold mt-4 px-6 py-2 bg-white/10 rounded-full" id="lightbox-info"></div>
        </div>
    </div>

<script>
    let state = { workouts: [], library: [] };
    let currentWId = null, currentExIdx = null, currentLibId = null, editingLibId = null, timerInterval = null;

    function getLocalDate() {
        const now = new Date();
        const offset = now.getTimezoneOffset() * 60000;
        return (new Date(now - offset)).toISOString().split('T')[0];
    }

    const router = {
        routes: {
            'main': () => showScreen('screen-main'),
            'workout': (params) => { currentWId = parseInt(params.id); showScreen('screen-workout'); },
            'edit-lib': (params) => { editingLibId = params.libId ? parseInt(params.libId) : null; showScreen('screen-edit-lib'); },
            'run': (params) => { currentWId = parseInt(params.wid); currentExIdx = parseInt(params.idx); showScreen('screen-run'); }
        },
        navigate(route, params = {}) {
            let url = '#' + route;
            const query = new URLSearchParams(params).toString();
            if (query) url += '?' + query;
            window.history.pushState(null, '', url);
            this.handle();
        },
        handle() {
            const hash = window.location.hash.slice(1) || 'main';
            const [route, query] = hash.split('?');
            const params = Object.fromEntries(new URLSearchParams(query));
            if (this.routes[route]) this.routes[route](params);
        }
    };
    window.addEventListener('popstate', () => router.handle());

    async function api(action, data = {}, isFormData = false) {
        try {
            const options = { method: 'POST' };
            if (isFormData) { data.append('action', action); options.body = data; } 
            else { options.headers = { 'Content-Type': 'application/json' }; options.body = JSON.stringify({ action, ...data }); }
            const res = await fetch('api.php', options); 
            return await res.json();
        } catch(e) { 
            if(action === 'load') return JSON.parse(localStorage.getItem('gym_state') || '{"workouts":[], "library":[]}');
            if(action === 'save') localStorage.setItem('gym_state', JSON.stringify(data));
            return data;
        }
    }

    async function loadData() { state = await api('load'); router.handle(); }

    async function autoSave() {
        if (currentWId) {
            const w = state.workouts.find(x => x.id === currentWId);
            if (w) {
                w.title = document.getElementById('w-title').value;
                w.date = document.getElementById('w-date').value;
            }
        }
        await api('save', state);
        if(window.location.hash === '' || window.location.hash === '#main') renderMain();
    }

    function showScreen(id) {
        document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        if (id === 'screen-main') renderMain();
        if (id === 'screen-workout') renderWorkout();
        if (id === 'screen-edit-lib') renderEditLib();
        if (id === 'screen-run') renderRun();
        window.scrollTo(0,0);
    }

    function renderMain() {
        const todayStr = getLocalDate();
        const listContainer = document.getElementById('workouts-list');
        const archiveContainer = document.getElementById('archive-list');
        const sep = document.getElementById('archive-separator');
        
        listContainer.innerHTML = ''; 
        archiveContainer.innerHTML = '';

        // Группировка тренировок по дате
        const grouped = state.workouts.reduce((acc, w) => {
            if (!acc[w.date]) acc[w.date] = [];
            acc[w.date].push(w);
            return acc;
        }, {});

        const sortedDates = Object.keys(grouped).sort((a, b) => new Date(b) - new Date(a));
        let hasArchive = false;

        sortedDates.forEach(date => {
            const isToday = date === todayStr;
            const isArchive = date < todayStr;
            const target = isArchive ? archiveContainer : listContainer;
            if (isArchive) hasArchive = true;

            const dateGroup = document.createElement('div');
            dateGroup.className = "space-y-3";
            
            const dateLabel = document.createElement('p');
            dateLabel.className = `text-[10px] font-black uppercase tracking-widest mb-2 ml-1 ${isToday ? 'text-black' : 'text-gray-400'}`;
            dateLabel.innerText = new Date(date).toLocaleDateString('ru-RU', {day:'numeric', month:'long'});
            dateGroup.appendChild(dateLabel);

            grouped[date].forEach(w => {
                const card = document.createElement('div');
                const done = w.exercises.filter(e => e.history?.length > 0).length;
                const progress = w.exercises.length ? Math.round((done / w.exercises.length) * 100) : 0;
                
                card.className = `bg-white p-5 rounded-2xl shadow-sm flex items-center gap-4 border border-gray-100 active:scale-95 transition-all ${isToday ? 'today-card' : ''} ${isArchive ? 'archive-card' : ''}`;
                card.onclick = () => router.navigate('workout', {id: w.id});
                
                card.innerHTML = `
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800">${w.title || 'Без названия'}</h3>
                        <div class="w-full bg-gray-100 h-1 rounded-full mt-2">
                            <div class="bg-black h-1 rounded-full" style="width:${progress}%"></div>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-gray-300 uppercase">${progress}%</span>
                `;
                dateGroup.appendChild(card);
            });

            target.appendChild(dateGroup);
        });

        sep.classList.toggle('hidden', !hasArchive);
    }

    function renderWorkout() {
        const w = state.workouts.find(x => x.id === currentWId);
        if(!w) return;
        document.getElementById('w-title').value = w.title || '';
        document.getElementById('w-date').value = w.date || '';
        const list = document.getElementById('w-exercises-list');
        list.innerHTML = '';
        w.exercises.forEach((ex, i) => {
            const lib = state.library.find(l => l.id === ex.libId) || {name:'Удалено'};
            const div = document.createElement('div');
            div.className = "bg-white p-3 rounded-2xl shadow-sm flex items-center gap-3 border border-gray-50 cursor-move";
            div.draggable = true;
            div.dataset.index = i;
            div.innerHTML = `<div class="w-12 h-12 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0">${(lib.images && lib.images[0]) ? `<img src="${lib.images[0]}" class="w-full h-full object-cover">` : ''}</div><div class="flex-1" onclick="router.navigate('run', {wid: ${currentWId}, idx: ${i}})"><h4 class="font-bold text-sm text-gray-700 leading-tight">${lib.name}</h4><p class="text-[10px] font-bold text-blue-400 uppercase">${ex.history?.length||0} подходов</p></div><button onclick="removeEx(${i})" class="p-2 text-red-100 font-bold">✕</button>`;
            
            div.addEventListener('dragstart', (e) => { e.currentTarget.classList.add('dragging'); e.dataTransfer.setData('text/plain', i); });
            div.addEventListener('dragend', (e) => e.currentTarget.classList.remove('dragging'));
            div.addEventListener('dragover', (e) => e.preventDefault());
            div.addEventListener('drop', (e) => {
                e.preventDefault();
                const fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
                const toIdx = parseInt(e.currentTarget.dataset.index);
                if (fromIdx !== toIdx) {
                    const movedItem = w.exercises.splice(fromIdx, 1)[0];
                    w.exercises.splice(toIdx, 0, movedItem);
                    autoSave(); renderWorkout();
                }
            });
            list.appendChild(div);
        });
    }

    function renderRun() {
        const w = state.workouts.find(x => x.id === currentWId);
        const ex = w.exercises[currentExIdx];
        const lib = state.library.find(l => l.id === ex.libId);
        currentLibId = lib.id;
        document.getElementById('run-ex-name').innerText = lib.name;
        document.getElementById('run-ex-desc').innerText = lib.description || '';
        document.getElementById('input-group-1').classList.toggle('hidden', lib.unit === 'раз');
        document.getElementById('input-group-2').classList.toggle('hidden', lib.unit === 'км');
        const mainImg = document.getElementById('run-main-img');
        mainImg.innerHTML = (lib.images && lib.images[0]) ? `<img src="${lib.images[0]}" class="w-full h-full object-cover">` : '';
        renderGlobalHistory(lib.id, lib.unit);
        stopTimer();
    }

    function startTimer() {
        stopTimer();
        const timerBlock = document.getElementById('rest-timer');
        const display = document.getElementById('timer-display');
        timerBlock.classList.remove('hidden');
        let timeLeft = 120;
        const updateDisplay = () => {
            const m = Math.floor(timeLeft / 60), s = timeLeft % 60;
            display.innerText = `${m}:${s < 10 ? '0' : ''}${s}`;
        };
        updateDisplay();
        timerInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft < 0) stopTimer(); else updateDisplay();
        }, 1000);
    }

    function stopTimer() { if (timerInterval) clearInterval(timerInterval); document.getElementById('rest-timer').classList.add('hidden'); }

    function addSet() {
        const v1 = document.getElementById('v1').value, v2 = document.getElementById('v2').value;
        if (!v1 && !v2) return; // Не добавляем пустые значения

        const w = state.workouts.find(x => x.id === currentWId);
        const ex = w.exercises[currentExIdx];
        ex.history.push({ id: Date.now(), v1, v2 });
        autoSave(); 
        renderGlobalHistory(ex.libId, state.library.find(l => l.id === ex.libId).unit);
        document.getElementById('v1').value = ''; document.getElementById('v2').value = '';
        startTimer();
    }

    function renderGlobalHistory(libId, unit) {
        const list = document.getElementById('history-list'); list.innerHTML = '';
        let allDays = {};
        state.workouts.forEach(w => {
            w.exercises.forEach(ex => {
                if(ex.libId === libId && ex.history.length > 0) {
                    if(!allDays[w.date]) allDays[w.date] = [];
                    ex.history.forEach(h => { allDays[w.date].push(h); });
                }
            });
        });
        const sortedDates = Object.keys(allDays).sort((a,b) => new Date(b) - new Date(a));
        sortedDates.forEach(date => {
            const dayBox = document.createElement('div');
            dayBox.className = "bg-white rounded-3xl p-5 shadow-sm border border-gray-50";
            dayBox.innerHTML = `<div class="text-[10px] font-black text-blue-500 uppercase mb-4">${new Date(date).toLocaleDateString('ru-RU', {day:'numeric', month:'long'})}</div>`;
            const setsList = document.createElement('div');
            allDays[date].forEach((h, index) => {
                const row = document.createElement('div'); row.className = "flex justify-between items-center text-sm py-1";
                
                let val = '';
                if (unit === 'кг/повторы') {
                    val = `<b>${h.v1 || 0}кг</b> × ${h.v2 || 0} раз`;
                } else if (unit === 'км') {
                    val = `<b>${h.v1 || 0}км</b>`;
                } else {
                    val = `<b>${h.v2 || 0} раз</b>`;
                }

                row.innerHTML = `<span class="text-gray-300 mr-4 font-mono text-[10px]">${index + 1}</span><span class="flex-1 text-gray-700">${val}</span><button onclick="deleteSetFromDatabase(${h.id}, ${libId}, '${unit}')" class="text-red-200 ml-2 p-1">✕</button>`;
                setsList.appendChild(row);
            });
            dayBox.appendChild(setsList); list.appendChild(dayBox);
        });
    }

    function deleteSetFromDatabase(setId, libId, unit) {
        if(!confirm('Удалить этот подход?')) return;
        state.workouts.forEach(w => {
            w.exercises.forEach(ex => {
                if(ex.libId === libId) { ex.history = ex.history.filter(h => h.id !== setId); }
            });
        });
        autoSave(); renderGlobalHistory(libId, unit);
    }

    function renderLib() {
        const list = document.getElementById('lib-list'); list.innerHTML = '';
        state.library.forEach(l => {
            const d = document.createElement('div');
            d.className = "p-3 bg-gray-50 rounded-xl flex items-center gap-3 active:bg-gray-100 mb-1";
            d.innerHTML = `
                <div class="w-10 h-10 bg-white rounded-lg overflow-hidden flex-shrink-0">${(l.images && l.images[0]) ? `<img src="${l.images[0]}" class="w-full h-full object-cover">` : ''}</div>
                <div class="flex-1" onclick="addExToWorkout(${l.id})">
                    <b class="text-sm text-gray-700">${l.name}</b>
                </div>
                <button onclick="router.navigate('edit-lib', {libId: ${l.id}})" class="text-blue-400 text-[10px] font-bold uppercase p-2">Ред.</button>
                <button onclick="deleteFromLibraryById(${l.id})" class="text-red-300 text-[10px] font-bold uppercase p-2">Удал.</button>
            `;
            list.appendChild(d);
        });
    }

    function addExToWorkout(libId) {
        const w = state.workouts.find(x => x.id === currentWId);
        w.exercises.push({ libId, history: [] });
        autoSave();
        toggleOverlay(false);
        renderWorkout();
    }

    function deleteFromLibraryById(id) {
        if(confirm('Удалить упражнение из базы навсегда?')) {
            state.library = state.library.filter(x => x.id !== id);
            api('save', state);
            renderLib();
        }
    }

    async function saveLibraryItemWithUpload() {
        const name = document.getElementById('ex-name').value;
        const desc = document.getElementById('ex-desc').value;
        const unit = document.querySelector('.unit-badge.active')?.dataset.unit || 'кг/повторы';
        if (!name) return;

        const btn = document.getElementById('btn-save-lib');
        btn.disabled = true;
        btn.textContent = 'Сохранение...';

        // Сначала загружаем новые файлы на сервер
        let newImages = [];
        const fileInput = document.getElementById('ex-files');
        if (fileInput.files.length > 0) {
            const formData = new FormData();
            formData.append('action', 'upload');
            for (const file of fileInput.files) {
                formData.append('files[]', file);
            }
            try {
                const res = await fetch('api.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.paths) newImages = result.paths;
            } catch (e) {
                console.error('Upload error:', e);
            }
        }

        // Объединяем старые изображения с новыми
        let images = editingLibId ? (state.library.find(x => x.id === editingLibId)?.images || []) : [];
        images = images.concat(newImages);

        if (editingLibId) {
            const l = state.library.find(x => x.id === editingLibId);
            l.name = name; l.description = desc; l.unit = unit; l.images = images;
        } else {
            state.library.push({ id: Date.now(), name, description: desc, unit, images });
        }
        await api('save', state);

        btn.disabled = false;
        btn.textContent = 'Готово';
        fileInput.value = '';
        window.history.back();
    }

    function createNewWorkout() {
        const id = Date.now();
        state.workouts.push({ id, title: '', date: getLocalDate(), exercises: [] });
        autoSave();
        router.navigate('workout', {id});
    }

    function duplicateWorkout(id) {
        const s = state.workouts.find(x => x.id === id);
        const nId = Date.now();
        state.workouts.push({ ...s, id: nId, date: getLocalDate(), exercises: s.exercises.map(ex => ({ ...ex, history: [] })) });
        autoSave(); router.navigate('workout', {id: nId});
    }

    function deleteWorkout() { if(confirm('Удалить тренировку?')) { state.workouts = state.workouts.filter(x => x.id !== currentWId); autoSave(); window.history.back(); } }
    function removeEx(idx) { const w = state.workouts.find(x => x.id === currentWId); w.exercises.splice(idx,1); autoSave(); renderWorkout(); }
    function toggleOverlay(s) { document.getElementById('overlay').classList.toggle('hidden', !s); if(s) renderLib(); }
    function openExerciseSelector() { toggleOverlay(true); }
    function renderEditLib() {
        if(editingLibId) {
            const l = state.library.find(x => x.id === editingLibId);
            document.getElementById('ex-name').value = l.name;
            document.getElementById('ex-desc').value = l.description || '';
            document.querySelectorAll('.unit-badge').forEach(b => b.classList.toggle('active', b.dataset.unit === l.unit));
        } else {
            document.getElementById('ex-name').value = '';
            document.getElementById('ex-desc').value = '';
            document.querySelectorAll('.unit-badge').forEach(b => b.classList.toggle('active', b.dataset.unit === 'кг/повторы'));
        }
    }

    let sliderImages = []; let sliderIdx = 0;
    function openSlider(libId) { const lib = state.library.find(l => l.id === libId); if(!lib?.images?.length) return; sliderImages = lib.images; sliderIdx = 0; updateSlider(); document.getElementById('lightbox').classList.add('active'); }
    function nextSlide(e) { e.stopPropagation(); sliderIdx = (sliderIdx + 1) % sliderImages.length; updateSlider(); }
    function updateSlider() { document.getElementById('lightbox-img').src = sliderImages[sliderIdx]; document.getElementById('lightbox-info').innerText = `${sliderIdx + 1} / ${sliderImages.length}`; }
    
    document.querySelectorAll('.unit-badge').forEach(b => b.onclick = () => { document.querySelectorAll('.unit-badge').forEach(x => x.classList.remove('active')); b.classList.add('active'); });
    loadData();
</script>
</body>
</html>