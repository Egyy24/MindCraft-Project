document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-menu .tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // untuk hilangkan active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // untuk menambah active class ketika button di klik
            button.classList.add('active');

            // untuk konten mana yang di show based on button text atau a data attribute
            const tabName = button.textContent.toLowerCase().replace(/\s/g, ''); 
            const targetContentId = tabName + '-tab'; 

            // untuk tunjukkan corresponding content
            const targetContent = document.getElementById(targetContentId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
});