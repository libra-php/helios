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

const fileUpload = (e) => {
    e.preventDefault();
    const file_desc = e.currentTarget.closest(".file-desc");
    const file_upload = e.currentTarget.closest(".file-desc").nextElementSibling;
    file_desc.classList.add("hidden");
    file_upload.classList.remove("hidden");
}
