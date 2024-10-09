const showSidebar = () => {
    const sidebar = document.getElementById("sidebar");
    const module_content = document.getElementById("module-content");
    sidebar.classList.add("active");
    module_content.classList.add("hidden");
}

const hideSidebar = () => {
    const sidebar = document.getElementById("sidebar");
    const module_content = document.getElementById("module-content");
    sidebar.classList.remove("active");
    module_content.classList.remove("hidden");
}

const toggleSidebar = () => {
    console.log("test");
    const sidebar = document.getElementById("sidebar");
    if (sidebar.classList.contains("active")) {
        hideSidebar();
    } else {
        showSidebar();
    }
}

const toggleCheckbox = (e) => {
    const hidden = e.currentTarget.previousElementSibling;
    hidden.value = e.currentTarget.checked ? 1 : 0;
}

const clearSearch = () => {
    document.getElementById('filter-search').value = '';
}

const clearPost = () => {
    setTimeout(() => {document.getElementById('new-post').value = ''});
}

const bodyChange = (e) => {
    const value = e.currentTarget.value;
    const button = e.currentTarget.closest('.d-flex').querySelector('button[type="submit"]');
    button.disabled = value.length === 0;
}

const showComment = (e) => {
    const comment = e.currentTarget.closest('.actions').nextElementSibling;
    comment.classList.toggle("hidden");
}

const toggleFileUpload = (e) => {
    const file_upload = e.currentTarget.nextElementSibling;
    const hidden_input = file_upload.nextElementSibling.firstElementChild;
    if (file_upload.classList.contains("hidden")) {
        file_upload.classList.remove("hidden");
        hidden_input.disabled = true;
    } else {
        file_upload.classList.add("hidden");
        hidden_input.disabled = false;
    }
}
