const showSidebar = () => {
  const sidebar = document.getElementById("sidebar");
	const mobile_toggle = document.getElementById("mobile-toggle");
	sidebar.classList.add("active");
	mobile_toggle.classList.add("hidden");
}

const hideSidebar = () => {
  const sidebar = document.getElementById("sidebar");
	const mobile_toggle = document.getElementById("mobile-toggle");
	sidebar.classList.remove("active");
	mobile_toggle.classList.remove("hidden");
}

const toggleCheckbox = (e) => {
    const hidden = e.currentTarget.previousElementSibling;
    hidden.value = e.currentTarget.checked ? 1 : 0;
}
