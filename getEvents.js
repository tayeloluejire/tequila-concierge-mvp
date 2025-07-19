const axios = require('axios');
require('dotenv').config();

exports.handler = async function(event, context) {
    if (event.httpMethod !== 'POST') {
        return {
            statusCode: 405,
            body: JSON.stringify({ success: false, message: 'Method Not Allowed. Use POST.' })
        };
    }

    let location;
    try {
        const body = JSON.parse(event.body);
        location = body.location;
    } catch (err) {
        return {
            statusCode: 400,
            body: JSON.stringify({ success: false, message: 'Invalid JSON input.' })
        };
    }

    if (!location) {
        return {
            statusCode: 400,
            body: JSON.stringify({ success: false, message: 'Missing required parameter: location' })
        };
    }

    const TICKETMASTER_API_KEY = process.env.TICKETMASTER_API_KEY || 'sCbTC8eciqL0U2vBaGl1avilPhLZb1ak';

    const TICKETMASTER_API_URL = `https://app.ticketmaster.com/discovery/v2/events.json?apikey=${TICKETMASTER_API_KEY}&sort=date,asc&city=${encodeURIComponent(location)}&countryCode=US`;

    try {
        const response = await axios.get(TICKETMASTER_API_URL);
        
        // Log full API response for debugging
        console.log("Ticketmaster API Response:", JSON.stringify(response.data, null, 2));
        
        const events = response.data._embedded?.events || [];

        const formattedEvents = events.map(event => ({
            name: event?.name || 'No name provided',
            date: (event?.dates?.start?.localDate || 'Date not available') + (event?.dates?.start?.localTime ? ` at ${event.dates.start.localTime}` : ''),
            venue: event._embedded?.venues?.[0]?.name || 'N/A',
            link: event?.url || '#'
        }));

        return {
            statusCode: 200,
            body: JSON.stringify({ success: true, events: formattedEvents }),
        };

    } catch (error) {
        console.error('Error fetching events:', error.response ? error.response.data : error.message);
        return {
            statusCode: 500,
            body: JSON.stringify({
                success: false,
                message: 'Failed to retrieve events.',
                error: error.response?.data || error.message
            }),
        };
    }
};
