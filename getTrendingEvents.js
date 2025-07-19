// In your main JavaScript file (or a new getTrendingEvents.js)
async function loadTrendingEvents() {
    const container = document.getElementById('trendingEventsContainer');
    if (!container) return;

    container.innerHTML = '<p class="text-center text-muted mt-3">Fetching trending events...</p>';

    try {
        // Modify this to call ticketmaster_events.php with parameters for trending/featured events
        // Or fetch from a pre-curated JSON if dynamic "trending" is hard via API
        const response = await fetch('api/ticketmaster_events.php?genre=Sports&size=3'); // Example: Fetch 3 trending Sports events
        const data = await response.json();

        if (!data.success || !data.events || data.events.length === 0) {
            container.innerHTML = '<p class="text-center text-muted mt-3">No trending events available right now.</p>';
            return;
        }

        container.innerHTML = ''; // Clear loading message
        data.events.forEach(event => {
            const eventCard = `
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="${event.image_url || 'images/event_placeholder.jpg'}" class="card-img-top" alt="${event.name}">
                        <div class="card-body">
                            <h5 class="card-title text-primary-teal">${event.name}</h5>
                            <p class="card-text small text-dark-neutral mb-2">${event.venue}, ${event.date}</p>
                            <a href="${event.link}" target="_blank" class="btn btn-sm btn-outline-primary-teal">View Tickets <i class="bi bi-arrow-right-circle"></i></a>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', eventCard);
        });

    } catch (error) {
        console.error('Error loading trending events:', error);
        container.innerHTML = '<p class="text-center text-danger mt-3">Failed to load trending events. Please try again later.</p>';
    }
}

// Call this function on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    loadTrendingEvents();
    // Ensure your existing sports highlights loading is still active for that section
    // loadSportsHighlights(); // From your existing index-2.html script
});