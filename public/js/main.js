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

