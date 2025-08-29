document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'nvrEndpoints';
    const defaultEndpoints = [
        { name: 'Home CCTV', url: 'https://homecctv.brandon.my/' },
        { name: 'SG CCTV', url: 'https://sg-cctv.brandon.my/master.php' }
    ];

    let endpoints = JSON.parse(localStorage.getItem(STORAGE_KEY)) || defaultEndpoints;
    if (!localStorage.getItem(STORAGE_KEY)) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(defaultEndpoints));
    }

    const list = document.getElementById('endpointList');
    const addForm = document.getElementById('addForm');
    const editForm = document.getElementById('editForm');
    const addModal = new bootstrap.Modal(document.getElementById('addModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    const iframeContainer = document.getElementById('iframeContainer');
    const nvrIframe = document.getElementById('nvrIframe');
    const closeIframe = document.getElementById('closeIframe');
    const mainContent = document.getElementById('mainContent');

    function renderList() {
        list.innerHTML = '';
        endpoints.forEach((ep, index) => {
            const item = document.createElement('li');
            item.className = 'list-group-item d-flex justify-content-between align-items-center';
            item.innerHTML = `
                <a href="#" class="view-link" data-url="${ep.url}">${ep.name} (${ep.url})</a>
                <div>
                    <button class="btn btn-sm btn-warning me-2 edit-btn" data-index="${index}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-index="${index}">Delete</button>
                </div>
            `;
            list.appendChild(item);
        });
    }

    function saveToStorage() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(endpoints));
    }

    function showIframe(url) {
        nvrIframe.src = url;
        mainContent.classList.add('d-none');
        iframeContainer.classList.remove('d-none');
    }

    function hideIframe() {
        nvrIframe.src = '';
        iframeContainer.classList.add('d-none');
        mainContent.classList.remove('d-none');
    }

    addForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const name = document.getElementById('name').value.trim();
        const url = document.getElementById('url').value.trim();
        if (name && url) {
            endpoints.push({ name, url });
            saveToStorage();
            renderList();
            addForm.reset();
            addModal.hide();
        }
    });

    list.addEventListener('click', (e) => {
        if (e.target.classList.contains('view-link')) {
            e.preventDefault();
            const url = e.target.dataset.url;
            showIframe(url);
        } else if (e.target.classList.contains('edit-btn')) {
            const index = e.target.dataset.index;
            document.getElementById('editIndex').value = index;
            document.getElementById('editName').value = endpoints[index].name;
            document.getElementById('editUrl').value = endpoints[index].url;
            editModal.show();
        } else if (e.target.classList.contains('delete-btn')) {
            const index = e.target.dataset.index;
            if (confirm('Delete this endpoint?')) {
                endpoints.splice(index, 1);
                saveToStorage();
                renderList();
            }
        }
    });

    editForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const index = document.getElementById('editIndex').value;
        const name = document.getElementById('editName').value.trim();
        const url = document.getElementById('editUrl').value.trim();
        if (name && url) {
            endpoints[index] = { name, url };
            saveToStorage();
            renderList();
            editModal.hide();
        }
    });

    closeIframe.addEventListener('click', hideIframe);

    renderList();
});