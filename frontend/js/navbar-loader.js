// Load navbar component
async function loadNavbar(navbarPath, targetId = 'navbar-placeholder') {
    try {
        const response = await fetch(navbarPath);
        const html = await response.text();
        const placeholder = document.getElementById(targetId);
        if (placeholder) {
            placeholder.innerHTML = html;
        } else {
            // If no placeholder, insert at beginning of body
            document.body.insertAdjacentHTML('afterbegin', html);
        }
    } catch (error) {
        console.error('Error loading navbar:', error);
    }
}
