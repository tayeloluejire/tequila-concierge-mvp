// netlify/functions/getTravelDeals.js

const axios = require('axios');
require('dotenv').config();

exports.handler = async function(event, context) {
    if (event.httpMethod !== 'POST') {
        return {
            statusCode: 405,
            body: JSON.stringify({ success: false, message: 'Method Not Allowed. Use POST.' }),
        };
    }

    let destination, dates;
    try {
        const body = JSON.parse(event.body);
        destination = body.destination;
        dates = body.dates;
    } catch (err) {
        return {
            statusCode: 400,
            body: JSON.stringify({ success: false, message: 'Invalid JSON input.' }),
        };
    }

    if (!destination || !dates) {
        return {
            statusCode: 400,
            body: JSON.stringify({ success: false, message: 'Missing required parameters: destination, dates' }),
        };
    }

    // Mock data - replace with real API calls when ready
    const mockDeals = [
        {
            type: "Flight & Hotel Package",
            destination: "Paris, France",
            price: "$1200",
            dates: "Oct 10 - Oct 17, 2025",
            link: "https://example.com/paris-deal"
        },
        {
            type: "Hotel Stay",
            destination: "Dubai, UAE",
            price: "$150/night",
            dates: "Nov 5 - Nov 12, 2025",
            link: "https://example.com/dubai-hotel"
        },
        {
            type: "Flight Only",
            destination: "New York, USA",
            price: "$600",
            dates: "Sep 1 - Sep 8, 2025",
            link: "https://example.com/nyc-flight"
        }
    ];

    try {
        // Filter mock deals by destination (case-insensitive)
        const filteredDeals = mockDeals.filter(deal =>
            deal.destination.toLowerCase().includes(destination.toLowerCase())
        );

        return {
            statusCode: 200,
            body: JSON.stringify({
                success: true,
                deals: filteredDeals.length > 0 ? filteredDeals : [],
            }),
        };

    } catch (error) {
        console.error('Error fetching travel deals:', error.response ? error.response.data : error.message);
        return {
            statusCode: 500,
            body: JSON.stringify({
                success: false,
                message: 'Failed to retrieve travel deals.',
                error: error.message,
            }),
        };
    }
};
