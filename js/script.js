const sidebar = document.getElementById('sidebar');
const main = document.getElementById('main');
const menuBtn = document.getElementById('menuBtn');

menuBtn.onclick = () => {
    sidebar.classList.toggle('active');
    main.classList.toggle('shift');
};

function switchPage(page) {
    document.querySelectorAll('.content-page').forEach(p => {
        p.style.display = 'none';
    });
    const target = document.getElementById('page-' + page);
    if (target) target.style.display = 'block';
}
