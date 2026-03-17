// SIDEBAR
const sidebar = document.getElementById("sidebar");
const main = document.getElementById("main");
const menuBtn = document.getElementById("menuBtn");

menuBtn.onclick = () => {
    sidebar.classList.toggle("active");
    main.classList.toggle("shift");
};

// PAGINA SWITCH
function switchPage(page){
    document.querySelectorAll(".content-page").forEach(p=>{
        p.style.display="none";
    });
    document.getElementById("page-"+page).style.display="block";
}

// LEDEN TOEVOEGEN
function addLid(){
    let naam = document.getElementById("lidNaam").value;
    let email = document.getElementById("lidEmail").value;

    if(!naam || !email) return;

    let table = document.getElementById("ledenTabelBody");
    let row = table.insertRow();

    row.innerHTML = `
        <td>${naam}</td>
        <td>${email}</td>
        <td>${new Date().getFullYear()}</td>
        <td><button class="delete-btn" onclick="deleteRow(this)">X</button></td>
    `;
    
    document.getElementById("lidNaam").value = "";
    document.getElementById("lidEmail").value = "";
    updateStats();
}

// LES TOEVOEGEN
function addLes(){
    let naam = document.getElementById("lesNaam").value;
    let datum = document.getElementById("lesDatum").value;
    let tijd = document.getElementById("lesTijd").value;
    let prijs = document.getElementById("lesPrijs").value;

    if (!naam || !datum || !tijd || !prijs) return;

    let rowContent = `
        <td>${naam}</td>
        <td>${datum}</td>
        <td>${tijd}</td>
        <td>€${prijs}</td>
        <td><span class="badge blue">Ingepland</span></td>
        <td><button class="delete-btn" onclick="deleteRow(this)">X</button></td>
    `;

    let dashTable = document.getElementById("lesTabelDashboard");
    let row1 = dashTable.insertRow();
    row1.innerHTML = rowContent;

    let lesTable = document.getElementById("lesTabelPagina");
    let row2 = lesTable.insertRow();
    row2.innerHTML = rowContent;
    
    document.getElementById("lesNaam").value = "";
    document.getElementById("lesDatum").value = "";
    document.getElementById("lesTijd").value = "";
    document.getElementById("lesPrijs").value = "";

    updateStats();
    switchPage('dashboard');
}

// VERWIJDEREN
function deleteRow(btn) {
    btn.closest('tr').remove();
    updateStats();
}

// STATS UPDATE
function updateStats() {
    let ledenCount = document.getElementById("ledenTabelBody").rows.length;
    document.getElementById("stat-leden").innerText = ledenCount;
    document.getElementById("stat-actief").innerText = ledenCount;
    document.getElementById("stat-nieuw").innerText = ledenCount;
    document.getElementById("stat-inactief").innerText = 0;
}

// FILTER
function filterLessen() {
    let filter = document.getElementById("searchInput").value.toLowerCase();
    let status = document.getElementById("statusFilter").value;
    let rows = document.querySelectorAll("#lesTabelDashboard tr");

    rows.forEach(row => {
        let text = row.cells[0].textContent.toLowerCase();
        let statusText = row.cells[4].textContent.trim();
        let show = (status === 'all' || statusText === status) && text.includes(filter);
        row.style.display = show ? "" : "none";
    });
}

// GRAFIEK
function initDashboardChart() {
    const ctx = document.getElementById("ledenChart");
    if (!ctx) return;
    new Chart(ctx, {
        type: "line",
        data: {
            labels: ["Jan", "Feb", "Mrt", "Apr"],
            datasets: [{
                data: [0, 0, 5, 5],
                borderColor: "#3b82f6",
                backgroundColor: "rgba(59,130,246,0.2)",
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: { legend: { display: false } }
        }
    });
}

window.addEventListener('load', () => {
    switchPage('dashboard');
    updateStats();
    initDashboardChart();
});
