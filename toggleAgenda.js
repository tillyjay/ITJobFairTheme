console.log("toggleAgenda.js is loaded");

// Toggle the visibility of the agenda table
function toggleTable() {
    var table = document.querySelector('figure.wp-block-table.agenda_table');
	var button = document.querySelector('.agenda_button');
    var display = window.getComputedStyle(table).display;
    if (display === "none") {
        table.style.display = "block";
		button.querySelector("i").classList.remove("fa-arrow-down");
		button.querySelector("i").classList.add("fa-arrow-up");
    } else {
        table.style.display = "none";
		button.querySelector("i").classList.remove("fa-arrow-up");
		button.querySelector("i").classList.add("fa-arrow-down");
    }
}