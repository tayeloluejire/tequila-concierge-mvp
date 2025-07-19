document.addEventListener('DOMContentLoaded', () => {
    const countryFilter = document.getElementById('country-filter');
    const genreFilter = document.getElementById('genre-filter');
    const dateFilter = document.getElementById('date-filter');
    const eventCardsContainer = document.getElementById('event-cards-container');

    const fetchEvents = async () => {
        eventCardsContainer.innerHTML = `
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-primary-teal" role="status">
                    <span class="visually-hidden">Loading events...</span>
                </div>
                <p class="mt-2">Loading exciting events...</p>
            </div>
        `;

        const params = new URLSearchParams();
        if (countryFilter.value) params.append('countryCode', countryFilter.value);
        if (genreFilter.value) params.append('genre', genreFilter.value);
        if (dateFilter.value) params.append('date', dateFilter.value);

        try {
            const response = await fetch(`api/ticketmaster_events.php?${params.toString()}`);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            eventCardsContainer.innerHTML = '';

            if (data.length === 0) {
                eventCardsContainer.innerHTML = `
                    <p class="col-12 text-center text-muted">No events found matching your criteria.</p>
                `;
                return;
            }

            data.forEach(event => {
                const startDate = new Date(event.dates.start.localDate);
                const eventDate = startDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                const eventTime = event.dates.start.localTime ?
                    new Date(`1970-01-01T${event.dates.start.localTime}`).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    }) : '';

                const venue = event._embedded?.venues?.[0] || {};
                const location = `${venue.name || 'Venue TBD'}, ${venue.city?.name || 'City'}, ${venue.state?.stateCode || ''}, ${venue.country?.countryCode || ''}`;

                const priceText = event.priceRanges ?
                    `Price: ${event.priceRanges[0].min} - ${event.priceRanges[0].max} ${event.priceRanges[0].currency}` : '';

                const imageUrl = event.images?.find(img => img.ratio === '16_9' && img.width >= 640)?.url ||
                                 event.images?.[0]?.url ||
                                 'https://via.placeholder.com/640x360?text=Event+Image';

                const infoText = event.info ? event.info.substring(0, 100) + '...' : '';

                const card = `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 event-card shadow-sm">
                            <img src="${imageUrl}" class="card-img-top" alt="${event.name}">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${event.name}</h5>
                                <div class="event-info small text-muted mb-2">
                                    <p><i class="bi bi-calendar"></i> ${eventDate} ${eventTime}</p>
                                    <p><i class="bi bi-geo-alt"></i> ${location}</p>
                                    ${priceText ? `<p><i class="bi bi-tag"></i> ${priceText}</p>` : ''}
                                </div>
                                <p class="card-text flex-grow-1">${infoText}</p>
                                <a href="${event.url}" target="_blank" class="btn btn-primary-alt btn-sm mt-3">View Tickets</a>
                            </div>
                        </div>
                    </div>
                `;
                eventCardsContainer.insertAdjacentHTML('beforeend', card);
            });

        } catch (error) {
            console.error('Error fetching events:', error);
            eventCardsContainer.innerHTML = `
                <p class="col-12 text-center text-danger">⚠️ Failed to load events. Please check your internet or server status.</p>
            `;
        }
    };

    fetchEvents(); // Initial load
    countryFilter.addEventListener('change', fetchEvents);
    genreFilter.addEventListener('change', fetchEvents);
    dateFilter.addEventListener('change', fetchEvents);
});
