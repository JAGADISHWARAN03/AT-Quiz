document.addEventListener('DOMContentLoaded', () => {
    // Sidebar navigation links
    const navLinks = [
        { id: 'sidebar-dashboard', section: 'dashboard-overview', action: '' },
        { id: 'sidebar-create-quiz', section: 'quiz-creation', action: 'create_quiz' },
        { id: 'sidebar-results', section: 'results-section', action: 'results' },
        { id: 'sidebar-categories', section: 'categories-section', action: 'categories' },
        { id: 'sidebar-mail', section: 'mail-section', action: 'mail' }
    ];

    navLinks.forEach(link => {
        const element = document.getElementById(link.id);
        if (element) {
            element.addEventListener('click', (e) => {
                e.preventDefault();
                // Hide all sections
                document.querySelectorAll('#dashboard-overview, #quiz-creation, #results-section, #categories-section, #mail-section, #quiz-display-section, #questions-containern')
                    .forEach(section => section.classList.add('hidden'));
                // Show the target section
                const targetSection = document.getElementById(link.section);
                if (targetSection) {
                    targetSection.classList.remove('hidden');
                }
                // Clear quiz display and questions content if not needed
                if (link.section !== 'quiz-display-section') {
                    document.getElementById('quiz-display-content').innerHTML = '';
                    document.getElementById('quiz-display-section').classList.add('hidden');
                }
                if (link.section !== 'questions-containern') {
                    document.getElementById('questions-containern').classList.add('hidden');
                }
                // Update active navigation
                document.querySelectorAll('.active-nav').forEach(nav => nav.classList.remove('active-nav'));
                element.classList.add('active-nav');
                // Update URL without reloading
                const newUrl = `dashboard.php${link.action ? '?action=' + link.action : ''}`;
                history.pushState({}, '', newUrl);
            });
        }
    });
});